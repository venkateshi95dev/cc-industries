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
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ImportExportCore\Block\Adminhtml;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Psr\Log\LoggerInterface;

class ImportProcessPage extends Template
{
    public const FILE_TXT = 'bss_processing';

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
     * Construct.
     *
     * @param Context $context
     * @param DirectoryList $directoryList
     * @param File $driverFile
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context         $context,
        DirectoryList   $directoryList,
        File            $driverFile,
        LoggerInterface $logger
    ) {
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Show Content Processing Page
     *
     * @return Phrase|void
     */
    public function getProcess()
    {
        try {
            //get the base folder path you want to scan (replace var with pub / media or any other core folder)
            $path = $this->directoryList->getPath('var') . '/importexport/' . self::FILE_TXT;

            //read just that single directory
            $contents = $this->driverFile->fileGetContents($path);
            $findString = strrchr($contents, "\n");
            $display = $findString ? substr($findString, 1) : "";

            return __("$display");
        } catch (FileSystemException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
