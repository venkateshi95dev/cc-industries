<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 5:30 PM
 * @brief
 */

namespace Crimson\MachOrder\Model;

use Crimson\MachOrder\Api\Data\MachPackageBoxInterface;
use Magento\Framework\DataObject;

/**
 * Class MachPackageBox
 * @package Crimson\MachOrder\Model
 */
class MachPackageBox extends DataObject implements MachPackageBoxInterface
{
    public function setSku(?string $sku): MachPackageBoxInterface
    {
        return $this->setData(self::SKU, $sku);
    }

    public function getSku(): ?string
    {
        return $this->_getData(self::SKU);
    }

    public function setQty($qty): MachPackageBoxInterface
    {
        return $this->setData(self::QTY, $qty);
    }

    public function getQty(): ?int
    {
        return $this->_getData(self::QTY) ? (int) $this->_getData(self::QTY) : null;
    }

}
