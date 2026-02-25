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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Downloads;

use Ebizcharge\Ebizcharge\Api\Data\SyncAssetsInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config as ConfigModel;
use Ebizcharge\Ebizcharge\Model\Customer as CustomerModel;
use Ebizcharge\Ebizcharge\Model\ShellCommand;
use Ebizcharge\Ebizcharge\Model\SyncAssets;
use Ebizcharge\Ebizcharge\Model\SyncAssetsFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\LayoutFactory;

/**
 * Download resource Progress Bar
 *
 * Class ProgressBar
 */
class ProgressBar extends Action
{
    /**
     * @var LayoutFactory
     */
    protected LayoutFactory $_resultLayoutFactory;

    /**
     * @var CustomerModel
     */
    protected CustomerModel $_customerModel;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var ShellCommand
     */
    protected ShellCommand $_shellCommand;

    /**
     * @var ConfigModel
     */
    protected ConfigModel $_configModel;

    /**
     * @var SyncAssetsFactory
     */
    protected SyncAssetsFactory $_syncAssetsFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $_jsonFactory;

    /**
     * DownloadLatestResources constructor.
     *
     * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     * @param CustomerModel $customerModel
     * @param EbizchargeLogger $ebizchargeLogger
     * @param ConfigModel $configModel
     * @param ShellCommand $shellCommand
     * @param SyncAssetsFactory $syncAssetsFactory
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context           $context,
        LayoutFactory     $resultLayoutFactory,
        CustomerModel     $customerModel,
        EbizchargeLogger  $ebizchargeLogger,
        ConfigModel       $configModel,
        ShellCommand      $shellCommand,
        SyncAssetsFactory $syncAssetsFactory,
        JsonFactory       $jsonFactory
    ) {
        parent::__construct($context);

        /** @var _configModel */
        $this->_configModel = $configModel;
        /** @var _customerModel */
        $this->_customerModel = $customerModel;
        /** @var _shellCommand */
        $this->_shellCommand = $shellCommand;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _syncAssetsFactory */
        $this->_syncAssetsFactory = $syncAssetsFactory;
        /** @var _jsonFactory */
        $this->_jsonFactory = $jsonFactory;
    }

    /**
     * Execute Methods
     *
     * @return false|ResponseInterface|Json|ResultInterface|void
     */
    public function execute()
    {
        /** @var  $params */
        $progressRequestAction = $this->getRequest()->getParam('progress_request');

        /** assets Action Request */
        if (!isset($progressRequestAction)) {
            $this->_ebizchargeLogger->addError(__("Error occurred assets request is not set"));
            return false;
        }
        /** @var $jsonFactory */
        $jsonFactory = $this->_jsonFactory->create();

        /** Sync Assets Switch */
        switch ($progressRequestAction) {
            /** Asset Request download customers */
            case 'downloaded_customer_progress':
                $downloadProgressData = $this->_syncAssetsFactory->create()
                    ->getDownloadProgress(SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS);
                $currentProgressCounter = $downloadProgressData["progress_counter"] ?? 0;
                if ((float)($currentProgressCounter) > 0) {
                    $downloadedRecords = $downloadProgressData["total_downloaded_records"] ?? 0;
                    $this->_ebizchargeLogger->addInfo(__("Downloaded (" . $downloadedRecords .
                        ") customers from EBizCharge Hub with progress (" . $currentProgressCounter . "%)."));
                }
                /** creating JSON Factory */
               break;

                /** Assets Request is download Orders */
            case 'downloaded_order_progress':
                $downloadProgressData = $this->_syncAssetsFactory->create()
                    ->getDownloadProgress(SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS);

                $currentProgressCounter = $downloadProgressData["progress_counter"] ?? 0;
                if ((float)($currentProgressCounter) > 0) {
                    $downloadedRecords = $downloadProgressData["total_downloaded_records"] ?? 0;
                    $this->_ebizchargeLogger->addInfo(__("Downloaded (" . $downloadedRecords .
                        ") Orders from EBizCharge Hub with progress (" . $currentProgressCounter . "%)."));
                }
             break;
                /** Assets Download Items */
            case 'downloaded_product_progress':
                $downloadProgressData = $this->_syncAssetsFactory->create()
                    ->getDownloadProgress(SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS);
                $currentProgressCounter = $downloadProgressData["progress_counter"] ?? 0;
                if ((float)($currentProgressCounter) > 0) {
                    $downloadedRecords = $downloadProgressData["total_downloaded_records"] ?? 0;
                    $this->_ebizchargeLogger->addInfo(__("Downloaded (" . $downloadedRecords .
                        ") Items from EBizCharge Hub with progress (" . $currentProgressCounter . "%)."));
                }
                /** creating JSON Factory */
                break;
        }

        return $jsonFactory->setData(json_encode($downloadProgressData));

    }
}
