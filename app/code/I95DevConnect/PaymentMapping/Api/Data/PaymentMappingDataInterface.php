<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentMapping
 */

namespace I95DevConnect\PaymentMapping\Api\Data;

/**
 * Represents Data Object for a Payment Mapping
 */
interface PaymentMappingDataInterface
{
    public const ID = 'id';
    public const MAPPED_DATA = 'mapped_data';
    public const CREATED_AT = 'created_at';

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $Id
     * @return $this
     */
    public function setId($Id);

    /**
     * Get mapping data
     *
     * @return string|null
     */
    public function getMappedData();

    /**
     * Set Payment Mapping Data
     *
     * @param string $mappedData
     * @return $this
     */
    public function setMappedData($mappedData);

    /**
     * Get created date
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created date
     *
     * @param string $createdDate
     * @return $this
     */
    public function setCreatedAt($createdDate);
}
