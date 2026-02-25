<?php

namespace Crimson\CorvetteCentralImport\Service;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\SerializerInterface;

class ImportCCProductPackages
{
    CONST SUCCESS_CODE         = "success";
    CONST CSV_FILES_PATH       = "/var/import/ImportCCProducts/Packages/";
    CONST CSV_PACKAGES_MAIN_IMPORT_FILE = "Item Multi Package Data(Packages).csv";
    CONST CSV_MAIN_FILE            = "crimson_product_packages_import.csv";
    CONST BATCH_SIZE               = 1000;

    protected array $_headerCols = [];

    public function __construct(
        protected Csv $csvProcessor,
        protected DirectoryList $directoryList,
        protected File $file,
        protected Filesystem $filesystem,
        protected SerializerInterface $serializer,
    ) {}

    public function execute(): array
    {
        $result = [
            'result'   => self::SUCCESS_CODE,
            'message' => ''
        ];

        try {
            // get root path
            $rootPath = $this->directoryList->getRoot();
            // get files path
            $filesPath = $rootPath . self::CSV_FILES_PATH;

            //processing 1st main file
            $mainFileDataExport = [];
            $mainFileDataExport = $this->_processMainFile($mainFileDataExport);
            if (!$mainFileDataExport) {
                throw new LocalizedException(__('No data after processing main file.'));
            }

            //creating main export CSV file
            $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $streamMainFileExport = $directory->openFile($filesPath . self::CSV_MAIN_FILE, 'w+');
            $streamMainFileExport->lock();
            foreach (array_chunk($mainFileDataExport, self::BATCH_SIZE, true) as $currentRows) {
                foreach ($currentRows as $sku => $row) {
                    $streamMainFileExport->writeCsv([
                        'sku'              => $sku,
                        'product_websites' => CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_CODE,
                        'cc_packageexists' => 'Yes',
                        'cc_packages'      => $this->_getPackages($row),
                    ]);
                }
            }

            //finishing with the file
            $streamMainFileExport->unlock();
            $streamMainFileExport->close();

            // logging messages
            $result['message'] = __('All rows have been processed.');

        } catch (LocalizedException | \Exception $e) {
            $result['status']   = 'error';
            $result['message'] = __('We can\'t import the files right now: %1', $e->getMessage());
        } finally {
            return $result;
        }
    }

    private function _processMainFile(array $mainFileDataExport): array
    {
        // get root path
        $rootPath = $this->directoryList->getRoot();
        // get files path
        $filesPath = $rootPath . self::CSV_FILES_PATH;

        // getting main file
        $mainFileDataImport = $this->csvProcessor->getData($filesPath . ImportCCProductPackages::CSV_PACKAGES_MAIN_IMPORT_FILE);
        array_shift($mainFileDataImport);
        if (!$mainFileDataImport) {
            throw new LocalizedException(__('No data in main file.'));
        }

        foreach (array_chunk($mainFileDataImport, self::BATCH_SIZE, true) as $currentRows) {
            foreach ($currentRows as $row) {
                if (empty($row[0])) {
                    continue;
                }

                $mainFileDataExport[trim($row[0])][] = [
                    "cc_packages"        => [
                        'length' => !empty($row[2]) ? (float)number_format($row[2], 4, '.', '') : 0.00,
                        'width'  => !empty($row[3]) ? (float)number_format($row[3], 4, '.', '') : 0.00,
                        'height' => !empty($row[4]) ? (float)number_format($row[4], 4, '.', '') : 0.00,
                        'weight' => !empty($row[5]) ? (float)number_format($row[5], 4, '.', '') : 0.00,
                    ],
                ];
            }
        }

        return $mainFileDataExport;
    }

    protected function _getPackages(array $row)
    {
        if (empty($row)) {
            return $this->serializer->serialize([]);
        }

        $result = [];
        foreach ($row as $index => $package) {
            $result[] = [
                'record_id' => $index,
                'length'    => $package['cc_packages']['length'],
                'width'     => $package['cc_packages']['width'],
                'height'    => $package['cc_packages']['height'],
                'weight'    => $package['cc_packages']['weight'],
            ];
        }

        return $this->serializer->serialize($result);
    }
}
