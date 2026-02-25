<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 5:28 PM
 * @brief
 */

namespace Crimson\MachOrder\Model;

use Crimson\MachOrder\Api\Data\MachPackageBoxInterface;
use Crimson\MachOrder\Api\Data\MachPackageInterface;
use Magento\Framework\DataObject;

/**
 * Class MachPackage
 * @package Crimson\MachOrder\Model
 */
class MachPackage extends DataObject implements MachPackageInterface
{
    /**
     * @param $packageId
     *
     * @return MachPackageInterface
     */
    public function setPackageId($packageId): MachPackageInterface
    {
        return $this->setData(self::PACKAGE_ID, $packageId);
    }

    /**
     * @return string|null
     */
    public function getPackageId(): ?string
    {
        return $this->_getData(self::PACKAGE_ID);
    }

    /**
     * @param $trackingNumber
     *
     * @return MachPackageInterface
     */
    public function setTrackingNumber($trackingNumber): MachPackageInterface
    {
        return $this->setData(self::TRACKING_NUMBER, $trackingNumber);
    }

    /**
     * @return string|null
     */
    public function getTrackingNumber(): ?string
    {
        return $this->_getData(self::TRACKING_NUMBER);
    }

    /**
     * @param $packageLink
     *
     * @return MachPackageInterface
     */
    public function setPackageLink($packageLink): MachPackageInterface
    {
        return $this->setData(self::PACKAGE_LINK, $packageLink);
    }

    /**
     * @return string|null
     */
    public function getPackageLink(): ?string
    {
        return $this->_getData(self::PACKAGE_LINK);
    }

    /**
     * @param MachPackageBoxInterface[] $boxDetails
     *
     * @return MachPackageInterface
     */
    public function setBoxDetails(array $boxDetails): MachPackageInterface
    {
        return $this->setData(self::BOX_DETAILS, $boxDetails);
    }

    /**
     * @return MachPackageBoxInterface[]
     */
    public function getBoxDetails(): array
    {
        return $this->_getData(self::BOX_DETAILS) ?: [];
    }

}
