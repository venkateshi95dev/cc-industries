<?php

namespace Crimson\MachShipping\Model\Shipping\Carrier\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Method
 * @package Crimson\MachShipping\Model\Shipping\Carrier\Source
 */
class Method implements OptionSourceInterface
{

    CONST MACH_UPS_CARRIER_CODE   = 'ups';
    CONST MACH_FEDEX_CARRIER_CODE = 'fedex';
    CONST MACH_RESCOM_RATES_CODE  = ['207', '208'];
    CONST MACH_RESIDENTIAL_RATE_CODE  = '207';
    CONST MACH_COMMERCIAL_RATE_CODE   = '208';

    protected array $_availableRates = [
        '501' => 'Ground Delivery',
        '502' => '3 Day Delivery',
        '503' => '2 Day Delivery',
        '504' => '2 Day AM Delivery',
        '505' => 'Next Day/Overnight Saver',
        '506' => 'Next Day/Overnight Delivery',
        '507' => 'Next Day Early AM Delivery',
        '508' => 'SurePost/SmartPost',
        '508C' => 'SurePost/SmartPost',
        '201' => 'FedEx Overnight',
        '202' => 'FedEx Priority',
        '203' => 'FedEx Standard Overnight',
        '204' => 'FedEx 2 Day AM',
        '205' => 'FedEx 2 Day',
        '206' => 'FedEx Express Saver',
        '207' => 'FedEx Home Delivery',
        '208' => 'FedEx Ground',
        '209' => 'SmartPost',
    ];

    protected array $_machCarriers = [
        self::MACH_UPS_CARRIER_CODE => 'UPS',
        self::MACH_FEDEX_CARRIER_CODE => 'FedEx'
    ];

    protected array $_machCarrierRates = [
        self::MACH_UPS_CARRIER_CODE => [
          '501',
          '502',
          '503',
          '504',
          '505',
          '506',
          '507',
          '508',
          '508C',
      ],
        self::MACH_FEDEX_CARRIER_CODE => [
          '201',
          '202',
          '203',
          '204',
          '205',
          '206',
          '207',
          '208',
          '209',
      ]
    ];

    public function toOptionArray(): ?array
    {
        $methods = $this->_availableRates;

        $arr     = [];
        foreach ($methods as $k => $v) {
            $arr[] = ['value' => $k, 'label' => __($k . ' - ' . $v)];
        }

        return $arr;
    }

    public function machCarriersToOptionArray(): array
    {
        $carriers = $this->_machCarriers;

        $arr     = [];
        foreach ($carriers as $k => $v) {
            $arr[] = ['value' => $k, 'label' => __($v)];
        }

        return $arr;
    }

    /**
     * @return array
     */
    public function getAvailableMethods(): array
    {
        return $this->_availableRates;
    }

    /**
     * @param array $availableRates
     * @return $this
     */
    public function setAvailableMethods(array $availableRates)
    {
        $this->_availableRates = $availableRates;
        return $this;
    }


    /**
     * @return int|null|string
     */
    public function getFinalFallbackMethod()
    {
        foreach ($this->getAvailableMethods() as $code => $label) {
            return $code;
        }

        return null;
    }

    /**
     * @param $code
     * @return mixed|null
     */
    public function getMethod($code)
    {
        return isset($this->_availableRates[$code]) ? $this->_availableRates[$code] : null;
    }
}
