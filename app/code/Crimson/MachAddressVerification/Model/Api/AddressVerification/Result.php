<?php

namespace Crimson\MachAddressVerification\Model\Api\AddressVerification;

use Magento\Framework\DataObject;

/**
 * Class Result
 * @package Crimson\MachAddressVerification\Model\Api\AddressVerification
 */
class Result extends DataObject
{

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
