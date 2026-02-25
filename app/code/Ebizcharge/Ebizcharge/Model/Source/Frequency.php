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

use Ebizcharge\Ebizcharge\Model\Recurring;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Recurring Frequency Class
 *
 * Class Frequency
 */
class Frequency implements OptionSourceInterface
{
    /**
     * To Option Array
     *
     * @return string[][]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => Recurring::RECURRING_FREQUENCIES_DAILY,
                'label' => __('Daily')
            ],
            [
                'value' => Recurring::RECURRING_FREQUENCIES_WEEKLY,
                'label' => __('Weekly')
            ],
            [
                'value' => Recurring::RECURRING_FREQUENCIES_BI_WEEKLY,
                'label' => __('Every two weeks')
            ],
            [
                'value' => Recurring::RECURRING_FREQUENCIES_BI_MONTHLY,
                'label' => __('Twice per month')
            ], // Twice per month on the 1 and 15th of every month
            [
                'value' => Recurring::RECURRING_FREQUENCIES_FOUR_WEEK,
                'label' => __('Every four weeks')
            ], // we can handle by monthly
            [
                'value' => Recurring::RECURRING_FREQUENCIES_MONTHLY,
                'label' => __('Monthly')
            ],
            [
                'value' => Recurring::RECURRING_FREQUENCIES_TWO_MONTH,
                'label' => __('Every two months')
            ],
            [
                'value' => Recurring::RECURRING_FREQUENCIES_QUARTERLY,
                'label' => __('Quarterly')
            ],       // Every quarter on the 1st in Jan, Apr, Jul and Oct.
            [
                'value' => Recurring::RECURRING_FREQUENCIES_THREE_MONTH,
                'label' => __('Every three months')
            ],
            [
                'value' => Recurring::RECURRING_FREQUENCIES_90_DAYS,
                'label' => __('Every 90 days')
            ],
            [
                'value' => Recurring::RECURRING_FREQUENCIES_FOUR_MONTH,
                'label' => __('Every four months')
            ],
            [
                'value' => Recurring::RECURRING_FREQUENCIES_FIVE_MONTH,
                'label' => __('Every five months')
            ],
            [
                'value' => Recurring::RECURRING_FREQUENCIES_SIX_MONTH,
                'label' => __('Every six months')
            ],
            [
                'value' => Recurring::RECURRING_FREQUENCIES_180_DAYS,
                'label' => __('Every 180 days')
            ],
            [
                'value' => Recurring::RECURRING_FREQUENCIES_BI_ANNUALLY,
                'label' => __('Twice per year')
            ], //Every 6 month on the 1st in Jan and Jul
            [
                'value' => Recurring::RECURRING_FREQUENCIES_ANNUALLY,
                'label' => __('Yearly')
            ],
        ];
    }
}
