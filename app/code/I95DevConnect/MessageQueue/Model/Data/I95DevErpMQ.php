<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\Data;

use I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * I95Dev ERP MessageQueue Model
 */
class I95DevErpMQ extends AbstractModel implements I95DevErpMQInterface //NOSONAR
{
    /**
     * @var string
     */
    public $dataString;

    /**
     * @var string
     */
    public $dataId;

    /**
     * Get Message Queue Id
     *
     * @return void
     */
    public function getMsgId()
    {
        $this->getData(self::MSG_ID);
    }

    /**
     * Set Message Queue Id
     *
     * @param int $msgId
     * @return $this
     */
    public function setMsgId($msgId)
    {
        return $this->setData(self::MSG_ID, $msgId);
    }

    /**
     * Get Erp Code
     *
     * @return string
     */
    public function getErpCode()
    {
        return $this->getData(self::ERP_CODE);
    }

    /**
     * Get Erp Code
     *
     * @param string $erpCode
     * @return $this
     */
    public function setErpCode($erpCode)
    {
        return $this->setData(self::ERP_CODE, $erpCode);
    }

    /**
     * Get Entity code
     *
     * @return string|null
     */
    public function getEntityCode()
    {
        return $this->getData(self::ENTITY_CODE);
    }

    /**
     * Set Entity Code
     *
     * @param string $entityCode
     * @return I95DevErpMQ
     */
    public function setEntityCode($entityCode)
    {
        return $this->setData(self::ENTITY_CODE, $entityCode);
    }

    /**
     * Get created date
     *
     * @return string|null
     */
    public function getCreatedDt()
    {
        return $this->getData(self::CREATED_DT);
    }

    /**
     * Set created date
     *
     * @param string $createdDt
     * @return $this
     */
    public function setCreatedDt($createdDt)
    {
        return $this->setData(self::CREATED_DT, $createdDt);
    }

    /**
     * Get updated date
     *
     * @return string|null
     */
    public function getUpdatedDt()
    {
        return $this->getData(self::UPDATED_DT);
    }

    /**
     * Set updated date
     *
     * @param string $updatedDt
     * @return $this
     */
    public function setUpdatedDt($updatedDt)
    {
        return $this->setData(self::UPDATED_DT, $updatedDt);
    }

    /**
     * Get status
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get magento Id
     *
     * @return void
     */
    public function getMagentoId()
    {
        return $this->getData(self::MAGENTO_ID);
    }

    /**
     * Set Magento Id
     *
     * @param int $magentoId
     * @return $this
     */
    public function setMagentoId($magentoId)
    {
        return $this->setData(self::MAGENTO_ID, $magentoId);
    }

    /**
     * Get target id
     *
     * @return void
     */
    public function getTargetId()
    {
        return $this->getData(self::TARGET_ID);
    }

    /**
     * Set target id
     *
     * @param int $targetId
     * @return $this
     */
    public function setTargetId($targetId)
    {
        return $this->setData(self::TARGET_ID, $targetId);
    }

    /**
     * Get error id
     *
     * @return int|null
     */
    public function getErrorId(): ?int
    {
        return $this->getData(self::ERROR_ID);
    }

    /**
     * Set error id
     *
     * @param int $errorId
     * @return $this
     */
    public function setErrorId($errorId)
    {
        return $this->setData(self::ERROR_ID, $errorId);
    }

    /**
     * Get counter
     *
     * @return int|null
     */
    public function getCounter(): ?int
    {
        return $this->getData(self::COUNTER);
    }

    /**
     * Set counter
     *
     * @param int $counter
     * @return $this
     */
    public function setCounter($counter)
    {
        return $this->setData(self::COUNTER, $counter);
    }

    /**
     * Get reference name
     *
     * @return string|null
     */
    public function getRefName()
    {
        return $this->getData(self::REF_NAME);
    }

    /**
     * Set reference name
     *
     * @param string $refName
     * @return $this
     */
    public function setRefName($refName)
    {
        return $this->setData(self::REF_NAME, $refName);
    }

    /**
     * Get if there is error in data
     *
     * @return int|null
     */
    public function getIsDataError(): ?int
    {
        return $this->getData(self::IS_DATA_ERROR);
    }

    /**
     * Set if there is error in data
     *
     * @param int $isDataError
     * @return $this
     */
    public function setIsDataError($isDataError)
    {
        return $this->setData(self::IS_DATA_ERROR, $isDataError);
    }

    /**
     * Get data id
     *
     * @return null|int
     */
    public function getDataId()
    {
        return $this->dataId;
    }

    /**
     * Get data id
     *
     * @param null|string $dataId
     * @return $this
     */
    public function setDataId($dataId)
    {
        $this->dataId =  $dataId;
        return $this;
    }

    /**
     * Get data string
     *
     * @return string|null
     */
    public function getDataString()
    {
        return $this->dataString;
    }

    /**
     * Set data string
     *
     * @param string|null $dataString
     * @return $this
     */
    public function setDataString($dataString)
    {
        $this->dataString =  $dataString;
        return $this;
    }

    /**
     * Get erp msg id
     *
     * @return int|null
     */
    public function getDestinationMsgId()
    {
        return $this->getData(self::DESTINATION_MSG_ID);
    }

    /**
     * Set erp msg id
     *
     * @param int $destinationMsgId
     *
     * @return void
     */
    public function setDestinationMsgId($destinationMsgId)
    {
        $this->setData(self::DESTINATION_MSG_ID, $destinationMsgId);
    }

    /**
     * Get additional info
     *
     * @return null|string
     */
    public function getAdditionalInfo()
    {
        return $this->getData(self::ADDITIONAL_INFO);
    }

    /**
     * Set additional info
     *
     * @param null|string $additionalInfo
     * @return void
     */
    public function setAdditionalInfo($additionalInfo)
    {
        $this->setData(self::ADDITIONAL_INFO, $additionalInfo);
    }
    /**
     * Get response counter
     *
     * @return int|null
     */
    public function getResponseCounter(): ?int
    {
        return $this->getData(self::RESPONSE_COUNTER);
    }
    /**
     * Set response counter
     *
     * @param int $counter
     * @return $this
     */
    public function setResponseCounter($counter)
    {
        return $this->setData(self::RESPONSE_COUNTER, $counter);
    }
}
