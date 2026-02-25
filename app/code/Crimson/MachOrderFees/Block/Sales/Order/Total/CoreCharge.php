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

use Magento\Framework\Phrase;

/**
 * Class CoreCharge
 * @package Crimson\MachOrderFees\Block\Sales\Order\Total
 */
class CoreCharge extends AbstractTotal
{

    /**
     * @return string
     */
    protected function _getCode(): string
    {
        return 'core_charge';
    }

    /**
     * @return Phrase
     */
    protected function _getLabel(): Phrase
    {
        return __('Core Charges');
    }

    /**
     * @return float|null
     */
    protected function _getAmount(): ?float
    {
        return $this->getSource()->getCoreChargeAmount();
    }
}
