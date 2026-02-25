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
 * Class AdditionalHandling
 * @package Crimson\MachOrderFees\Block\Sales\Order\Total
 */
class AdditionalHandling extends AbstractTotal
{
    /**
     * @return string
     */
    protected function _getCode(): string
    {
        return 'additional_handling';
    }

    /**
     * @return Phrase
     */
    protected function _getLabel(): Phrase
    {
        return __('Additional Handling');
    }

    /**
     * @return float|null
     */
    protected function _getAmount(): ?float
    {
        return $this->getSource()->getAdditionalHandlingAmount();
    }
}
