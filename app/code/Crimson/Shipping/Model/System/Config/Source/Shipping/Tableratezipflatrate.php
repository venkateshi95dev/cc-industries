<?php

namespace Crimson\Shipping\Model\System\Config\Source\Shipping;

use Crimson\Shipping\Model\Carrier\Tableratezipflatrate as CarrierTableratezipflatrate;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Tableratezipflatrate
 * @package Crimson\Shipping\Model\System\Config\Source\Shipping
 */
class Tableratezipflatrate implements OptionSourceInterface
{
    /**
     * @var CarrierTableratezipflatrate
     */
    protected $_carrierTablerate;

    /**
     * Tableratezipflatrate constructor.
     * @param CarrierTableratezipflatrate $carrierTablerate
     */
    public function __construct(
        CarrierTableratezipflatrate $carrierTablerate
    )
    {
        $this->_carrierTablerate = $carrierTablerate;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray(): array
    {
        $arr = [];
        foreach ($this->_carrierTablerate->getCode('condition_name') as $k => $v) {
            $arr[] = ['value' => $k, 'label' => $v];
        }

        return $arr;
    }
}
