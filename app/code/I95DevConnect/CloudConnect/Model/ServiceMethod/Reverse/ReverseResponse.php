<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model\ServiceMethod\Reverse;

use Exception;
use I95DevConnect\CloudConnect\Api\Data\RequestInterfaceFactory;
use I95DevConnect\CloudConnect\Model\Logger;
use I95DevConnect\CloudConnect\Model\Service;
use I95DevConnect\CloudConnect\Model\type;
use I95DevConnect\I95DevServer\Api\I95DevServerRepositoryInterfaceFactory;
use I95DevConnect\I95DevServer\Model\ServiceMethod\AbstractServiceMethod;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\ErrorUpdateData;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use I95DevConnect\CloudConnect\Model\RequestFactory;

/**
 * Model class to send response to cloud from Inbound MQ
 */
class ReverseResponse
{
    /**
     * @var \I95DevConnect\CloudConnect\Helper\Data
     */
    public $cloudHelper;

    /**
     * @var RequestInterfaceFactory
     */
    public $requestInterface;

    /**
     * @var I95DevErpMQRepositoryInterfaceFactory
     */
    public $messageQueueModel;

    /**
     * @var string
     */
    public $erpName = 'ERP';

    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var AbstractServiceMethod
     */
    public $abstractServicemethod;

    /**
     * @var string
     */
    public $msgIdConst = "msg_id";

    /**
     * @var string
     */
    public $errorIdConst = "error_id";

    /**
     * @var string
     */
    public $targetIdConst = "target_id";

    /**
     * @var string
     */
    public $magentoIdConst = "magento_id";

    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var I95DevServerRepositoryInterfaceFactory
     */
    private $i95DevRepository;

    /**
     * @var Service
     */
    public $service;

    /**
     * @var ErrorUpdateData
     */
    public $errorUpdateData;

    /**
     * @var RequestFactory
     */
    public $request;

    /**
     * @var mixed
     */
    public $data;

    /**
     * @param \I95DevConnect\CloudConnect\Helper\Data $cloudHelper
     * @param RequestInterfaceFactory $requestInterface
     * @param Logger $logger
     * @param I95DevErpMQRepositoryInterfaceFactory $messageQueueModel
     * @param Service $service
     * @param I95DevServerRepositoryInterfaceFactory $i95DevRepository
     * @param ErrorUpdateData $errorUpdateData
     * @param AbstractServiceMethod $abstractServicemethod
     * @param Manager $eventManager
     * @param RequestFactory $request
     */
    public function __construct( // NOSONAR
        \I95DevConnect\CloudConnect\Helper\Data $cloudHelper,
        RequestInterfaceFactory $requestInterface,
        Logger $logger,
        I95DevErpMQRepositoryInterfaceFactory $messageQueueModel,
        Service $service,
        I95DevServerRepositoryInterfaceFactory $i95DevRepository,
        ErrorUpdateData $errorUpdateData,
        AbstractServiceMethod $abstractServicemethod,
        Manager $eventManager,
        RequestFactory $request
    ) {
        $this->cloudHelper = $cloudHelper;
        $this->requestInterface = $requestInterface;
        $this->messageQueueModel = $messageQueueModel;
        $this->logger = $logger;
        $this->service = $service;
        $this->i95DevRepository = $i95DevRepository;
        $this->errorUpdateData = $errorUpdateData;
        $this->abstractServicemethod = $abstractServicemethod;
        $this->eventManager = $eventManager;
        $this->request = $request;
    }

    /**
     * Method to send response to cloud from Inbound MQ
     *
     * @param object $request
     * @param string $entity
     * @param string $requestType
     * @throws LocalizedException
     */
    public function sync($request, $entity, $requestType)
    {
        try {
            if ($this->cloudHelper->isEnabled()) {
                $mqCollectionData = $request->getRequestData();

                if (!empty($mqCollectionData)) {
                    $cloudResult = $this->sendReverseResponse($mqCollectionData, $requestType, $entity, $request);

                    if (isset($cloudResult->ResultData) && $cloudResult->ResultData) {
                        $requestObj = $this->processResultData($cloudResult, $request->getContext());

                        if ($requestObj->getRequestData()) {
                            $this->i95DevRepository->create()->serviceMethod(
                                'setMessageQueueResponseAckList',
                                json_encode($requestObj, JSON_UNESCAPED_UNICODE),
                                $this->cloudHelper->getErpComponent()
                            );
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->createLog(
                __METHOD__,
                $e->getMessage(),
                Logger::EXCEPTION,
                'critical'
            );
        }
    }

    /**
     * Send reverse response
     *
     * @param object $mqCollectionData
     * @param string $requestType
     * @param string $entity
     * @param object $request
     * @return type|Object|null
     * @return type|Object|null
     */
    public function sendReverseResponse($mqCollectionData, $requestType, $entity, $request)
    {
        $responseData = [];
        $totalrecord = count($mqCollectionData);
        for ($i = 0; $i <= $totalrecord; $i++) {
            if (isset($mqCollectionData[$i])) {
                $record = $mqCollectionData[$i];
                $destinationMsgId = (int)$record['destination_msg_id'];
                $dataResult = $this->prepareResponseData($record, $destinationMsgId, $entity);
                if (!$dataResult) {
                    continue;
                }

                $responseData[] = $dataResult;
            }
        }

        $devResponse = $this->requestInterface->create();
        $packetSize = $this->cloudHelper->getPacketSize();
        $devResponse->setContext($request->Context);
        $devResponse->setPacketSize($packetSize);
        $devResponse->setRequestData($responseData);

        return $this->service->makeServiceCall(
            $requestType,
            $entity,
            $devResponse,
            $request->Context->SchedulerId
        );
    }

    /**
     * Prepare response data
     *
     * @param array $record
     * @param string $destinationMsgId
     * @param string $entity
     * @return bool
     */
    public function prepareResponseData($record, $destinationMsgId, $entity)
    {
        $this->data = [];
        $this->data = $this->cloudHelper->prepareDataObject();
        if ($record['status'] == Data::SUCCESS) {
            $this->data->setSourceId($record[$this->magentoIdConst]);
            $this->data->setReference($record['ref_name']);
            $this->data->setTargetId($record[$this->targetIdConst]);
            $this->data->setMessageId($destinationMsgId);
        } else {
            $this->data->setSourceId('');
            $this->data->setReference($record['ref_name']);
            $this->data->setTargetId($record[$this->targetIdConst]);
            $this->data->setMessageId($destinationMsgId);
            $errorData = $this->errorUpdateData->load($record[$this->errorIdConst])->getData();
            if (isset($errorData['msg'])) {
                $this->data->setMessage(__($errorData['msg']));
            }
        }

        if ($entity == 'Customer') {
            $pendingAddress = $this->getMqAddressDataByParentId(
                Data::PENDING,
                $record[$this->msgIdConst]
            );
            if (count($pendingAddress) > 0) {
                return false;
            }
            $addresses = $this->getAddressResponse($record);
            if (!empty($addresses)) {
                $inputData['addresses'] = $addresses;
                $this->data->setInputData(
                    $this->abstractServicemethod->encryptAES(json_encode($inputData, JSON_UNESCAPED_UNICODE))
                );
            }
            $this->eventManager->dispatch(
                "after_prepare_mq_response",
                [
                    'currentObject' => $this,
                    'entity' => 'Customer',
                    'mqData' => $record
                ]
            );
        }
        return $this->data;
    }

    /**
     * Get inbound messagequeue address by status
     *
     * @param array $status
     * @param int $parent_id
     * @return object
     * @createdBy Arushi Bansal
     */
    public function getMqAddressDataByParentId($status, $parent_id)
    {
        return $this->messageQueueModel->create()->getCollection()
            ->addFieldToSelect([$this->msgIdConst, $this->targetIdConst, $this->magentoIdConst, $this->errorIdConst])
            ->addFieldToFilter('entity_code', 'address')
            ->addFieldToFilter(
                'status',
                [
                    'in' => $status
                ]
            )
            ->addFieldToFilter('parent_msg_id', $parent_id)
            ->getData();
    }

    /**
     * Retrieve address responses which are not in pending state.
     *
     * @param array $record
     * @return array
     * @author Debashis S. Gopal
     */
    public function getAddressResponse($record)
    {
        $status = [Data::SUCCESS, Data::COMPLETE, Data::ERROR];
        $collectionArr = $this->getMqAddressDataByParentId($status, $record[$this->msgIdConst]);
        $addresses = [];

        foreach ($collectionArr as $address) {
            $msgAddress = '';
            if (isset($address[$this->errorIdConst])) {
                $errorData = $this->errorUpdateData->load($address[$this->errorIdConst]);

                $list = explode(",", (string)$errorData->getMsg());
                if (!empty($list)) {
                    foreach ($list as $lt) {
                        if ($msgAddress != "") {
                            $msgAddress .= ", ";
                        }
                        $msgAddress .= __(trim($lt));
                    }
                }
            }
            $addresses[] = [
                'messageId' => $address[$this->msgIdConst],
                'targetId' => $address[$this->targetIdConst],
                'reference' => $record[$this->targetIdConst],
                'sourceId' => $address[$this->magentoIdConst],
                'entityName' => "address",
                'message' => $msgAddress
            ];
        }
        return $addresses;
    }

    /**
     * Process result data
     *
     * @param object $cloudResult
     * @param object $context
     * @return mixed
     */
    public function processResultData($cloudResult, $context)
    {
        try {
            $requestObj = $this->requestInterface->create();
            $packetSize = $this->cloudHelper->getPacketSize();
            $requestObj->setContext($context);
            $requestObj->setPacketSize($packetSize);

            $requestDataList = [];
            foreach ($cloudResult->ResultData as $key => $requestData) {
                $item = null;
                $requestDataList[$key] = $cloudResult->ResultData[$key];
                $mqCollection = $this->messageQueueModel->create()->getCollection();
                $item = $mqCollection
                    ->addFieldToFilter('destination_msg_id', $requestDataList[$key]->messageId)
                    ->addFieldToFilter('status', ['in' => [Data::SUCCESS, Data::ERROR]])
                    ->getFirstItem();
                if ($item) {
                    $item->setResponseCounter(1);
                    $item->save();
                    $requestDataList[$key]->messageId = $item->getMsgId();
                    unset($cloudResult->ResultData[$key]);
                }
            }
            $requestObj->setRequestData($requestDataList);
        } catch (Exception $e) {
            $this->logger->createLog(
                __METHOD__,
                $e->getMessage(),
                Logger::EXCEPTION,
                'critical'
            );
        }

        return $requestObj;
    }
}
