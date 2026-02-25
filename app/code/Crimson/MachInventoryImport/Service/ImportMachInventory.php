<?php

namespace Crimson\MachInventoryImport\Service;

use Crimson\Catalog\Service\UpdateSpecialPrice;
use Crimson\MachBase\Model\MachConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Crimson\MachInventoryImport\Model\Config as ImportInventoryConfig;
use Crimson\MachInventoryImport\Logger\Logger;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface as SourceItemInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryImportExport\Model\Import\SourceItemConvert;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Crimson\MachInventoryImport\Service\ClearAllReservations;
use Crimson\Catalog\Service\AdjustInventorySourceItems;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ImportMachInventory
 * @package Crimson\MachInventoryImport\Service
 */
class ImportMachInventory
{
    CONST SUCCESS_CODE = "success";

    protected $_minimumCount = 0;
    protected array $_errors       = [];
    protected array $_itemDataRows = [];
    protected array $_skuZeroQty   = [];

    /**
     * @var Csv
     */
    protected $csvProcessor;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var ImportInventoryConfig
     */
    protected $importInventoryConfig;

    /**
     * @var DefaultSourceProviderInterface
     */
    protected $defaultSourceProvider;

    /**
     * @var SourceItemConvert
     */
    protected $sourceItemConvert;

    /**
     * @var SourceItemsSaveInterface
     */
    protected $sourceItemsSave;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var ClearAllReservations
     */
    protected $clearAllReservations;

    /**
     * @var File
     */
    protected $file;

    public function __construct(
        Csv $csvProcessor,
        DirectoryList $directoryList,
        ImportInventoryConfig $importInventoryConfig,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SourceItemConvert $sourceItemConvert,
        SourceItemsSaveInterface $sourceItemsSave,
        Filesystem $filesystem,
        IndexerRegistry $indexerRegistry,
        ClearAllReservations $clearAllReservations,
        protected AdjustInventorySourceItems $adjustInventorySourceItems,
        protected UpdateSpecialPrice $updateSpecialPrice,
        protected StoreManagerInterface $storeManagerInterface,
        File $file,
        Logger $logger
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->importInventoryConfig = $importInventoryConfig;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->sourceItemConvert = $sourceItemConvert;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->filesystem = $filesystem;
        $this->indexerRegistry = $indexerRegistry;
        $this->logger = $logger;
        $this->clearAllReservations = $clearAllReservations;
        $this->_minimumCount = $this->importInventoryConfig->getThresholdMinValue();
        $this->file = $file;
    }

    /**
     * @return string[]
     */
    public function execute(): array
    {
        $result = [
            'result'   => self::SUCCESS_CODE,
            'message' => ''
        ];

        try {
            $this->logger->debug("= = = = Starting Mach Inventory Import  = = = =");

            // clearing table inventory_reservation
            $this->logger->debug(__('Clearing table inventory_reservation.'));
            $this->clearAllReservations->execute();

            // get root path
            $rootPath = $this->directoryList->getRoot();
            // get files path
            $filesPath = $rootPath . ImportInventoryConfig::CSV_FILES_FULL_PATH;

            // getting files
            $this->logger->debug(__('Getting Mach Inventory files.'));
            $files = $this->parseMachInventoryCSVFiles($filesPath);
            if (!$files) {
                throw new LocalizedException(__('No files were found.'));
            }
            $this->logger->debug(__('%1 file(s) found to be processed. Files: %2', count($files), implode(' ' . PHP_EOL, $files)));

            // insert data from file to an array
            $this->logger->debug(__('Getting data from file(s).'));
            $rows = $this->mergeFilesInfo($filesPath, $files);
            if (!$rows) {
                throw new LocalizedException(__('No files were found.'));
            }

            // looping
            foreach ($rows as $key => $row) {
                $this->_processRow($row, $key);
            }

            // processing the rows not processed due to minimum count
            $this->_processFinalRows();

            // logging messages
            $result['message'] = __('Mach inventory files have been processed. Check log file for more details.');
            $this->logger->debug(__('Mach inventory files have been processed.'));

            // moving processed file to the Processed folder
            $this->_moveFileToProcessed($files);
            $this->logger->debug(__('Mach inventory files have been moved to the Processed folder.'));

            // logging errors found during the process
            $this->_logErrorsFound();

            // fixing Saleable items
            $this->adjustInventorySourceItems->execute();

            // clearing table inventory_reservation
            $this->logger->debug(__('Clearing table inventory_reservation.'));
            $this->clearAllReservations->execute();

            // clearing special prices if needed
            $zipWebsiteId = $this->_getZipWebsiteId();
            if ($zipWebsiteId && !empty($this->_skuZeroQty)) {
                $this->logger->debug(__('Clearing special prices.'));
                $this->updateSpecialPrice->runBySKUsAndWebsite($this->_skuZeroQty, $zipWebsiteId);
            }

            // reindexing the Inventory
            $this->logger->debug(__('Reindexing.'));
            $this->_reindexInventory();

        } catch (LocalizedException | \Exception $e) {
            $result['status']   = 'error';
            $result['message'] = __('We can\'t import the inventory files right now: %1', $e->getMessage());
            $this->logger->debug($e);
        } finally {
            $this->logger->debug("= = = = Finishing = = = =");
            return $result;
        }
    }

    /**
     * @param $filesPath
     * @param $files
     * @return array|array[]
     */
    protected function mergeFilesInfo($filesPath, $files): array
    {
        if (!$files) {
            return [];
        }

        $data = [];
        foreach ($files as $file) {
            $rows = $this->csvProcessor->getDataPairs($filesPath . $file);
            // remove header data from array
            array_shift($rows);
            if (!$rows) {
                continue;
            }

            // merging rows
            $data = array_merge($data, $rows);
        }

        if ($data) {
            $data = array_map(function ($sku, $qty) {
                return [
                    ImportInventoryConfig::ROW_SKU => $sku,
                    ImportInventoryConfig::ROW_QTY => $qty,
                ];
            }, array_keys($data), $data);
        }

        return $data;
    }

    /**
     * Processing the final rows
     */
    protected function _processFinalRows(): void
    {
        if (count($this->_itemDataRows)) {
            $this->_insertQueuedDataRows(true);
        }
    }

    /**
     * @return $this
     */
    protected function _reindexInventory(): ImportMachInventory
    {
        $startTimerLive = microtime(true);
        $this->logger->debug(__('Invalidating inventory indexer.'));
        $indexer = $this->indexerRegistry->get(InventoryIndexer::INDEXER_ID);
        $indexer->invalidate();
        $this->logger->debug(__('Invalidating inventory indexer complete.'));
        $runTime = round(microtime(true) - $startTimerLive,4);

        $this->logger->debug(__('Completed inventory reindex in %1 seconds.', $runTime));

        return $this;
    }

    /**
     * @return bool
     */
    protected function _logErrorsFound(): bool
    {
        try {

            if (empty($this->_errors)) {
                return true;
            }

            $this->logger->debug(__('The following rows contained errors:'));
            foreach ($this->_errors as $row => $error) {
                $this->logger->debug(__('Row %1: %2', $row, $error));
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function _processRow(array $csvLineRow, int $key): void
    {
        try {
            // Invalid row, we log the real row index
            if (!$this->_isValidRow($csvLineRow)) {
                $this->_errors[$key+2] = ImportInventoryConfig::CSV_FILE_INVALID_ROW_ERROR;
                return;
            }

            $this->_addItemRowUpdate(
                (string) $csvLineRow[ImportInventoryConfig::ROW_SKU],
                (float) $csvLineRow[ImportInventoryConfig::ROW_QTY]
            );

            //if qty is 0 we add the sku to later clean the Special Prices
            if ($csvLineRow[ImportInventoryConfig::ROW_QTY] < 1) {
                $this->_skuZeroQty[] = $csvLineRow[ImportInventoryConfig::ROW_SKU];
            }
        } catch (\Exception $e) {
            // Invalid row, we log the real row index
            $this->_errors[$key+2] = $e->getMessage();
            return;
        }
    }

    /**
     * @param array $csvLineRow
     * @return bool
     */
    protected function _isValidRow(array $csvLineRow): bool
    {
        if (!empty($csvLineRow[ImportInventoryConfig::ROW_SKU]) && $csvLineRow[ImportInventoryConfig::ROW_QTY] >= 0) {
            return true;
        }

        return false;
    }

    /**
     * @param string $sku
     * @param float $qty
     * @return $this
     */
    protected function _addItemRowUpdate(string $sku, float $qty): ImportMachInventory
    {
        $this->_itemDataRows[] = [
            SourceItemInterface::SOURCE_CODE => $this->defaultSourceProvider->getCode(),
            SourceItemInterface::SKU         => $sku,
            SourceItemInterface::QUANTITY    => $qty,
            SourceItemInterface::STATUS      => SourceItemInterface::STATUS_IN_STOCK,
        ];

        $this->_insertQueuedDataRows();

        return $this;
    }

    /**
     * @param bool $force
     */
    protected function _insertQueuedDataRows($force = false): void
    {
        if (count($this->_itemDataRows) >= $this->_minimumCount || $force) {
            $this->_insertBunch($this->_itemDataRows);

            $this->_itemDataRows = [];
        }
    }

    /**
     * @param array $itemDataRows
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws ValidationException
     */
    protected function _insertBunch(array $itemDataRows): void
    {
        if (empty($itemDataRows)) {
            return;
        }

        $this->logger->debug(__('Importing %1 item rows', count($itemDataRows)));
        $startTimerLive = microtime(true);

        // Importing
        $sourceItems = $this->sourceItemConvert->convert($itemDataRows);
        $this->sourceItemsSave->execute($sourceItems);

        $runTime = round(microtime(true) - $startTimerLive,4);
        $this->logger->debug(__('Importing %1 item rows complete in %2 seconds.', count($itemDataRows), $runTime));
    }

    /**
     * @param string $filesPath
     * @param string $fileRegexPattern
     * @return array
     */
    protected function parseMachInventoryCSVFiles(string $filesPath, string $fileRegexPattern = ImportInventoryConfig::FILE_WILD_CARD_NAME_CSV_FILES): array
    {
        try {
            $this->file->cd($filesPath);
            $files = $this->file->ls(File::GREP_FILES);

            if (empty($files)) {
                return [];
            } else {

                // sorting, order is very important last qty will remain on same sku
                $compareFileNames = function ($a, $b) {
                    return strcmp($a["text"], $b["text"]);
                };

                usort($files, $compareFileNames);

                return array_column(array_filter(
                    $files,
                    function ($fileInfo) use ($fileRegexPattern) {
                        return strpos($fileInfo['text'], $fileRegexPattern) === 0 &&
                               strtolower($fileInfo['filetype']) == 'csv';
                    }
                ), "text");
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @param array $files
     * @return bool
     */
    public function _moveFileToProcessed(array $files): bool
    {
        if (!$files) {
            return false;
        }

        try {
            $directoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $rootPath = $this->directoryList->getRoot();
            foreach ($files as $filename) {
                $fromPathFile = $rootPath . ImportInventoryConfig::CSV_FILES_FULL_PATH . $filename;
                $processedFile = $rootPath . ImportInventoryConfig::CSV_FILE_PROCESSED_PATH_NAME . $filename;
                try {
                    $directoryWrite->renameFile($fromPathFile, $processedFile);
                } catch (\Exception $e) {
                    continue;
                }
            }

            return true;
        } catch (FileSystemException $e) {
            return false;
        }
    }

    private function _getZipWebsiteId(): ?int
    {
        try {
            return $this->storeManagerInterface->getWebsite(MachConfig::ZIP_WEBSITE_CODE)->getId();
        } catch (\Exception $e) {
            return null;
        }
    }
}
