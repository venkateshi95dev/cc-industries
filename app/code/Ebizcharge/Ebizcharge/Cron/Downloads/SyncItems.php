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
use Ebizcharge\Ebizcharge\Model\Customer as CustomerModel;
use Ebizcharge\Ebizcharge\Model\ShellCommand;
use Magento\Framework\Exception\LocalizedException;

/**
 * Cron class to Download Items
 *
 * Class SyncItems
 */
class SyncItems
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
     * SyncItems constructor.
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
     * Execute method fetch Products|Items data from Ebizcharge Gateway
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_ebizchargeLogger->addInfo(__(
                'Product|Items download process started to fetch the records from EBizCharge Gateway'
            ));

            /**
             * run shell command for downloading the products
             */
            $this->_shellCommand->shellProcessDownloadProducts();

            $this->_ebizchargeLogger->addInfo(__(
                'Product|Items download process Completed to fetch record EBizCharge Gateway'
            ));
        } catch (LocalizedException $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during fetching items from EBizCharge Hub " . $exception->getMessage()
            ));
        }
    }
}
