<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/4/2019 11:42 AM
 * @brief
 */

namespace Crimson\MachOrder\Model\Shipment\Track\Attributes;

use Crimson\MachOrder\Model\ResourceModel\Shipment\Track\MachData;
use Crimson\Sales\Model\Order\Attributes\AbstractAttachHandler;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentTrackInterface;

/**
 * Class AttachHandler
 * @package Crimson\MachOrder\Model\Shipment\Track\Attributes
 */
class AttachHandler
{
    /**
     * @var MachData
     */
    protected $machDataResource;

    /**
     * AttachHandler constructor.
     *
     * @param MachData $machDataResource
     */
    public function __construct(
        MachData $machDataResource
    ) {
        $this->machDataResource = $machDataResource;
    }

    /**
     * @param ShipmentTrackInterface $track
     * @return $this
     * @throws LocalizedException
     */
    public function attachAttributes(ShipmentTrackInterface $track): AttachHandler
    {
        if (!$track->getEntityId()) {
            return $this;
        }

        $orderData = $this->machDataResource->getMachData($track->getEntityId());

        $track->getExtensionAttributes()
            ->setMachPackageId($orderData['mach_package_id'] ?? null)
            ->setMachCustomTrackUrl($orderData['mach_custom_track_url'] ?? null);

        return $this;
    }
}
