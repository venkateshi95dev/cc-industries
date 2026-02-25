<?php

/**
 * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
 */

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Model\ServiceMethod;

use Exception;
use I95DevConnect\I95DevServer\Api\I95DevServerRepositoryInterfaceFactory;
use I95DevConnect\I95DevServer\Model\ServiceMethod\ReverseSync\ErpToMQ\Generic;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterface;
use I95DevConnect\MessageQueue\Api\I95DevReverseResponseInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\DataPersistence;
use I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory;
use I95DevConnect\MessageQueue\Model\I95DevResponse;
use I95DevConnect\MessageQueue\Model\Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for ERP to Magento sync
 */
class ReverseSync extends AbstractServiceMethod // NOSONAR
{
    public const MSG = "message";
    public const TARGETID = "targetId";
    public const SOURCEID = "sourceId";
    public const MSGID = "messageId";
    public const MSG_ID = "msg_id";
    public const MSGID_C = "MessageId";
    public const ISCHILD = "isChild";
    public const STATUS = "status";
    public const RESULT = "result";
    public const TARGET_ID = "target_id";
    public const ENTITY_NAME = "entityName";
    public const ENTITY_CODE = "entity_code";
    public const ERROR_ID = "error_id";
    public const MGT_ID = "magento_id";
    public const INPUT_DATA = "inputData";

    /**
     * @var Generic
     */
    public $genericErpToMQ;
    /**
     * @var ReverseSync\MQToEcomm\Generic
     */
    public $genericMQToMagento;
    /**
     * @var DataPersistence
     */
    public $persistenceHelper;
    /**
     * @var Data
     */
    public $dataHelper;
    /**
     * @var I95DevErpMQRepositoryInterface
     */
    public $i95DevErpMQ;
    /**
     * @var $currentEntityProperties
     */
    public $currentEntityProperties;
    /**
     * @var $responseData
     */
    public $responseData;
    /**
     * @var I95DevReverseResponseInterfaceFactory
     */
    public $i95DevRevResponse;
    /**
     * @var null
     */
    public $parentData = null;
    /**
     * @var array
     */
    public $inputData = [];
    /**
     * @var $childEntityName
     */
    public $childEntityName;
    /**
     * @var ErrorUpdateDataFactory
     */
    public $errorUpdateData;
    /**
     * @var $recordResponse
     */
    public $recordResponse;

    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var I95DevServerRepositoryInterfaceFactory
     */
    public $i95DevRepository;

    /**
     * Constructor for DI
     *
     * @param Logger                                 $logger
     * @param I95DevResponse                         $i95DevResponse
     * @param Generic                                $genericErpToMQ
     * @param ReverseSync\MQToEcomm\Generic          $genericMQToMagento
     * @param DataPersistence                        $persistenceHelper
     * @param Data                                   $dataHelper
     * @param I95DevErpMQRepositoryInterface         $i95DevErpMQ
     * @param I95DevReverseResponseInterfaceFactory  $i95DevRevResponse
     * @param ScopeConfigInterface                   $scopeConfigInterface
     * @param ErrorUpdateDataFactory                 $errorUpdateData
     * @param StoreManagerInterface                  $storeManager
     * @param Manager                                $eventManager
     * @param I95DevServerRepositoryInterfaceFactory $i95DevRepository
     */
    public function __construct( // NOSONAR
        Logger $logger,
        I95DevResponse $i95DevResponse,
        Generic $genericErpToMQ,
        ReverseSync\MQToEcomm\Generic $genericMQToMagento,
        DataPersistence $persistenceHelper,
        Data $dataHelper,
        I95DevErpMQRepositoryInterface $i95DevErpMQ,
        I95DevReverseResponseInterfaceFactory $i95DevRevResponse,
        ScopeConfigInterface $scopeConfigInterface,
        ErrorUpdateDataFactory $errorUpdateData,
        StoreManagerInterface $storeManager,
        Manager $eventManager,
        I95DevServerRepositoryInterfaceFactory $i95DevRepository
    ) {
        $this->genericErpToMQ = $genericErpToMQ;
        $this->genericMQToMagento = $genericMQToMagento;
        $this->persistenceHelper = $persistenceHelper;
        $this->dataHelper = $dataHelper;
        $this->i95DevErpMQ = $i95DevErpMQ;
        $this->i95DevRevResponse = $i95DevRevResponse;
        $this->errorUpdateData = $errorUpdateData;
        $this->eventManager = $eventManager;
        $this->i95DevRepository = $i95DevRepository;
        parent::__construct($logger, $i95DevResponse, $scopeConfigInterface, $storeManager);
    }

    /**
     * Method to insert ERP string in to Inbound MQ table
     *
     * @param  string      $methodName
     * @param  string      $inputString
     * @param  object      $currentMethodProperties
     * @param  null|string $erpName
     * @return Object
     * @throws LocalizedException
     */
    public function syncDataToMQ($methodName, $inputString, &$currentMethodProperties, $erpName = null)
    {
        $this->erpName = !empty($erpName) ? $erpName : __('ERP');
        $this->currentMethodProperties = $currentMethodProperties;

        $recordStatus = true;
        $recordMessage = "";

        $this->setCurrentEntityCode($currentMethodProperties);

        $inputArray = $this->convertInputStringToArray($inputString);

        /**
         * @updatedBy Debashis S. Gopal. Return false with error message. If decoding failed for $inputString.
         */
        if (empty($inputArray)) {
            $this->setResponse(false, 'Unable decode input string.', []);
            return $this->i95DevResponse;
        }

        $finalString = $this->processInputArray($inputArray);
        foreach ($finalString as $singleRecord) {
            $messageId = null;
            try {
                $entityRoute = $this->initialEntityRoute($currentMethodProperties, $singleRecord, $this->inputData);
                $messageId = $entityRoute[self::MSGID];
            } catch (LocalizedException $ex) {
                $recordMessage = $ex->getMessage();
                $recordStatus = false;
                $messageId = null;
                $this->logger->createLog(
                    __METHOD__,
                    $methodName . " :: " . $ex->getMessage(),
                    LoggerInterface::I95EXC,
                    'critical'
                );
            }

            if (isset($currentMethodProperties['isParent']) || isset($currentMethodProperties[self::ISCHILD])) {
                $this->callCreateResponse($currentMethodProperties, $messageId, $singleRecord);
            } else {
                $this->responseData[] = ($this->parentData !== null) ?
                    $this->parentData : $this->createResponse($messageId, $singleRecord);
            }
        }

        $this->setResponse($recordStatus, $recordMessage, $this->responseData);

        return $this->i95DevResponse;
    }

    /**
     * Set current entity code
     *
     * @param array $currentMethodProperties
     */
    public function setCurrentEntityCode($currentMethodProperties)
    {
        $currentEntityCode = '';
        if (isset($currentMethodProperties['entityCode'])) {
            $currentEntityCode = $currentMethodProperties['entityCode'];
        } else {
            $this->setResponse("Error", "Entity code not exists");
        }
        $this->getCurrentEnityProperties($currentEntityCode);
    }

    /**
     * Process input array
     *
     * @param  array $inputArray
     * @return array
     */
    public function processInputArray($inputArray)
    {
        if (isset($inputArray['RequestData'])) {
            $requestDataArr = $inputArray['RequestData'];
        } elseif (isset($inputArray['requestData'])) {
            $requestDataArr = $inputArray['requestData'];
        } else {
            $requestDataArr = [];
        }

        return $this->getFinalString($inputArray, $requestDataArr);
    }

    /**
     * Get final string
     *
     * @param  array $inputArray
     * @param  array $requestDataArr
     * @return array
     */
    public function getFinalString($inputArray, $requestDataArr)
    {
        $finalString = [];
        if (!empty($requestDataArr) && count($requestDataArr) > 0) {
            foreach ($requestDataArr as $requestData) {
                if (isset($requestData['InputData'])) {
                    $inputDataString = $requestData['InputData'];
                } elseif (isset($requestData['inputData'])) {
                    $inputDataString = $requestData['inputData'];
                } else {
                    $inputDataString = '';
                }
                $decryptedString = $this->convertInputStringToArray($this->decryptDES($inputDataString));
                if (isset($requestData[self::MSGID]) && $requestData[self::MSGID] !== 0) {
                    $decryptedString['DestinationId'] = $requestData[self::MSGID];
                }
                $finalString[] = $decryptedString;
            }
        } else {
            $finalString = $inputArray;
        }

        return $finalString;
    }

    /**
     * Initial entity route
     *
     * @param  array $currentMethodProperties
     * @param  array $singleRecord
     * @param  array $inputData
     * @return array
     */
    public function initialEntityRoute($currentMethodProperties, $singleRecord, $inputData)
    {
        if (isset($currentMethodProperties['classObject']) && isset($currentMethodProperties['methodName'])
        ) {
            $this->parentData = null;
            /* Called for customer and address entity */
            $customClassModelObject = $currentMethodProperties['classObject'];
            $methodName = $currentMethodProperties['methodName'];
            $messageId = $customClassModelObject->$methodName($singleRecord);

            if (isset($currentMethodProperties['isDivided']) && count($this->inputData) > 0) {
                $inputData[$this->childEntityName] = $this->inputData;
                $this->parentData->setInputdata($this->encryptAES(json_encode($inputData)));
            }
        } else {
            /* Called for all entity - Generic function call */
            $messageId = $this->genericErpToMQ->defaultMessageQueueInsert(
                $singleRecord,
                $this
            );
        }
        return [self::MSGID => $messageId];
    }

    /**
     * Call create response
     *
     * @param  array  $currentMethodProperties
     * @param  string $messageId
     * @param  array  $singleRecord
     * @return void
     */
    public function callCreateResponse($currentMethodProperties, $messageId, $singleRecord)
    {
        if (isset($currentMethodProperties['isParent'])) {
            $this->parentData = $this->createResponse($messageId, $singleRecord);
        } elseif (isset($currentMethodProperties[self::ISCHILD])) {
            $this->childEntityName = $currentMethodProperties[self::ISCHILD];
            $this->inputData[] = $this->createResponse($messageId, $singleRecord);
        }
    }

    /**
     * Create response
     *
     * @param  int    $messageId
     * @param  array  $singleRecord
     * @param  string $inputData
     * @return object
     */
    public function createResponse($messageId, $singleRecord, $inputData = null)
    {
        $i95DevRevResponseData = null;
        if (isset($messageId)) {
            if (is_numeric($messageId)) {
                $recordStatus = true;
                $recordMessage = "";
            } else {
                if (is_array($messageId)) {
                    $recordStatus = false;
                    $recordMessage = $messageId[self::MSG];
                    $messageId = $messageId[self::MSGID];
                } else {
                    $recordStatus = false;
                    $recordMessage = $messageId;
                }
            }

            $i95DevRevResponseData = $this->i95DevRevResponse->create();
            $i95DevRevResponseData->setResult($recordStatus);
            $i95DevRevResponseData->setMessageid($messageId);
            $i95DevRevResponseData->setMessage($recordMessage);
            if (isset($singleRecord[self::TARGETID])) {
                $i95DevRevResponseData->setTargetid($singleRecord[self::TARGETID]);
            }
            if (isset($this->currentMethodProperties[self::ISCHILD]) && isset($singleRecord['targetCustomerId'])) {
                $i95DevRevResponseData->setTargetcustomerid($singleRecord['targetCustomerId']);
            }
            if (!empty($inputData)) {
                $i95DevRevResponseData->setInputdata($inputData);
            }
        }
        return $i95DevRevResponseData;
    }

    /**
     * Business layer to sync data from MQ to magento
     *
     * @return void
     * @throws Exception
     */
    public function syncMQtoMagento()
    {
        if ($this->dataHelper->isEnabled()) {
            try {
                $packetSize = $this->scopeConfigInterface->getValue(
                    'i95dev_messagequeue/i95dev_extns/packet_size',
                    ScopeInterface::SCOPE_WEBSITE,
                    $this->storeManager->getDefaultStoreView()->getWebsiteId()
                );
                if (!$packetSize) {
                    $packetSize = 50;
                }

                $mqDirectSyncCollection = $this->getMQDirectSyncEntityDataCollection($packetSize);
                if ($mqDirectSyncCollection->getSize() > 0) {
                    $this->genericMQToMagento->reverseSyncMQtoMagento($mqDirectSyncCollection);
                }

                $mqCollection = $this->getMQPendingDataCollection($packetSize);
                if ($mqCollection->getSize() > 0) {
                    $this->genericMQToMagento->reverseSyncMQtoMagento($mqCollection);
                }
            } catch (LocalizedException $ex) {
                $this->logger->createLog(
                    __METHOD__,
                    $ex->getMessage(),
                    LoggerInterface::I95EXC,
                    'critical'
                );
            }
        }
    }

    /**
     * Get the MQ data collection that needs to be synced to magento
     *
     * @param  int $packetSize
     * @return object
     */
    private function getMQDirectSyncEntityDataCollection($packetSize)
    {
        $entityToFilter = $this->getEntityToFilter();
        $i95DevErpMQColl = $this->i95DevErpMQ->getCollection();
        $i95DevErpMQColl->addFieldToSelect(self::MSG_ID, self::MSG_ID)
            ->addFieldToFilter(
                self::STATUS,
                [
                    ['eq' => Data::PENDING],
                    ['eq' => Data::ERROR]
                ]
            )
            ->addFieldToFilter(
                'counter',
                ['lt' => Data::RETRY_LIMIT_DIRECT]
            )
            ->addFieldToFilter(
                'entity_code',
                [
                    'in' => $entityToFilter
                ]
            )
            ->setOrder(self::MSG_ID, 'ASC');
        $i95DevErpMQColl->getSelect()
            ->limit($packetSize);
        return $i95DevErpMQColl;
    }

    /**
     * Get Entity To Filer From Collection
     *
     * @return array
     */
    public function getEntityToFilter()
    {
        $apiMethodsRoutes = $this->i95DevRepository->create()->apiServiceMethodRoutes;
        $apiEntitys = [];
        foreach ($apiMethodsRoutes as $data) {
            if (isset($data['saveWithSync'])) {
                $apiEntitys[] = $data['entityCode'];
            }
        }
        return $apiEntitys;
    }

    /**
     * Get the MQ data collection that needs to be synced to magento
     *
     * @param  int $packetSize
     * @return object
     */
    private function getMQPendingDataCollection($packetSize)
    {
        $retryLimit = $this->scopeConfigInterface->getValue(
            'i95dev_messagequeue/I95DevConnect_mqsettings/retry_limit',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
        if (!$retryLimit) {
            $retryLimit = Data::RETRY_LIMIT;
        }

        $entityToFilter = $this->getEntityToFilter();
        $i95DevErpMQColl = $this->i95DevErpMQ->getCollection();
        $i95DevErpMQColl->addFieldToSelect(self::MSG_ID, self::MSG_ID)
            ->addFieldToFilter(
                self::STATUS,
                [
                    ['eq' => Data::PENDING],
                    ['eq' => Data::ERROR]
                ]
            )
            ->addFieldToFilter(
                'counter',
                ['lt' => $retryLimit]
            )
            ->addFieldToFilter(
                'entity_code',
                [
                    'nin' => $entityToFilter
                ]
            )
            ->setOrder(self::MSG_ID, 'ASC'); //@updatedBy Debashis S. Gopal. Sort order added in collection
        $i95DevErpMQColl->getSelect()
            ->limit($packetSize);

        return $i95DevErpMQColl;
    }

    /**
     * Method to get MQ status
     *
     * @param  string $finalRequest
     * @return object
     */
    public function getMessageQueueStatus($finalRequest)
    {
        $collection = $this->initAcknowledgement($finalRequest);
        $result = [];
        if (!empty($collection)) {
            foreach ($collection as $singleRecordCollection) {
                $this->recordResponse = [];
                $this->recordResponse[self::RESULT] = false;
                $status = $singleRecordCollection->getStatus();
                if ($status == Data::SUCCESS
                    || $status == Data::COMPLETE
                ) {
                    $this->recordResponse[self::RESULT] = true;
                }
                $this->recordResponse[self::MSGID] = $singleRecordCollection->getId();
                $this->recordResponse[self::ERROR_ID] = $singleRecordCollection->getErrorId();

                $msg = $this->getErrorMsg($this->recordResponse);

                $this->recordResponse[self::MSG] = $msg;
                $this->recordResponse[self::TARGETID] = $singleRecordCollection->getTargetId();
                $this->recordResponse[self::SOURCEID] = $singleRecordCollection->getMagentoId();
                $this->recordResponse[self::ENTITY_NAME] = $singleRecordCollection->getEntityCode();

                if ($singleRecordCollection->getEntityCode() == 'Customer') {
                    $this->recordResponse = $this->getCustomerStatus($singleRecordCollection, $this->recordResponse);
                    if (!$this->recordResponse) {
                        continue;
                    }
                }

                $responseEvent = "erpconnect_send_reverse_response";
                $this->eventManager->dispatch($responseEvent, ['responseObject' => $this]);

                $result[] = $this->recordResponse;
            }
            $this->setResponse(true, "", $result);
        }

        return $this->i95DevResponse;
    }

    /**
     * Common code for reverse acknowledgement and response
     *
     * @param     string $finalRequest
     * @return    boolean/Object
     * @createdBy Arushi Bansal
     */
    public function initAcknowledgement($finalRequest)
    {
        $dataString = $this->convertInputStringToArray($finalRequest);
        $msgId = [];
        $requestData = isset($dataString['requestData']) ? $dataString['requestData'] : $dataString['RequestData'];
        if (!empty($requestData)) {
            foreach ($requestData as $value) {
                $msgId[] = $value[self::MSGID];
            }
        }

        $collection = $this->i95DevErpMQ->getCollection();
        $collection
            ->addFieldToSelect([self::STATUS, self::ERROR_ID, self::TARGET_ID, self::ENTITY_CODE, self::MGT_ID]);
        $collection->addFieldToFilter("msg_id", ["in" => implode(',', $msgId)]);
        if ($collection->getSize() > 0) {
            return $collection;
        } else {
            $this->setResponse(false);
            return [];
        }
    }

    /**
     * Get error message
     *
     * @param  array $recordResponse
     * @return string
     */
    public function getErrorMsg($recordResponse)
    {
        $msg = '';
        if (isset($recordResponse[self::ERROR_ID])) {
            $errorData = $this->errorUpdateData->create()->load($recordResponse[self::ERROR_ID]);
            $list = explode(",", $errorData->getMsg() ?? '');
            if (!empty($list)) {
                foreach ($list as $lt) {
                    if ($msg != "") {
                        $msg .= ", ";
                    }
                    $msg .= __(trim($lt));
                }
            }
        }

        return $msg;
    }

    /**
     * Get customer status
     *
     * @param  object $singleRecordCollection
     * @param  array  $recordResponse
     * @return bool
     */
    public function getCustomerStatus($singleRecordCollection, $recordResponse)
    {
        $status = [Data::PENDING];
        $pendingAddress = $this->getMqAddressDataByParentId(
            $status,
            $recordResponse[self::MSGID]
        )->getData();

        if (count($pendingAddress) > 0) {
            return false;
        }

        $status = [
            Data::SUCCESS,
            Data::COMPLETE,
            Data::ERROR
        ];
        $collectionArr = $this->getMqAddressDataByParentId(
            $status,
            $recordResponse[self::MSGID]
        )->getData();

        $addresses = $this->processErroredAddressStatus($collectionArr, $singleRecordCollection);

        /**
         * @author Debashis S. Gopal. Sending address response as encrypted string as expected by ERP
         */
        if (!empty($addresses)) {
            $inputData['addresses'] = $addresses;
            $recordResponse[self::INPUT_DATA] = $this->encryptAES(json_encode($inputData));
        }
        return $recordResponse;
    }

    /**
     * Get inbound messagequeue address by status
     *
     * @param     array $status
     * @param     int   $parent_id
     * @return    object
     * @createdBy Arushi Bansal
     */
    public function getMqAddressDataByParentId($status, $parent_id)
    {
        $cols = [self::MSG_ID, self::TARGET_ID, self::MGT_ID, self::ENTITY_CODE, self::ERROR_ID, self::STATUS];
        return $this->i95DevErpMQ->getCollection()
            ->addFieldToSelect($cols)
            ->addFieldToFilter(self::ENTITY_CODE, 'address')
            ->addFieldToFilter(
                self::STATUS,
                [
                    'in' => $status
                ]
            )
            ->addFieldToFilter('parent_msg_id', $parent_id);
    }

    /**
     * Process error address status
     *
     * @param  array  $collectionArr
     * @param  object $singleRecordCollection
     * @return mixed
     */
    public function processErroredAddressStatus($collectionArr, $singleRecordCollection)
    {
        $addresses = [];
        foreach ($collectionArr as $address) {
            $msgAddress = $this->getErrorMsg($address);
            $addresses[] = [
                self::MSGID => $address[self::MSG_ID],
                self::TARGETID => $address[self::TARGET_ID],
                'reference' => $singleRecordCollection->getTargetId(),
                self::SOURCEID => $address[self::MGT_ID],
                self::ENTITY_NAME => $address[self::ENTITY_CODE],
                self::MSG => $msgAddress
            ];
        }
        return $addresses;
    }

    /**
     * Method to set Acknowledgement
     *
     * @param  string $finalRequest
     * @return object
     */
    public function setMessageQueueAck($finalRequest)
    {
        $collection = $this->initAcknowledgement($finalRequest);
        $result = [];
        if (!empty($collection)) {
            foreach ($collection as $singleRecordCollection) {
                $recordResponse = [];
                $recordResponse[self::RESULT] = false;
                $status = $singleRecordCollection->getStatus();
                if ($status == Data::SUCCESS
                    || $status == Data::COMPLETE
                ) {
                    $singleRecordCollection->setStatus(Data::COMPLETE);
                    $singleRecordCollection->save();
                    $recordResponse[self::RESULT] = true;
                }

                $recordResponse[self::MSGID] = $singleRecordCollection->getId();
                $recordResponse[self::TARGETID] = $singleRecordCollection->getTargetId();
                $recordResponse[self::SOURCEID] = $singleRecordCollection->getMagentoId();
                $recordResponse[self::ENTITY_NAME] = $singleRecordCollection->getEntityCode();

                if ($singleRecordCollection->getEntityCode() == 'Customer') {
                    $recordResponse = $this->setMessageQueueAckForCustomer($singleRecordCollection, $recordResponse);
                }

                if (!$recordResponse) {
                    continue;
                }

                $result[] = $recordResponse;
            }
            $this->setResponse(true, "", $result);
        }

        return $this->i95DevResponse;
    }

    /**
     * Set message queue acknowledge for customer
     *
     * @param  object $singleRecordCollection
     * @param  array  $recordResponse
     * @return array
     */
    public function setMessageQueueAckForCustomer($singleRecordCollection, $recordResponse) //NOSONAR
    {
        $status = [
            Data::PENDING,
        ];
        $pendingAddress = $this->getMqAddressDataByParentId(
            $status,
            $recordResponse[self::MSGID]
        )->getData();

        if (count($pendingAddress) > 0) {
            return null;
        }

        $status = [
            Data::SUCCESS,
            Data::COMPLETE,
            Data::ERROR
        ];
        $collectionArr = $this->getMqAddressDataByParentId($status, $recordResponse[self::MSGID]);

        $addresses = $this->prepareAddressAcknowledgement($collectionArr);

        if (!empty($addresses)) {
            $recordResponse['responseData']['addresses'] = $addresses;
        }

        return $recordResponse;
    }

    /**
     * Prepare address acknowledgement
     *
     * @param  array $collectionArr
     * @return mixed
     */
    public function prepareAddressAcknowledgement($collectionArr)
    {
        $addresses = null;
        foreach ($collectionArr as $address) {
            $msgAddress = $this->getErrorMsg($address);
            $addressStatus = $address[self::STATUS];
            $addresses = $this->saveAddress($addressStatus, $address, $msgAddress);
        }
        return $addresses;
    }

    /**
     * Save address
     *
     * @param  string $addressStatus
     * @param  object $address
     * @param  string $msgAddress
     * @return array
     * @return array
     */
    public function saveAddress($addressStatus, $address, $msgAddress)
    {
        $addresses = [];
        if ($addressStatus == Data::SUCCESS
            || $addressStatus == Data::COMPLETE
        ) {
            $address->setStatus(Data::COMPLETE);
            $address->save();

            $addresses[] = [
                self::RESULT => true,
                self::MSGID => $address[self::MSG_ID],
                self::TARGETID => $address[self::TARGET_ID],
                self::SOURCEID => $address[self::MGT_ID],
                self::ENTITY_NAME => $address[self::ENTITY_CODE],
                self::MSG => $msgAddress
            ];
        }

        return $addresses;
    }
}
