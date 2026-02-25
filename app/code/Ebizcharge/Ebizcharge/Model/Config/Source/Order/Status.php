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

namespace Ebizcharge\Ebizcharge\Model\Config\Source\Order;

use Magento\Framework\Option\ArrayInterface;
use Magento\Sales\Model\Order\Config;

/**
 * Order Statuses source model
 *
 * Class Status
 */
class Status implements ArrayInterface
{
    /**
     * Undefined Option Label
     *
     * @const UNDEFINED_OPTION_LABEL
     */
    public const UNDEFINED_OPTION_LABEL = '-- Please Select --';

    /**
     * @var Config
     */
    protected Config $_orderConfig;

    /**
     * @param Config $orderConfig
     */
    public function __construct(Config $orderConfig)
    {
        /** @var  _orderConfig */
        $this->_orderConfig = $orderConfig;
    }

    /**
     * Order statuses to option array
     *
     * @return array|array[]
     */
    public function toOptionArray(): array
    {
        $statuses = $this->_orderConfig->getStatuses();

        // phpcs:ignore
        $options = [['value' => '', 'label' => __(self::UNDEFINED_OPTION_LABEL)]];
        foreach ($statuses as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }
        return $options;
    }
}
