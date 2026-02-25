<?php
/**
 * @namespace   Crimson
 * @module      MachShipping
 * @date        06/07/2022 12:00 am
 * @brief
 */

namespace Crimson\MachShipping\Api\Data; 

interface UpsDeliverySaturdaysInterface
{
    const ENTITY_ID = 'entity_id';
    const ZIP_CODE_VALUE = 'zip_code_value';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return \Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface 
     */
    public function setId($id);

    /**
     * Retrieve entity id
     *
     * @return mixed
     */
    public function getEntityId();

    /**
     * Set entity id
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * @return string|null
     */
    public function getZipCodeValue() : ?string;

    /**
     * @param string|null $zipCodeValue
     * @return \Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface
     */
    public function setZipCodeValue(?string $zipCodeValue) : \Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface;

}
