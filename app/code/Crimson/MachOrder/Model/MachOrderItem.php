<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 5:43 PM
 * @brief
 */

namespace Crimson\MachOrder\Model;

use Crimson\MachOrder\Api\Data\MachOrderItemInterface;
use Magento\Framework\DataObject;

/**
 * Class MachOrderItem
 * @package Crimson\MachOrder\Model
 */
class MachOrderItem extends DataObject implements MachOrderItemInterface
{
    public function setSku($sku): MachOrderItemInterface
    {
        return $this->setData(self::SKU, $sku);
    }

    public function getSku()
    {
        return $this->_getData(self::SKU);
    }

    public function setQtyOrdered($qtyOrdered): MachOrderItemInterface
    {
        return $this->setData(self::QTY_ORDERED, $qtyOrdered);
    }

    public function getQtyOrdered()
    {
        return $this->_getData(self::QTY_ORDERED);
    }

    public function setQtyShipped($qtyShipped): MachOrderItemInterface
    {
        return $this->setData(self::QTY_SHIPPED, $qtyShipped);
    }

    public function getQtyShipped()
    {
        return $this->_getData(self::QTY_SHIPPED);
    }

    public function setQtyBackordered($qtyBackordered): MachOrderItemInterface
    {
        return $this->setData(self::QTY_BACKORDERED, $qtyBackordered);
    }

    public function getQtyBackordered()
    {
        return $this->_getData(self::QTY_BACKORDERED);
    }

}
