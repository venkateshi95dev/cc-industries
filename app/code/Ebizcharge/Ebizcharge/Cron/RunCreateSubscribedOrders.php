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

namespace Ebizcharge\Ebizcharge\Cron;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ShellCommand;
use Magento\Framework\Exception\LocalizedException;

/**
 * Run Create Subscribed Orders cron class
 *
 * Class RunCreateSubscribedOrders
 */
class RunCreateSubscribedOrders
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_subscriptions_orders_create_order';

    /**
     * @var ShellCommand
     */
    protected ShellCommand $_shellCommand;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * RunCreateSubscribedOrders constructor.
     *
     * @param EbizchargeLogger $ebizchargeLogger
     * @param ShellCommand $shellCommand
     */
    public function __construct(
        EbizchargeLogger $ebizchargeLogger,
        ShellCommand $shellCommand
    ) {
        /** @var  _shellCommand */
        $this->_shellCommand = $shellCommand;
        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Execute Method
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_ebizchargeLogger->addInfo(__(
                'Subscribed orders process has started to place subscribed orders.'
            ));
            /**
             * Downloading Shell process to download orders
             */
            $this->_shellCommand->shellProcessRunSubscribedOrders();

            $this->_ebizchargeLogger->addInfo(__(
                'Subscribed orders process has been completed to place subscribed orders.'
            ));
        } catch (LocalizedException $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during placing subscribed orders Locally . " . $exception->getMessage()
            ));
        }
    }
}
