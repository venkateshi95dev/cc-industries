<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Api;

/**
 * Data Persistence Interface which contains create, get update methods.
 */
interface DataPersistenceInterface
{
    /**
     * Create and save data in magento entity tables
     *
     * @param string $entityCode
     * @param array $dataString
     * @param string $messageId
     * @param string $erpCode
     *
     * @return I95DevResponseInterface
     * @updated By Hrusikesh Added $messageId as Extra Parameter
     */
    public function createEntity($entityCode, $dataString, $messageId, $erpCode = null);

    /**
     * Get entity info from magento table
     *
     * @param string $entityCode
     * @param array $dataString
     * @param string $erpCode
     * @param int|null $messageId
     * @return I95DevResponseInterface
     */
    public function getEntityInfo($entityCode, $dataString, $erpCode = null, $messageId = null);

    /**
     * Set ERP response data in given entity
     *
     * @param string $entityCode
     * @param array $dataString
     * @param string $erpCode
     * @return I95DevResponseInterface
     */
    public function getEntityResponse($entityCode, $dataString, $erpCode = null);
}
