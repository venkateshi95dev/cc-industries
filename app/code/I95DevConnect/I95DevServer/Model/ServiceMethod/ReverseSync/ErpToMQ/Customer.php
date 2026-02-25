<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Model\ServiceMethod\ReverseSync\ErpToMQ;

use I95DevConnect\I95DevServer\Model\I95DevServerRepositoryFactory;

/**
 * Class to get customers entity from ERP and save in to Inbound MQ
 */
class Customer
{
    public const DESTINATIONID = "DestinationId";
    public const ADDRESSES = "addresses";
    /**
     * @var I95DevServerRepositoryFactory
     */
    public $i95DevRepository;

    /**
     * Constructor for DI
     *
     * @param I95DevServerRepositoryFactory $i95DevRepository
     */
    public function __construct(
        I95DevServerRepositoryFactory $i95DevRepository
    ) {
        $this->i95DevRepository = $i95DevRepository;
    }

    /**
     * Separate customer and address call for MQ
     *
     * @param [] $customerData
     */
    public function createCustomersList($customerData)
    {
        if (isset($customerData[self::DESTINATIONID])) {
            $destination_id = $customerData[self::DESTINATIONID];
        }

        /* Get address Json string */
        $addressList = [];
        if (isset($customerData[self::ADDRESSES])) {
            $addressList = $customerData[self::ADDRESSES];
            unset($customerData[self::ADDRESSES]);
        }
        $this->i95DevRepository->create()->serviceMethod("createCustomerList", json_encode([$customerData]));

        /* make address call sperate for MQ */
        if (!empty($addressList)) {
            foreach ($addressList as $singleAddress) {
                if (isset($destination_id)) {
                    $singleAddress[self::DESTINATIONID] = $destination_id;
                }
                $this->i95DevRepository->create()->serviceMethod("createAddressList", json_encode([$singleAddress]));
            }
        }
    }
}
