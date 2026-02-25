<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Shipment;

use Aws\S3\ObjectCopier;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Model\DataPersistence\Shipment\Shipment\CreateFactory;

/**
 * Class for creating Shipment, getting Shipment info and setting Shipment response
 */
class Shipment
{
    /**
     * @var object
     */
    public $shipmentInfo;

    /**
     * @var object
     */
    public $shipmentResponse;

    /**
     * @var Shipment\CreateFactory
     */
    public $shipmentCreate;

    /**
     *
     * @param Shipment\CreateFactory $shipmentCreate
     * @updatedBy Arushi Bansal
     */
    public function __construct(
        CreateFactory $shipmentCreate
    ) {
        $this->shipmentCreate = $shipmentCreate;
    }

    /**
     * Create Shipment.
     *
     * @param string $stringData
     * @param string $entityCode
     * @param string $erp
     *
     * @return I95DevResponseInterface
     * @updatedBy Arushi Bansal
     */
    public function create($stringData, $entityCode, $erp)
    {
        return $this->shipmentCreate->create()->createShipment($stringData, $entityCode, $erp);
    }
}
