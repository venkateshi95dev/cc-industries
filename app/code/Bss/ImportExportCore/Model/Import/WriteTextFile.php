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
 *  @category  BSS
 *  @package   Bss_ImportExportCore
 *  @author    Extension Team
 *  @copyright Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 *  @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ImportExportCore\Model\Import;

use Bss\ImportExportCore\Controller\Adminhtml\Import\Validate;
use Bss\ImportExportCore\Model\Import;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\ImportExport\Helper\Report;
use Magento\ImportExport\Model\History;
use Magento\ImportExport\Model\Report\ReportProcessorInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;

/**
 * Write content to file.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class WriteTextFile extends Validate
{
    public const FILE_TXT = 'bss_category';
    public const TYPE_PROCESS = "Processing";
    public const TYPE_SUCCESS = "Success";
    public const FILE_PROCESSING = 'bss_processing';

    /**
     * @var array
     */
    public $process = [];

    /**
     * @var array
     */
    public $message = [];

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Construct Function
     *
     * @param Context $context
     * @param ReportProcessorInterface $reportProcessor
     * @param History $historyModel
     * @param Report $reportHelper
     * @param UploaderFactory $uploaderFactory
     * @param Import $importModel
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context                  $context,
        ReportProcessorInterface $reportProcessor,
        History                  $historyModel,
        Report                   $reportHelper,
        UploaderFactory          $uploaderFactory,
        Import                   $importModel,
        DirectoryList            $directoryList,
        Filesystem               $filesystem
    ) {
        parent::__construct(
            $context,
            $reportProcessor,
            $historyModel,
            $reportHelper,
            $uploaderFactory,
            $importModel
        );
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
    }

    /**
     * Create custom folder and write text file
     *
     * @param Validate $subject
     * @return void
     * @throws FileSystemException
     */
    public function beforeExecute(Validate $subject)
    {
        $varDirectory = $this->filesystem->getDirectoryWrite(
            DirectoryList::VAR_DIR
        );
        $varPath = $this->directoryList->getPath('var');
        $filePath = $varPath . '/importexport/' . self::FILE_TXT;
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $this->write($varDirectory, $filePath, $data);
        }
    }

    /**
     * Write content to text file
     *
     * @param WriteInterface $writeDirectory
     * @param string $filePath
     * @param array|mixed $data
     * @param null|bool $process
     * @return void
     * @throws FileSystemException
     */
    public function write(WriteInterface $writeDirectory, string $filePath, $data, $process = null)
    {
        $stream = $writeDirectory->openFile($filePath, 'w+');
        $stream->lock();

        if ($process) {
            $this->writeProcess($data, $stream);
        } else {
            foreach ($data as $value) {
                $stream->write($value);
                $stream->write("\n");
            }
        }

        $stream->unlock();
        $stream->close();
    }

    /**
     * Ready to write content to text file
     *
     * @return void
     * @throws FileSystemException
     */
    public function readyToWrite()
    {
        foreach ($this->process as $processKey => $processValue) {
            if ($processValue === self::TYPE_PROCESS) {
                $this->process[$processKey] = self::TYPE_SUCCESS;
            }
        }
        $varDirectory = $this->filesystem->getDirectoryWrite(
            DirectoryList::VAR_DIR
        );
        $varPath = $this->directoryList->getPath('var');
        $filePath = $varPath . '/importexport/' . self::FILE_PROCESSING;
        foreach ($this->message as $key => $value) {
            $this->process[$key] = $this->process[$key] . "\t $value";
        }
        $this->write($varDirectory, $filePath, $this->process, true);
    }

    /**
     * Write content to text file
     *
     * @param array $data
     * @param mixed $stream
     * @return void
     */
    public function writeProcess($data, $stream)
    {
        $a = 0;
        foreach ($data as $key => $value) {
            // +2 cuz $key start from 0 and data input start from row 2
            $stream->write((int)$key + 2 . "\t" . $value . "\n");
            if ($value === self::TYPE_SUCCESS) {
                $a++;
            }
        }

        $total = count($data);
        $totals = $total ? floatval($a / $total) * 100 : 0;
        $final = number_format((float)$totals, 2, '.', '');
        $stream->write("\nTotal import Success: $a/$total => " . "\t $final %");
    }
}
