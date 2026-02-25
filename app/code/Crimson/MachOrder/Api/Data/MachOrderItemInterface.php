<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 5:41 PM
 * @brief
 */

namespace Crimson\MachOrder\Api\Data;

/**
 * Interface MachOrderItemInterface
 * @package Crimson\MachOrder\Api\Data
 */
interface MachOrderItemInterface
{
    const SKU = 'sku';
    const QTY_ORDERED = 'qty_ordered';
    const QTY_SHIPPED = 'qty_shipped';
    const QTY_BACKORDERED = 'qty_backordered';

    /**
     * @param $sku
     *
     * @return \Crimson\MachOrder\Api\Data\MachOrderItemInterface
     */
    public function setSku($sku): MachOrderItemInterface;

    /**
     * @return mixed
     */
    public function getSku();

    /**
     * @param $qtyOrdered
     *
     * @return \Crimson\MachOrder\Api\Data\MachOrderItemInterface
     */
    public function setQtyOrdered($qtyOrdered): MachOrderItemInterface;

    /**
     * @return mixed
     */
    public function getQtyOrdered();

    /**
     * @param $qtyShipped
     *
     * @return \Crimson\MachOrder\Api\Data\MachOrderItemInterface
     */
    public function setQtyShipped($qtyShipped): MachOrderItemInterface;

    /**
     * @return mixed
     */
    public function getQtyShipped();

    /**
     * @param $qtyBackordered
     *
     * @return \Crimson\MachOrder\Api\Data\MachOrderItemInterface
     */
    public function setQtyBackordered($qtyBackordered): MachOrderItemInterface;

    /**
     * @return mixed
     */
    public function getQtyBackordered();
}
