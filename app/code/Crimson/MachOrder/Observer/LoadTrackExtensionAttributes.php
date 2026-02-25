<?php
 /**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/4/2019 3:12 PM
 * @brief       We do this via observer because \Magento\Shipping\Model\Info loads directly.
 */

namespace Crimson\MachOrder\Observer;

use Crimson\MachOrder\Model\Shipment\Track\Attributes\AttachHandler;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Shipment\Track;

/**
 * Class LoadTrackExtensionAttributes
 * @package Crimson\MachOrder\Observer
 */
class LoadTrackExtensionAttributes implements ObserverInterface
{
    /**
     * @var AttachHandler
     */
    protected $attachHandler;

    public function __construct(AttachHandler $attachHandler)
    {
        $this->attachHandler = $attachHandler;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Track $track */
        $track = $observer->getTrack();

        $this->attachHandler->attachAttributes($track);
    }
}
