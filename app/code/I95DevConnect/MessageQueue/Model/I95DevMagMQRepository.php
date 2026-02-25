<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model;

use Exception;
use I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterface;
use I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterface;
use I95DevConnect\MessageQueue\Model\ResourceModel\I95DevMagMQ;
use I95DevConnect\MessageQueue\Model\ResourceModel\I95DevMagMQ\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterface;
use I95DevConnect\MessageQueue\Helper\Data as MessageQueueHelper;
use Magento\Framework\Model\Context;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;

/**
 * Data object for Magento Messagequeue
 */
class I95DevMagMQRepository extends AbstractModel implements I95DevMagMQRepositoryInterface
{
    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var DataObjectProcessor
     */
    public $dataObjectProcessor;

    /**
     * @var object
     */
    public $I95DevMagMQRepository;

    /**
     *
     * @param Context $context
     * @param Registry $registry
     * @param I95DevMagMQ $resource
     * @param Collection $resourceCollection
     * @param Logger $logger
     * @param DataObjectProcessor $dataObjectProcessor
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        I95DevMagMQ $resource,
        Collection $resourceCollection,
        Logger $logger,
        DataObjectProcessor $dataObjectProcessor,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->dataObjectProcessor = $dataObjectProcessor;

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
     * @param I95DevMagMQInterface $magentoMQData
     * @return $this|I95DevMagMQRepository
     * @throws LocalizedException
     */
    public function saveMQData(I95DevMagMQInterface $magentoMQData)
    {
        try {
            $datetime = date('Y-m-d H:i:s');
            $magentoMQData->setUpdatedDt($datetime);

            if (empty($magentoMQData->getMsgId())) {
                $magentoMessageQueueCollection = $this
                        ->getCollection()
                        ->addFieldToFilter("erp_code", $magentoMQData->getErpCode())
                        ->addFieldToFilter("entity_code", $magentoMQData->getEntitycode())
                        ->addFieldToFilter('magento_id', $magentoMQData->getMagentoId())
                        ->addFieldToFilter('status', ['eq' => 1])
                        ->addFieldToSelect('msg_id');

                if ($magentoMessageQueueCollection->getSize() > 0) {
                    foreach ($magentoMessageQueueCollection as $collection) {
                        $magentoMQData->setMsgId($collection->getMsgId());
                    }
                }
            }
            if ($magentoMQData->getMsgId() == "" || $magentoMQData->getMsgId() < 1) {
                $magentoMQData->setCreatedDt($datetime);
            }

            $magentoMQData = $this->updateCounter($magentoMQData);

            foreach ($magentoMQData->getData() as $attributeCode => $attributeData) {
                $this->setDataUsingMethod($attributeCode, $attributeData);
            }

            $this->save();
        } catch (LocalizedException $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }

        return $this;
    }

    /**
     * Update OBMQ Counter
     *
     * @param I95DevMagMQInterface $magentoMQData
     * @return I95DevMagMQInterface
     * @throws LocalizedException
     */
    public function updateCounter($magentoMQData)
    {
        try {
            $obmqCollection = $this->getCollection()
                ->addFieldToFilter("msg_id", $magentoMQData->getMsgId())
                ->addFieldToSelect(['counter', 'status', 'destination_msg_id']);

            foreach ($obmqCollection as $collection) {
                if ($magentoMQData->getStatus() == MessageQueueHelper::PENDING) {
                    $magentoMQData->setCounter(0);
                } elseif (in_array(
                    $magentoMQData->getStatus(),
                    [MessageQueueHelper::SUCCESS, MessageQueueHelper::ERROR]
                ) && $collection->getStatus() != MessageQueueHelper::SUCCESS
                ) {
                    if ($collection->getCounter() < MessageQueueHelper::RETRY_LIMIT &&
                        $collection->getDestinationMsgId() == 0) {
                        $magentoMQData->setCounter($collection->getCounter() + 1);
                    }
                }
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
        return $magentoMQData;
    }

    /**
     * Get
     *
     * @param int $msqId
     * @return false|I95DevErpMQInterface[]|I95DevMagMQRepository
     * @throws LocalizedException
     */
    public function get($msqId)
    {
        $message_queue = $this->load($msqId);

        if ($message_queue->getMsgId()) {
            return $message_queue;
        } else {
            $this->logger->createLog(
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
