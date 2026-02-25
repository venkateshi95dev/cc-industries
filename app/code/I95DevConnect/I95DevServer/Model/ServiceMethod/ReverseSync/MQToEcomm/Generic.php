<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Model\ServiceMethod\ReverseSync\MQToEcomm;

use Exception;
use I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\DataPersistenceInterface;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class to pick records from Inbound MQ and sent to Magento
 */
class Generic
{
    /**
     * @var I95DevErpMQRepositoryInterfaceFactory
     */
    public $i95DevErpMQRepository;

    /**
     * @var DataPersistenceInterface
     */
    public $dataPersistence;

    /**
     * @var I95DevErpMQInterfaceFactory
     */
    public $i95DevErpMQfactory;

    /**
     * @var ManagerInterface
     */
    public $eventManager;

    /**
     * @param I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository
     * @param DataPersistenceInterface              $dataPersistence
     * @param I95DevErpMQInterfaceFactory           $i95DevErpMQfactory
     * @param ManagerInterface                      $eventManager
     */
    public function __construct(
        I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository,
        DataPersistenceInterface $dataPersistence,
        I95DevErpMQInterfaceFactory $i95DevErpMQfactory,
        ManagerInterface $eventManager
    ) {
        $this->i95DevErpMQRepository = $i95DevErpMQRepository;
        $this->dataPersistence = $dataPersistence;
        $this->i95DevErpMQfactory = $i95DevErpMQfactory;
        $this->eventManager = $eventManager;
    }

    /**
     * Getting collection from Inbound MQ and processing one by one to save in Magento
     *
     * @param  Object $mqCollection
     * @throws Exception
     */
    public function reverseSyncMQtoMagento($mqCollection)
    {
        $product_mapping_counter = 0;
        foreach ($mqCollection as $mqRecordCollection) {
            $message_queue = $this->i95DevErpMQRepository->create()->get($mqRecordCollection->getId());

            if ($message_queue->getEntityCode() == "product" && $product_mapping_counter == 0) {
                $this->eventManager->dispatch('fetch_product_mapping_reverse');
                $product_mapping_counter = 1;
            }

            //@Hrusikesh Removed code for update MQ status to processing
            $response = $this->dataPersistence->createEntity(
                $message_queue->getEntityCode(),
                $message_queue->getDataString(),
                $message_queue->getMsgId(),
                $message_queue->getErpCode()
            );

            if (isset($response)) {
                $this->dataPersistence->updateErpMQStatus(
                    $response->getStatus(),
                    $response->getResultdata(),
                    $response->getMessage(),
                    $mqRecordCollection->getId(),
                    $response->getCode()
                );
            } else {
                $this->dataPersistence->updateErpMQStatus(
                    Data::ERROR,
                    null,
                    'Some issue occur while saving data. Please contact admin.',
                    $mqRecordCollection->getId(),
                    105
                );
            }
        }
    }
}
