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

namespace Ebizcharge\Ebizcharge\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Sales\Model\Order\Config;

/**
 * Order Statuses source model
 *
 * Class Status
 */
class VoidOrderYesno implements ArrayInterface
{
    /**
     * Undefined Option Label
     *
     * @const UNDEFINED_OPTION_LABEL
     */
    public const UNDEFINED_OPTION_LABEL = '-- Please Select --';
    /**
     * void order Option Label void and create new
     *
     * @const VOID_ORDER_OPTION_LABEL_VOID_AND_CREATE_NEW
     */
    public const VOID_ORDER_OPTION_LABEL_VOID_AND_CREATE_NEW = 'Void and create new (Magento native)';
    /**
     * Void order Option Label Retain original
     *
     * @const VOID_ORDER_OPTION_LABEL_RETAIN_ORIGINAL
     */
    public const VOID_ORDER_OPTION_LABEL_RETAIN_ORIGINAL = 'Retain original';

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
        $options = [
            ['value' => '1', 'label' => __(self::VOID_ORDER_OPTION_LABEL_VOID_AND_CREATE_NEW)],
            ['value' => '2', 'label' => __(self::VOID_ORDER_OPTION_LABEL_RETAIN_ORIGINAL)]
        ];

        return $options;
    }
}

