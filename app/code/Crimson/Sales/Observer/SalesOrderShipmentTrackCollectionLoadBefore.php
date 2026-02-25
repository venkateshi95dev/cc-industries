<?php
/**
 * @namespace   Crimson
 * @module      SalesFix
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/4/2019 11:01 AM
 * @brief       Load extension attributes via collection load
 */

namespace Crimson\Sales\Observer;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection;

/**
 * Class SalesOrderShipmentTrackCollectionLoadBefore
 * @package Crimson\Sales\Observer
 */
class SalesOrderShipmentTrackCollectionLoadBefore implements ObserverInterface
{

    public function __construct(
        protected JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {}


    public function execute(Observer $observer): void
    {
        /** @var Collection $collection */
        $collection = $observer->getOrderShipmentTrackCollection();

        $this->extensionAttributesJoinProcessor->process($collection);
    }

}
