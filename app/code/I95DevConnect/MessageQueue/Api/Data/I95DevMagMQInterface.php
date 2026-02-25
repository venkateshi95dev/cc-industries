<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Api\Data;

/**
 * Represents Data Object for a Magento MessageQueue Data
 */
interface I95DevMagMQInterface //NOSONAR
{
    public const MSG_ID = 'msg_id';
    public const ERP_CODE = 'erp_code';
    public const ENTITY_CODE = 'entity_code';
    public const CREATED_DT = 'created_dt';
    public const UPDATED_DT = 'updated_dt';
    public const STATUS = 'status';
    public const MAGENTO_ID = 'magento_id';
    public const TARGET_ID = 'target_id';
    public const UPDATED_BY = 'updated_by';
    public const ERROR_ID = 'error_id';
    public const DESTINATION_UPADTED_DATE = 'destination_updated_dt';
    public const DESTINATION_MSG_ID = 'destination_msg_id';
    public const ADDITIONAL_INFO = 'additional_info';
    public const COUNTER = 'counter';

    /**
     * Get Message Queue Id
     *
     * @return int|null
     */
    public function getMsgId();

    /**
     * Set Message Queue Id
     *
     * @param int $msgId
     * @return $this
     */
    public function setMsgId($msgId);

    /**
     * Get Erp Code
     *
     * @return string
     */
    public function getErpCode();

    /**
     * Get Erp Code
     *
     * @param string $erpCode
     * @return $this
     */
    public function setErpCode($erpCode);

    /**
     * Get Entity code
     *
     * @return string|null
     */
    public function getEntityCode();

    /**
     * Set entity code
     *
     * @param string $entityCode
     * @return $this
     */
    public function setEntityCode($entityCode);

    /**
     * Get created date
     *
     * @return string|null
     */
    public function getCreatedDt();

    /**
     * Set created date
     *
     * @param string $createdDt
     * @return $this
     */
    public function setCreatedDt($createdDt);

    /**
     * Get updated date
     *
     * @return string|null
     */
    public function getUpdatedDt();

    /**
     * Set updated date
     *
     * @param string $updatedDt
     * @return $this
     */
    public function setUpdatedDt($updatedDt);

    /**
     * Get status
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get magento id
     *
     * @return string|null
     */
    public function getMagentoId();

    /**
     * Set magento id
     *
     * @param string $magentoId
     * @return $this
     */
    public function setMagentoId($magentoId);

    /**
     * Get target id
     *
     * @return int|null
     */
    public function getTargetId();

    /**
     * Set target id
     *
     * @param int $targetId
     * @return $this
     */
    public function setTargetId($targetId);

    /**
     * Get UpdatedBy
     *
     * @return int|null
     */
    public function getUpdatedBy();

    /**
     * Set UpdatedBy
     *
     * @param int $updatedBy
     * @return $this
     */
    public function setUpdatedBy($updatedBy);

    /**
     * Get reference name
     *
     * @return string|null
     */
    public function getErrorId();

    /**
     * Set reference name
     *
     * @param string $errorId
     * @return $this
     */
    public function setErrorId($errorId);

    /**
     * Get data string
     *
     * @return null|string
     */
    public function getDestinationUpdatedDate();

    /**
     * Get data string
     *
     * @param null|string $destinationUpdatedDate
     * @return $this
     */
    public function setDestinationUpdatedDate($destinationUpdatedDate);

    /**
     * Get destination msg id
     *
     * @return null|string
     */
    public function getDestinationMsgId();

    /**
     * Get destination msg id
     *
     * @param null|string $destinationMsgId
     * @return $this
     */
    public function setDestinationMsgId($destinationMsgId);

    /**
     * Get counter
     *
     * @return int|null
     */
    public function getCounter();

    /**
     * Set counter
     *
     * @param int $counter
     * @return $this
     */
    public function setCounter($counter);
}
