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
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentTrackInterface;

/**
 * Class SaveHandler
 * @package Crimson\MachOrder\Model\Shipment\Track\Attributes
 */
class SaveHandler
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
    public function saveAttributes(ShipmentTrackInterface $track): SaveHandler
    {
        $data = [
            'track_id'              => $track->getEntityId(),
            'mach_package_id'       => $track->getExtensionAttributes()->getMachPackageId(),
            'mach_custom_track_url' => $track->getExtensionAttributes()->getMachCustomTrackUrl(),
        ];

        $this->machDataResource->getConnection()->insertOnDuplicate($this->machDataResource->getMainTable(), $data);

        return $this;
    }
}
