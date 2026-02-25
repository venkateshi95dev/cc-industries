<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/4/2019 11:39 AM
 * @brief
 */

namespace Crimson\MachOrder\Plugin\Api;

use Crimson\MachOrder\Model\Shipment\Track\Attributes\AttachHandler;
use Crimson\MachOrder\Model\Shipment\Track\Attributes\SaveHandler;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;

/**
 * Class ShipmentTrackRepositoryInterfacePlugin
 * @package Crimson\MachOrder\Plugin\Api
 */
class ShipmentTrackRepositoryInterfacePlugin
{
    /**
     * @var AttachHandler
     */
    protected $attachHandler;
    /**
     * @var SaveHandler
     */
    protected $saveHandler;

    public function __construct(
        AttachHandler $attachHandler,
        SaveHandler $saveHandler
    ) {
        $this->attachHandler = $attachHandler;
        $this->saveHandler   = $saveHandler;
    }

    /**
     * @param ShipmentTrackRepositoryInterface $subject
     * @param ShipmentTrackInterface      $result
     *
     * @return ShipmentTrackInterface
     * @throws LocalizedException
     */
    public function afterGet(ShipmentTrackRepositoryInterface $subject, $result): ShipmentTrackInterface
    {
        $this->attachHandler->attachAttributes($result);

        return $result;
    }

    /**
     * @param ShipmentTrackRepositoryInterface $subject
     * @param $result
     * @return ShipmentTrackInterface
     * @throws LocalizedException
     */
    public function afterSave(ShipmentTrackRepositoryInterface $subject, $result): ShipmentTrackInterface
    {
        $this->saveHandler->saveAttributes($result);

        return $result;
    }
}
