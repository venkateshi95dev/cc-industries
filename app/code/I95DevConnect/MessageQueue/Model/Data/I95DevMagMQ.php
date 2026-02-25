<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\Data;

use I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * I95Dev Magento MessageQueue Model
 */
class I95DevMagMQ extends AbstractModel implements I95DevMagMQInterface //NOSONAR
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
     * @return int|null
     */
    public function getMsgId()
    {
        return $this->getData(self::MSG_ID);
    }

    /**
     * Set Message Queue Id
     *
     * @param int $msgId
     *
     * @return void
     */
    public function setMsgId($msgId)
    {
        $this->setData(self::MSG_ID, $msgId);
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
     * @return void
     */
    public function setErpCode($erpCode)
    {
        $this->setData(self::ERP_CODE, $erpCode);
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
     * Set entity code
     *
     * @param string $entityCode
     * @return void
     */
    public function setEntityCode($entityCode)
    {
        $this->setData(self::ENTITY_CODE, $entityCode);
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
     * @return void
     */
    public function setCreatedDt($createdDt)
    {
        $this->setData(self::CREATED_DT, $createdDt);
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
     * @return void
     */
    public function setUpdatedDt($updatedDt)
    {
        $this->setData(self::UPDATED_DT, $updatedDt);
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
     * @return void
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * Get magento Id
     *
     * @return int|null
     */
    public function getMagentoId()
    {
        return $this->getData(self::MAGENTO_ID);
    }

    /**
     * Set Magento Id
     *
     * @param int $magentoId
     * @return void
     */
    public function setMagentoId($magentoId)
    {
        $this->setData(self::MAGENTO_ID, $magentoId);
    }

    /**
     * Get target id
     *
     * @return void
     */
    public function getTargetId()
    {
        $this->getData(self::TARGET_ID);
    }

    /**
     * Set target id
     *
     * @param int $targetId
     * @return void
     */
    public function setTargetId($targetId)
    {
        $this->setData(self::TARGET_ID, $targetId);
    }

    /**
     * Get Updated By
     *
     * @return int|null
     */
    public function getUpdatedBy()
    {
        return $this->getData(self::UPDATED_BY);
    }

    /**
     * Set Updated By
     *
     * @param int $updatedBy
     * @return void
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->setData(self::UPDATED_BY, $updatedBy);
    }

    /**
     * Get error id
     *
     * @return int|null
     */
    public function getErrorId()
    {
        return $this->getData(self::ERROR_ID);
    }

    /**
     * Set error id
     *
     * @param int $errorId
     * @return void
     */
    public function setErrorId($errorId)
    {
        $this->setData(self::ERROR_ID, $errorId);
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
     * @return void
     */
    public function setDestinationMsgId($destinationMsgId)
    {
        $this->setData(self::DESTINATION_MSG_ID, $destinationMsgId);
    }

    /**
     * Get data string
     *
     * @return null|string
     */
    public function getDestinationUpdatedDate()
    {
        return $this->getData(self::DESTINATION_UPADTED_DATE);
    }

    /**
     * Get data string
     *
     * @param null|string $destinationUpdatedDate
     * @return void
     */
    public function setDestinationUpdatedDate($destinationUpdatedDate)
    {
        $this->setData(self::DESTINATION_UPADTED_DATE, $destinationUpdatedDate);
    }

    /**
     * Get counter
     *
     * @return int|null
     */
    public function getCounter()
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
}
