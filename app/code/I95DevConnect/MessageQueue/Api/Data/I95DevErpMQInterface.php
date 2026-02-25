<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Api\Data;

/**
 * Represents Data Object for a ERP MessageQueue Data
 */
interface I95DevErpMQInterface //NOSONAR
{
    public const MSG_ID = 'msg_id';
    public const ERP_CODE = 'erp_code';
    public const ENTITY_CODE = 'entity_code';
    public const CREATED_DT = 'created_dt';
    public const UPDATED_DT = 'updated_dt';
    public const STATUS = 'status';
    public const MAGENTO_ID = 'magento_id';
    public const TARGET_ID = 'target_id';
    public const ERROR_ID = 'error_id';
    public const COUNTER = 'counter';
    public const REF_NAME = 'ref_name';
    public const IS_DATA_ERROR = 'is_data_error';
    public const DESTINATION_MSG_ID = 'destination_msg_id';
    public const ADDITIONAL_INFO = 'additional_info';
    public const RESPONSE_COUNTER = 'response_counter';

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
     * Get error id
     *
     * @return int|null
     */
    public function getErrorId();

    /**
     * Set error id
     *
     * @param int $errorId
     * @return $this
     */
    public function setErrorId($errorId);

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

    /**
     * Get reference name
     *
     * @return string|null
     */
    public function getRefName();

    /**
     * Set reference name
     *
     * @param string $refName
     * @return $this
     */
    public function setRefName($refName);

    /**
     * Get if there is error in data
     *
     * @return int|null
     */
    public function getIsDataError();

    /**
     * Set if there is error in data
     *
     * @param int $isDataError
     * @return $this
     */
    public function setIsDataError($isDataError);

    /**
     * Get data id
     *
     * @return null|int
     */
    public function getDataId();

    /**
     * Get data id
     *
     * @param null|string $dataId
     * @return $this
     */
    public function setDataId($dataId);

    /**
     * Get data string
     *
     * @return null|string
     */
    public function getDataString();

    /**
     * Get data string
     *
     * @param null|string $dataString
     * @return $this
     */
    public function setDataString($dataString);

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
     * Get Erp Code
     *
     * @return string
     */
    public function getErpCode();

    /**
     * Set Erp Code
     *
     * @param string $erpCode
     * @return $this
     */
    public function setErpCode($erpCode);

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
     * Get additional info
     *
     * @return null|string
     */
    public function getAdditionalInfo();

    /**
     * Set additional info
     *
     * @param null|string $additionalInfo
     * @return $this
     */
    public function setAdditionalInfo($additionalInfo);
    /**
     * Get response counter
     *
     * @return int|null
     */
    public function getResponseCounter();
    /**
     * Set response counter
     *
     * @param int $counter
     * @return $this
     */
    public function setResponseCounter($counter);
}
