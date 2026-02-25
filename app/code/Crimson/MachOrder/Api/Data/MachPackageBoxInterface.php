<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 5:24 PM
 * @brief
 */

namespace Crimson\MachOrder\Api\Data;

/**
 * Interface MachPackageBoxInterface
 * @package Crimson\MachOrder\Api\Data
 */
interface MachPackageBoxInterface
{
    const SKU = 'sku';
    const QTY = 'qty';

    /**
     * @param string|null $sku
     *
     * @return MachPackageBoxInterface
     */
    public function setSku(?string $sku): MachPackageBoxInterface;

    /**
     * @return string|null
     */
    public function getSku():  ?string;

    /**
     * @param int $qty
     *
     * @return MachPackageBoxInterface
     */
    public function setQty($qty): MachPackageBoxInterface;

    /**
     * @return int|null
     */
    public function getQty():  ?int;
}
