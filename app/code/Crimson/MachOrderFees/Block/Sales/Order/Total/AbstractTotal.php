<?php
/**
 * @namespace   Crimson
 * @module      MachOrderFees
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/12/2019 1:14 PM
 * @brief
 */

namespace Crimson\MachOrderFees\Block\Sales\Order\Total;

use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;

/**
 * Class AbstractTotal
 *
 * @package Crimson\MachOrderFees\Block\Sales\Order\Total
 * @method \Crimson\MachOrderFees\Block\Sales\Order\Total\AbstractTotal setBeforeCondition(string $totalCode)
 * @method \Crimson\MachOrderFees\Block\Sales\Order\Total\AbstractTotal setAfterCondition(string $totalCode)
 * @method \Magento\Sales\Block\Adminhtml\Order\Totals|\Magento\Sales\Block\Order\Totals getParentBlock()
 *
 * @method string|null getBeforeCondition()
 * @method string|null getAfterCondition()
 */
abstract class AbstractTotal extends Template
{
    abstract protected function _getCode(): string;
    abstract protected function _getLabel(): \Magento\Framework\Phrase;
    abstract protected function _getAmount(): ?float;

    /**
     * Get label cell tag properties
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get order store object
     *
     * @return Order
     * @codeCoverageIgnore
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Get totals source object
     *
     * @return Order
     * @codeCoverageIgnore
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Get value cell tag properties
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Initialize reward points totals
     *
     * @return $this
     */
    public function initTotals()
    {
        if ($this->_getAmount() >= 0.01) {
            $source = $this->getSource();
            $value = $this->_getAmount();

            $total = new \Magento\Framework\DataObject(
                [
                    'code'   => $this->_getCode(),
                    'strong' => false,
                    'label'  => $this->_getLabel(),
                    'value'  => $source instanceof Creditmemo ? -$value : $value,
                ]
            );

            if ($this->getBeforeCondition()) {
                $this->getParentBlock()->addTotalBefore($total, $this->getBeforeCondition());
            } elseif ($this->getAfterCondition()) {
                $this->getParentBlock()->addTotal($total, $this->getAfterCondition());
            } else {
                $this->getParentBlock()->addTotal($total);
            }
        }

        return $this;
    }
}
