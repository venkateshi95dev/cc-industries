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

namespace Ebizcharge\Ebizcharge\Model\Source\Recurring;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Model\Recurring;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Recurring Status Class
 *
 * Class Status
 */
class Status implements OptionSourceInterface
{
    /**
     * @var Recurring
     */
    protected Recurring $_recurringModel;

    /**
     * Status constructor.
     *
     * @param Recurring $recurringModel
     */
    public function __construct(
        Recurring $recurringModel
    )
    {
        $this->_recurringModel = $recurringModel;
    }

    /**
     * To Option Array
     *
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => RecurringInterface::EBIZCHARGE_RECURRING_STATUSES[RecurringInterface::EBIZCHARGE_RECURRING_STATUS_ACTIVE],
                'value' => RecurringInterface::EBIZCHARGE_RECURRING_STATUS_ACTIVE
            ],
            [
                'label' => RecurringInterface::EBIZCHARGE_RECURRING_STATUSES[RecurringInterface::EBIZCHARGE_RECURRING_STATUS_SUSPENDED],
                'value' => RecurringInterface::EBIZCHARGE_RECURRING_STATUS_SUSPENDED
            ],
            [
                'label' => RecurringInterface::EBIZCHARGE_RECURRING_STATUSES[RecurringInterface::EBIZCHARGE_RECURRING_STATUS_EXPIRED],
                'value' => RecurringInterface::EBIZCHARGE_RECURRING_STATUS_EXPIRED
            ],
            [
                'label' => RecurringInterface::EBIZCHARGE_RECURRING_STATUSES[RecurringInterface::EBIZCHARGE_RECURRING_STATUS_CANCELED],
                'value' => RecurringInterface::EBIZCHARGE_RECURRING_STATUS_CANCELED
            ],
        ];
    }
}
