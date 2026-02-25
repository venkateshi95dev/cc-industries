<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ImportExportCore
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ImportExportCore\Command;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\ImportExport\Helper\Report;
use Magento\ImportExport\Model\History as ModelHistory;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Report\ReportProcessorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Controller responsible for initiating the import process.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Start extends Command
{
    public const FILE_TXT = 'bss_category';

    /**
     * @var string[]
     */
    protected $keys = [
        "form_key",
        "entity",
        "behavior",
        "validation_strategy",
        "allowed_error_count",
        "import_multiple_value_separator"
    ];

    /**
     * @var ReportProcessorInterface
     */
    protected $reportProcessor;

    /**
     * @var Report
     */
    protected $reportHelper;

    /**
     * @var \Magento\ImportExport\Model\Import
     */
    protected $importModel;

    /**
     * @var Import\ImageDirectoryBaseProvider
     */
    private $imagesDirProvider;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var File
     */
    protected $driverFile;

    /**
     * @var LoggerInterface;
     */
    protected $logger;

    /**
     * @var ModelHistory
     */
    protected $historyModel;

    /**
     * Construct.
     *
     * @param ReportProcessorInterface $reportProcessor
     * @param ModelHistory $historyModel
     * @param Report $reportHelper
     * @param Import $importModel
     * @param DirectoryList $directoryList
     * @param File $driverFile
     * @param LoggerInterface $logger
     * @param Import\ImageDirectoryBaseProvider|null $imageDirectoryBaseProvider
     * @param string|null $name
     */
    public function __construct(
        ReportProcessorInterface           $reportProcessor,
        ModelHistory                       $historyModel,
        Report                             $reportHelper,
        Import                             $importModel,
        DirectoryList                      $directoryList,
        File                               $driverFile,
        LoggerInterface                    $logger,
        ?Import\ImageDirectoryBaseProvider $imageDirectoryBaseProvider = null,
        string                             $name = null
    ) {
        $this->reportProcessor = $reportProcessor;
        $this->historyModel = $historyModel;
        $this->reportHelper = $reportHelper;
        $this->importModel = $importModel;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->logger = $logger;
        $this->imagesDirProvider = $imageDirectoryBaseProvider
            ?? ObjectManager::getInstance()->get(Import\ImageDirectoryBaseProvider::class);
        parent::__construct($name);
    }

    /**
     * Start import process action
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute1()
    {
        try {
            //get the base folder path you want to scan (replace var with pub / media or any other core folder)
            $path = $this->directoryList->getPath('var') . '/importexport/' . self::FILE_TXT;
            //read just that single directory
            $contents = $this->driverFile->fileGetContents($path);
            $data = $this->getInputFileData($contents);
        } catch (FileSystemException $e) {
            $this->logger->error($e->getMessage());
        }

        if (!empty($data)) {
            $this->importModel->setData($data);
            //Images can be read only from given directory.
            $this->importModel->setData('images_base_directory', $this->imagesDirProvider->getDirectory());
            $errorAggregator = $this->importModel->getErrorAggregator();
            $errorAggregator->initValidationStrategy(
                $this->importModel->getData(Import::FIELD_NAME_VALIDATION_STRATEGY),
                $this->importModel->getData(Import::FIELD_NAME_ALLOWED_ERROR_COUNT)
            );

            try {
                $this->importModel->importSource();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }

            if (!$this->importModel->getErrorAggregator()->hasToBeTerminated()) {
                $this->importModel->invalidateIndex();
            }
        }
    }

    /**
     * Generate Error Report File
     *
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return string
     */
    protected function createErrorReport(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        $this->historyModel->loadLastInsertItem();
        $sourceFile = $this->reportHelper->getReportAbsolutePath($this->historyModel->getImportedFile());
        $writeOnlyErrorItems = true;
        if ($this->historyModel->getData('execution_time') == ModelHistory::IMPORT_VALIDATION) {
            $writeOnlyErrorItems = false;
        }
        $fileName = $this->reportProcessor->createReport($sourceFile, $errorAggregator, $writeOnlyErrorItems);
        $this->historyModel->addErrorReportFile($fileName);
        return $fileName;
    }

    /**
     * Set Command
     */
    protected function configure()
    {
        $this->setName('bss:import_category')
            ->setDescription('Command for Import Bss Category !');

        parent::configure();
    }

    /**
     * Execute cmd.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->execute1();
        $output->writeln('Success!');
    }

    /**
     * Get Input File Data
     *
     * @param string|mixed $contents
     * @return int[]|string|string[]
     */
    public function getInputFileData($contents)
    {
        if ($contents) {
            $dataValue = explode("\n", $contents);
            foreach ($dataValue as $key => $value) {
                if (!$value) {
                    unset($dataValue[$key]);
                }
            }
            $count = count($dataValue);
            $data = array_flip($this->keys);
            for ($i = 0; $i < $count; $i++) {
                $data[array_search($i, $data)] = $dataValue[$i];
            }
            return $data;
        } else {
            return '';
        }
    }
}
