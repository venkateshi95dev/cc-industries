<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model;

use Exception;
use I95DevConnect\MessageQueue\Api\Data\I95DevErpDataInterfaceFactory;
use I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterface;
use I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Model\ResourceModel\I95DevErpMQ;
use I95DevConnect\MessageQueue\Model\ResourceModel\I95DevErpMQ\Collection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;

/**
 * Data object for ERP Messagequeue
 */
class I95DevErpMQRepository extends AbstractModel implements I95DevErpMQRepositoryInterface
{
    /**
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     * @var DataObjectProcessor
     */
    public $dataObjectProcessor;

    /**
     * @var I95DevErpDataRepositoryInterfaceFactory
     */
    public $i95DevERPDataRepository;

    /**
     * @var object
     */
    public $i95DevERPData;

    /**
     *
     * @param Context $context
     * @param Registry $registry
     * @param I95DevErpMQ $resource
     * @param Collection $resourceCollection
     * @param LoggerInterfaceFactory $logger
     * @param DataObjectProcessor $dataObjectProcessor
     * @param I95DevErpDataInterfaceFactory $i95DevERPData
     * @param I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository
     * @param array $data
     */
    public function __construct( // NOSONAR
        Context $context,
        Registry $registry,
        ResourceModel\I95DevErpMQ $resource,
        ResourceModel\I95DevErpMQ\Collection $resourceCollection,
        LoggerInterfaceFactory $logger,
        DataObjectProcessor $dataObjectProcessor,
        I95DevErpDataInterfaceFactory $i95DevERPData,
        I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->i95DevERPData = $i95DevERPData->create();
        $this->i95DevERPDataRepository = $i95DevERPDataRepository;
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
     * @param I95DevErpMQInterface $erpMessageQueueData
     * @return $this|I95DevErpMQRepository
     * @throws Exception
     */
    public function saveMQData(I95DevErpMQInterface $erpMessageQueueData)
    {
        foreach ($erpMessageQueueData->getData() as $attributeCode => $attributeData) {
            $this->setDataUsingMethod($attributeCode, $attributeData);
        }

        $msgId = $erpMessageQueueData->getMsgId();
        if ($msgId) {
            $this->setMsgId($msgId);
        }
        $this->save();

        $dataString = $erpMessageQueueData->getDataString();
        if (isset($dataString)) {
            $this->i95DevERPData->setMsgId($this->getMsgId());
            $this->i95DevERPData->setDataString($dataString);
            $this->i95DevERPDataRepository->create()->saveMQData($this->i95DevERPData);
        }

        return $this;
    }

    /**
     * Get
     *
     * @param int $msqId
     * @return false|I95DevErpMQInterface[]|I95DevErpMQRepository
     */
    public function get($msqId)
    {
        $messageQueue = $this->load($msqId);

        if ($messageQueue->getMsgId()) {
            $dataStringDetails = $this->i95DevERPDataRepository->create()->getByMsgId($msqId);
            if ($dataStringDetails->getDataId()) {
                $messageQueue->setData('data_id', $dataStringDetails->getDataId());
                $messageQueue->setData('data_string', $dataStringDetails->getDataString());
            }

            return $messageQueue;
        } else {
            $this->logger->create()->createLog(
                __METHOD__,
                'No such entity MsgId : ' . $msqId,
                'general',
                'critical'
            );
            return false;
        }
    }

    /**
     * Delete mq data
     *
     * @param string $msqId
     * @throws Exception
     */
    public function deleteMQData($msqId)
    {
        if ($this->load($msqId)) {
            $this->delete();
        }
    }
}
