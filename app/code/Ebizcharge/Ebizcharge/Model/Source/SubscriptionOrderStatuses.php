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

namespace Ebizcharge\Ebizcharge\Model\Source;

use Ebizcharge\Ebizcharge\Api\Data\OrderSubscriptionInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Recurring Status Class
 *
 * Class Status
 */
class SubscriptionOrderStatuses implements OptionSourceInterface
{
    /**
     * To Option Array
     *
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __(OrderSubscriptionInterface::EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_COMPLETED_TITLE),
                'value' => OrderSubscriptionInterface::EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_COMPLETED
            ],
            [
                'label' => __(OrderSubscriptionInterface::EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_FAILED_TITLE),
                'value' => OrderSubscriptionInterface::EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_FAILED
            ]

        ];
    }
}
