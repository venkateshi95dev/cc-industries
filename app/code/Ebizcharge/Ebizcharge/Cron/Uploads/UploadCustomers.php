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

namespace Ebizcharge\Ebizcharge\Cron\Uploads;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Customer as CustomerModel;
use Ebizcharge\Ebizcharge\Model\ShellCommand;
use Magento\Framework\Exception\LocalizedException;

/**
 * Cron class to Upload Customers
 *
 * Class UploadCustomers
 */
class UploadCustomers
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
     * @var ShellCommand
     */
    protected ShellCommand $_shellCommand;

    /**
     * UploadCustomers constructor.
     *
     * @param EbizchargeLogger $ebizchargeLogger
     * @param CustomerModel $customerModel
     * @param ShellCommand $shellCommand
     */
    public function __construct(
        EbizchargeLogger $ebizchargeLogger,
        CustomerModel $customerModel,
        ShellCommand $shellCommand
    ) {
        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var  _customerModel */
        $this->_customerModel = $customerModel;
        /** @var  _shellCommand */
        $this->_shellCommand = $shellCommand;
    }

    /**
     * Execute method to upload customers EBizCharge Hub
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_ebizchargeLogger->addInfo(__(
                'Customers upload process has started to upload the records to EBizCharge Gateway'
            ));

            /**
             * run shell command for uploading customers
             */
            $this->_shellCommand->shellProcessUploadCustomers();

            $this->_ebizchargeLogger->addInfo(__(
                'Customers upload process has completed to upload the records to EBizCharge Gateway'
            ));
        } catch (LocalizedException $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during uploading Customers to EBizCharge Hub " . $exception->getMessage()
            ));
        }
    }
}
