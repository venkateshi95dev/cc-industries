<?php
/**
 * @namespace   Crimson
 * @module      MachShipping
 * @date        06/07/2022 12:00 am
 * @brief
 */

namespace Crimson\MachShipping\Model;

use Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface;
use Magento\Framework\Model\AbstractModel;

class UpsDeliverySaturdays extends AbstractModel implements UpsDeliverySaturdaysInterface
{
    protected $_eventPrefix = 'crimson_machshipping_upsdeliverysaturdays';
    
    protected function _construct()
    {
        $this->_init(\Crimson\MachShipping\Model\ResourceModel\UpsDeliverySaturdays::class);
    }

    /**
     * @return string|null
     */
    public function getZipCodeValue() : ?string
    {
        return $this->_getData(self::ZIP_CODE_VALUE);
    }
    
    /**
     * @param string|null $zipCodeValue
     * @return \Crimson\MachShipping\Model\UpsDeliverySaturdays
     */
    public function setZipCodeValue(?string $zipCodeValue) : \Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface
    {
        return $this->setData(self::ZIP_CODE_VALUE, $zipCodeValue);
    }
    
}
