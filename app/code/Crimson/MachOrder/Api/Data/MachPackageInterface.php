<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 5:27 PM
 * @brief
 */

namespace Crimson\MachOrder\Api\Data;

/**
 * Interface MachPackageInterface
 * @package Crimson\MachOrder\Api\Data
 */
interface MachPackageInterface
{
    const PACKAGE_ID = 'package_id';
    const TRACKING_NUMBER = 'tracking_number';
    const PACKAGE_LINK = 'package_link';
    const BOX_DETAILS = 'box_details';

    /**
     * @param $packageId
     *
     * @return MachPackageInterface
     */
    public function setPackageId($packageId): MachPackageInterface;

    /**
     * @return string|null
     */
    public function getPackageId(): ?string;

    /**
     * @param $trackingNumber
     *
     * @return MachPackageInterface
     */
    public function setTrackingNumber($trackingNumber): MachPackageInterface;

    /**
     * @return string|null
     */
    public function getTrackingNumber(): ?string;

    /**
     * @param $packageLink
     *
     * @return MachPackageInterface
     */
    public function setPackageLink($packageLink): MachPackageInterface;

    /**
     * @return string|null
     */
    public function getPackageLink(): ?string;

    /**
     * @param \Crimson\MachOrder\Api\Data\MachPackageBoxInterface[] $boxDetails
     *
     * @return MachPackageInterface
     */
    public function setBoxDetails(array $boxDetails): MachPackageInterface;

    /**
     * @return \Crimson\MachOrder\Api\Data\MachPackageBoxInterface[]
     */
    public function getBoxDetails(): array;
}
