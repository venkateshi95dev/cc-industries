<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Model\ServiceMethod\ForwardSync\MQToErp;

use Exception;
use I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Config;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\ErpOrderStatus;
use I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class to get IDs from Outbound MQ and send to ERP
 */
class SendIds
{
    public const STATUS = "status";
    public const PKTSIZE = "packetSize";
    public const MSG_ID = "msg_id";
    public const MSGID = "messageId";

    /**
     * @var string
     */
    public $erpName = 'ERP';

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var $packetSize
     */
    public $packetSize;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var Config
     */
    public $helperConfig;

    /**
     * @var Configurable
     */
    public $configurable;

    /**
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    public $i95DevMagMQRepository;

    /**
     * @var ErpOrderStatus
     */
    public $erpOrderStatus;

    /**
     * @var I95DevMagMQInterfaceFactory
     */
    public $I95DevMagMQFactory;

    /**
     * @var ErrorUpdateDataFactory
     */
    public $messageErrorModel;
    
    /**
     * @var ManagerInterface
     */
    public $eventManager;

    /**
     * @param I95DevMagMQRepositoryInterfaceFactory $i95DevMagMQRepository
     * @param DateTime                              $date
     * @param ScopeConfigInterface                  $scopeConfig
     * @param LoggerInterface                       $logger
     * @param ErpOrderStatus                        $erpOrderStatus
     * @param I95DevMagMQInterfaceFactory           $I95DevMagMQFactory
     * @param ErrorUpdateDataFactory                $messageErrorModel
     * @param Config                                $helperConfig
     * @param Configurable                          $configurable
     * @param ManagerInterface                      $eventManager
     */
    public function __construct( // NOSONAR
        I95DevMagMQRepositoryInterfaceFactory $i95DevMagMQRepository,
        DateTime $date,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        ErpOrderStatus $erpOrderStatus,
        I95DevMagMQInterfaceFactory $I95DevMagMQFactory,
        ErrorUpdateDataFactory $messageErrorModel,
        Config $helperConfig,
        Configurable $configurable,
        ManagerInterface $eventManager
    ) {
        $this->i95DevMagMQRepository = $i95DevMagMQRepository;
        $this->date = $date;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->erpOrderStatus = $erpOrderStatus;
        $this->I95DevMagMQFactory = $I95DevMagMQFactory;
        $this->messageErrorModel = $messageErrorModel;
        $this->helperConfig = $helperConfig;
        $this->configurable = $configurable;
        $this->eventManager = $eventManager;
    }

    /**
     * Returns the list of updated outbound IDs
     *
     * @param  string $entityCode
     * @param  array  $requestData
     * @return array
     * @throws Exception
     */
    public function defaultUpdatedEntityIds($entityCode, $requestData)
    {
        try {
            $responseData = [];
            if (isset($requestData[self::PKTSIZE])) {
                $this->packetSize = $requestData[self::PKTSIZE];
                $responseData = $this->getOutboundUpdatedCollection($entityCode);
            } elseif (count($requestData['requestData']) > 0) {
                $responseData = $this->getOutboundCollectionByIds($requestData['requestData']);
            } elseif ($requestData[self::PKTSIZE] === null) {
                $responseData = $this->getOutboundUpdatedCollection($entityCode);
            }
            $this->erpName = isset($requestData['erp_name']) ? $requestData['erp_name'] : __('ERP');
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                Data::I95EXC,
                'critical'
            );
        }

        return $responseData;
    }

    /**
     * Get updated id collection from outbound MQ
     *
     * @param  string $entityCode
     * @return array
     * @throws Exception
     */
    private function getOutboundUpdatedCollection($entityCode)
    {
        $responseData = [];
        try {
            $collection = $this->getUpdatedCollection($entityCode);
            if ($collection->getSize() <= 0) {
                return $responseData;
            }

            $product_mapping_counter = 0;
            foreach ($collection as $recordCollection) {
                /* @updatedBy Hrusikesh added condition to skip sending child product in response
                 * for configurable product Issue Id: 24586278
                 */
                $parentConfigObject = $this->configurable->getParentIdsByChild($recordCollection->getMagentoId());
                if ($this->checkParentId($parentConfigObject, $entityCode, $recordCollection)) {
                    continue;
                }

                $orderCheckStatus = $this->checkOrder($entityCode, $recordCollection);
                if ($orderCheckStatus) {
                    continue;
                }

                if ($entityCode == "product" && $product_mapping_counter == 0) {
                    $this->eventManager->dispatch('fetch_product_mapping_forward');
                    $product_mapping_counter = 1;
                }

                $data['magentoId'] = $recordCollection->getMagentoId();
                $data[self::MSGID] = $recordCollection->getMsgId();
                $responseData[] = $data;
            }
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                Data::I95EXC,
                'critical'
            );
        }

        return $responseData;
    }

    /**
     * Get updated collection
     *
     * @param  string $entityCode
     * @return mixed
     */
    public function getUpdatedCollection($entityCode)
    {
        $this->erpName = $this->helperConfig->getConfigValues()->getData('component');
        $collection = $this->i95DevMagMQRepository->create()->getCollection();
        $collection->addFieldToSelect([self::STATUS, 'destination_msg_id', 'magento_id']);
        $collection->addFieldToFilter("erp_code", $this->erpName);
        $collection->addFieldToFilter("entity_code", $entityCode);
        $collection->getSelect()->where(
            self::STATUS.'='.Data::PENDING.' OR 
            ('.self::STATUS.'='.Data::ERROR.' AND counter < '.Data::RETRY_LIMIT.' AND destination_msg_id = 0)'
        );
        $collection->getSelect()->order(self::MSG_ID, 'ASC');

        if (!empty($this->packetSize)) {
            $collection->getSelect()->limit($this->packetSize);
        }

        return $collection;
    }

    /**
     * Check parent id
     *
     * @param object $parentConfigObject
     * @param string $entityCode
     * @param mixed $recordCollection
     * @return bool
     */
    public function checkParentId($parentConfigObject, $entityCode, $recordCollection)
    {
        $parentId = isset($parentConfigObject[0]) ? $parentConfigObject[0] : null;
        if ($entityCode == 'product' && $parentId !== null) {
            $errorDataModel = $this->messageErrorModel->create();
            $errorDataModel->setMsg('Variants creation not supported from Magento');
            $errorDataModel->save();
            $errorId = $errorDataModel->getId();
            $I95DevMagMQ = $this->I95DevMagMQFactory->create();
            $I95DevMagMQ->setMsgId($recordCollection->getMsgId());
            $I95DevMagMQ->setStatus(Data::ERROR);
            $I95DevMagMQ->setErrorId($errorId);
            $this->i95DevMagMQRepository->create()->saveMQData($I95DevMagMQ);
            return true;
        }
        return false;
    }

    /**
     * Check order
     *
     * @param string $entityCode
     * @param object $recordCollection
     * @return bool
     */
    public function checkOrder($entityCode, $recordCollection)
    {
        if ($entityCode == "order"
            && !empty($message = $this->erpOrderStatus->isOrderSyncable($recordCollection->getMagentoId()))
        ) {
            $errorDataModel = $this->messageErrorModel->create();
            $errorDataModel->setMsg($message);
            $errorDataModel->save();
            $errorId = $errorDataModel->getId();
            $I95DevMagMQ = $this->I95DevMagMQFactory->create();
            $I95DevMagMQ->setMsgId($recordCollection->getMsgId());
            $I95DevMagMQ->setStatus(Data::ERROR);
            $I95DevMagMQ->setErrorId($errorId);
            $this->i95DevMagMQRepository->create()->saveMQData($I95DevMagMQ);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Out Bound MQ collection by Message Queue Ids
     *
     * @param  array $requestData
     * @return array
     */
    private function getOutboundCollectionByIds($requestData)
    {
        $responseData = [];
        $idList = array_column($requestData, self::MSGID);
        $collection = $this->i95DevMagMQRepository->create()->getCollection();
        $collection->addFieldToSelect(['magento_id', self::MSG_ID]);
        $collection->addFieldToFilter(self::MSG_ID, ['in' => $idList]);
        $collection->getSelect()->order(self::MSG_ID, 'ASC');
        if ($collection->getSize() > 0) {
            foreach ($collection as $recordCollection) {
                $data['magentoId'] = $recordCollection->getMagentoId();
                $data[self::MSGID] = $recordCollection->getMsgId();
                $responseData[] = $data;
            }
        }
        return $responseData;
    }
}
