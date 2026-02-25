<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Block\Adminhtml\PaymentHistory\Grid as PaymentHistory;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\Widget\Grid\ExportInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/***
 * Subscriptions Payment History Exl export class
 *
 * Class ExportPaymentHistoryExl
 */
class ExportPaymentHistoryExl extends Action
{
    /**
     * Payment History File Name
     *
     * @const PAYMENT_HISTORY_FILE_NAME
     */
    public const PAYMENT_HISTORY_FILE_NAME = 'PaymentHistory.xlsx';

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var EbizchargeLogger
     */
    private $ebizchargeLogger;

    /**
     * Main Constructor of the class
     *
     * @param Context $context
     * @param EbizchargeLogger $ebizchargeLogger
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        EbizchargeLogger $ebizchargeLogger,
        FileFactory $fileFactory
    ) {
        /** @var  _fileFactory */
        $this->_fileFactory = $fileFactory;

        parent::__construct($context);

        /** @var ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Execute Method
     *
     * Export customers most ordered report to CSV format
     *
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = self::PAYMENT_HISTORY_FILE_NAME;

        /** @var ExportInterface $exportBlock */
        $exportBlock = $this->_view->getLayout()->createBlock(PaymentHistory::class);

        try {
            return $this->_fileFactory->create($fileName, $exportBlock->getExcelFile(), DirectoryList::VAR_DIR);
        } catch (Exception $e) {
            /** logging exception to the logger */
            $this->ebizchargeLogger->addCritical(__(" Exception occured " . $e->getMessage()));
        }
    }
}
