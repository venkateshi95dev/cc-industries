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

namespace Ebizcharge\Ebizcharge\Cron\Downloads;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config as EbizchargConfigResource;
use Ebizcharge\Ebizcharge\Model\Customer as CustomerModel;
use Ebizcharge\Ebizcharge\Model\ShellCommand;
use Magento\Framework\Exception\LocalizedException;

/**
 * Cron class to Download Customers
 *
 * Class SyncCustomers
 */
class SyncCustomers
{
    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var CustomerModel
     */
    protected CustomerModel $_customerModel;

    /**
     * @var EbizchargConfigResource
     */
    protected EbizchargConfigResource $_ebizchargeConfigResource;

    /**
     * @var ShellCommand
     */
    protected ShellCommand $_shellCommand;

    /**
     * SyncCustomers constructor.
     *
     * @param EbizchargeLogger $ebizchargeLogger
     * @param CustomerModel $customerModel
     * @param ShellCommand $shellCommand
     * @param EbizchargConfigResource $ebizchargConfigResource
     */
    public function __construct(
        EbizchargeLogger $ebizchargeLogger,
        CustomerModel $customerModel,
        ShellCommand $shellCommand,
        EbizchargConfigResource $ebizchargConfigResource
    ) {
        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var  _customerModel */
        $this->_customerModel = $customerModel;
        /** @var  _ebizchargeConfigResource */
        $this->_ebizchargeConfigResource = $ebizchargConfigResource;
        /** @var  _shellCommand */
        $this->_shellCommand = $shellCommand;
    }

    /**
     * Execute Method
     *
     * @return false|void
     */
    public function execute()
    {
        try {
            /** @var  $storeId */
            $storeId = $this->_customerModel->getStore()->getId();

            $isDownloadEnabled = $this->_ebizchargeConfigResource->isEconnectDownlaodEnabled($storeId);
            $isCustomerDownloadEnabled = $this->_ebizchargeConfigResource->isDownlaodCustomersEnabled($storeId);

            /** if Download from EBizCharge Enabled */
            if (!$isDownloadEnabled) {
                // phpcs:disable
                $this->_ebizchargeLogger->addError(__("Download Customers Cron is aborted as \"Download Feature\" from EBizCharge Gateway is disabled, please enable it in System Configuration."));
                return false;
            }
            /** if Download Customers from EBizCharge Enabled */
            if (!$isCustomerDownloadEnabled) {
                $this->_ebizchargeLogger->addError(__("Download Customers Cron is aborted as \"Download Feature\" from EBizCharge Gateway is disabled, please enable it in System Configuration."));
                return false;
            }

            $this->_ebizchargeLogger->addInfo(__('Customer download process started to fetch the records from EBizCharge Gateway'));
            // phpcs:enable

            /**
             * Syncing customers from EBizCharge Gateway
             */
            $this->_shellCommand->shellProcessDownloadCustomers();

            $this->_ebizchargeLogger->addInfo(__(
                'Customer download process Completed to fetch record EBizCharge Gateway'
            ));
        } catch (LocalizedException $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception during cron job of download customers." . $exception->getMessage()
            ));
        }
    }
}
