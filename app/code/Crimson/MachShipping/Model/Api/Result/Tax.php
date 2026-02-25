<?php

namespace Crimson\MachShipping\Model\Api\Result;

use Crimson\MachShipping\Model\Api\Shipping;
use Magento\Framework\DataObject;

/**
 * Class Freight
 * @package Crimson\MachShipping\Model\Result
 */
class Tax extends DataObject
{
    /**
     * @return string
     */
    public function getTaxCode(): string
    {
        return (string) $this->_getDataWithDefault('tax_code', 0);
    }

    /**
     * @return mixed|null
     */
    public function getTaxAmount(): float
    {
        return (float) $this->_getDataWithDefault('tax_amount', 0);
    }

    public function getResComFlag(): string
    {
        return (string) $this->_getDataWithDefault('res_com_flag', Shipping::RESIDENTIAL_FLAG);
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    protected function _getDataWithDefault($key, $default = null)
    {
        if ($this->hasData($key)) {
            return $this->_getData($key);
        }

        return $default;
    }

}
