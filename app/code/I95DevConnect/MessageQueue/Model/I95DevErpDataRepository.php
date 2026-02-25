<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model;

use Exception;
use I95DevConnect\MessageQueue\Api\Data\I95DevErpDataInterface;
use I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterface;
use I95DevConnect\MessageQueue\Model\ResourceModel\I95DevErpData;
use I95DevConnect\MessageQueue\Model\ResourceModel\I95DevErpData\Collection;
use Magento\Framework\Model\AbstractModel;
use I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Data object for ERP data
 */
class I95DevErpDataRepository extends AbstractModel implements I95DevErpDataRepositoryInterface
{
    /**
     * @var Logger
     */
    public $logger;

    /**
     *
     * @param Context $context
     * @param Registry $registry
     * @param I95DevErpData $resource
     * @param Collection $resourceCollection
     * @param Logger $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        I95DevErpData $resource,
        Collection $resourceCollection,
        Logger $logger,
        array $data = []
    ) {
        $this->logger = $logger;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Save mq data
     *
     * @param I95DevErpDataInterface $erpData
     * @return $this|I95DevErpDataInterface
     * @throws Exception
     */
    public function saveMQData(I95DevErpDataInterface $erpData)
    {
        foreach ($erpData->getData() as $attributeCode => $attributeData) {
            $this->setDataUsingMethod($attributeCode, $attributeData);
        }
        $msgId = $erpData->getMsgId();
        if ($msgId) {
            $checkData = $this->getByMsgId($msgId);
            if (!empty($checkData)) {
                $this->setDataId($checkData->getDataId());
            }

            $this->save();
        }
        return $this;
    }

    /**
     * Get data string of erp message queue on basis of msgId
     *
     * @param int $msgId
     * @return Object
     * @throws NoSuchEntityException
     */
    public function getByMsgId($msgId)
    {
        $erpData = $this->load($msgId, 'msg_id');
        if ($erpData->getDataId()) {
            return $erpData;
        } else {
            return $this;
        }
    }

    /**
     * Return erp message queue data. In case msgId not found exception will be thrown.
     *
     * @param string $dataId
     * @return I95DevErpMQInterface[]
     * @throws NoSuchEntityException
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function get($dataId)
    {
        $erpData = $this->load($dataId);

        if ($erpData->getDataId()) {
            return $erpData;
        } else {
            return $this;
        }
    }

    /**
     * Delete erp message queue data
     *
     * If error occurred during the delete exception will be thrown.
     *
     * @param int $dataId
     *
     * @throws Exception
     */
    public function deleteMQData($dataId)
    {
        if ($this->load($dataId)) {
            $this->delete();
        }
    }
}
