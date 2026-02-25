<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model;

use I95DevConnect\CloudConnect\Api\Data\RequestInterfaceFactory;
use I95DevConnect\CloudConnect\Api\PullResponseInterface;
use I95DevConnect\CloudConnect\Helper\ConfigHelper;
use I95DevConnect\CloudConnect\Helper\Data as CloudHelper;
use I95DevConnect\CloudConnect\Model\ServiceMethod\Forward\ResponseAcknowledgement;
use I95DevConnect\CloudConnect\Model\ServiceMethod\ServiceMethodFactory;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\ReadCustomXmlFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * class to pull the data from cloud to magento
 */
class PullResponse extends AbstractAgentCron implements PullResponseInterface
{
    /**
     * @var string
     */
    public $erpName = "ERP";
    /**
     * @var ServiceMethod\ServiceMethodFactory
     */
    public $serviceMethod;
    /**
     * @var ConfigHelper
     */
    public $configHelper;
    /**
     * @var string
     */
    public $logFilename = 'PullResponse';
    /**
     * @var ReadCustomXmlFactory
     */
    public $readCustomXml;
    /**
     * @var RequestInterfaceFactory
     */
    public $requestInterface;
    /**
     * @var RequestFactory
     */
    public $request;
    /**
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    public $I95DevMagMQ;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;
    /**
     * @var ServiceMethod\Forward\ResponseAcknowledgement
     */
    public $responseAckSender;
    /**
     * @var Data
     */
    public $mqHelper;
    /**
     * @var string
     */
    protected $schedulerType = 'PullResponse';

    /**
     * Constructor for DI
     * @param LoggerFactory $logger
     * @param Service $service
     * @param ServiceMethodFactory $serviceMethod
     * @param ConfigHelper $configHelper
     * @param CloudHelper $cloudHelper
     * @param ReadCustomXmlFactory $readCustomXml
     * @param RequestInterfaceFactory $requestInterface
     * @param RequestFactory $request
     * @param I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQ
     * @param Data $mqHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param ResponseAcknowledgement $responseAckSender
     */
    public function __construct( // NOSONAR
        LoggerFactory $logger,
        Service $service,
        ServiceMethodFactory $serviceMethod,
        ConfigHelper $configHelper,
        CloudHelper $cloudHelper,
        ReadCustomXmlFactory $readCustomXml,
        RequestInterfaceFactory $requestInterface,
        RequestFactory $request,
        I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQ,
        Data $mqHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        ResponseAcknowledgement $responseAckSender
    ) {
        $this->readCustomXml = $readCustomXml;
        $this->serviceMethod = $serviceMethod;
        $this->configHelper = $configHelper;
        $this->requestInterface = $requestInterface;
        $this->request = $request;
        $this->I95DevMagMQ = $I95DevMagMQ;
        $this->jsonHelper = $jsonHelper;
        $this->responseAckSender = $responseAckSender;
        $this->mqHelper = $mqHelper;
        parent::__construct($cloudHelper, $service, $logger);
    }

    /**
     * @inheritDoc
     */
    public function syncResponse()
    {
        return $this->startCronProcess();
    }

    /**
     * Initiate pull data job for reverse sync
     *
     * @param string $schedulerId
     * @param array $schedulerData
     */
    protected function initiateJob($schedulerId, $schedulerData)
    {
        $this->processActiveSubscription($schedulerId);
    }

    /**
     * Process active subscription
     *
     * @param string $schedulerId
     */
    public function processActiveSubscription($schedulerId)
    {
        try {
            $entities = $this->readCustomXml->create()->getXmlDataOrderBySyncOrder();
            $forwardSkipEntities = $this->configHelper->getForwardSkipEntities();
            foreach ($entities as $entity_code => $entity_value) {
                // Loop all the entities for pulling the data
                if (!in_array($entity_code, $forwardSkipEntities)) {
                    $this->manipulateEntityResponse($entity_code, $schedulerId);
                }
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }

    /**
     * Method to pull response to magento from cloud
     *
     * @param string $entity
     * @param string $schedulerId
     */
    public function manipulateEntityResponse($entity, $schedulerId)
    {
        $result = null;
        try {
            $packetSize = $this->cloudHelper->getPacketSize();
            $devResponse = $this->requestInterface->create();
            $devResponse->setContext(
                $this->request->create()->prepareContextObject('pullResponse', $schedulerId)
            );
            $devResponse->setPacketSize($packetSize);
            $result = $this->service
                ->makeServiceCall($this->schedulerType, $entity, $devResponse, $schedulerId);

            //save target details in OutboundMq and Entities.
            $destinationId = null;
            if (is_array($result->ResultData) && !empty($result->ResultData)) {
                $resultData = json_decode($this->jsonHelper->jsonEncode($result->ResultData), 1);
                $destinationId = $this->serviceMethod->create()
                    ->cloudConnect(
                        $resultData,
                        $entity,
                        $this->schedulerType,
                        'erpResponse',
                        'setResponse'
                    );
            } elseif (!empty($result->message)) {
                $this->logger->createLog(
                    "getting response from magento for magento cloud agent to transfer to cloud",
                    $result->message,
                    $this->logFilename,
                    Logger::INFO
                );
            }
            $this->responseAckSender->syncResponseAck($entity, $destinationId, $schedulerId);
        } catch (LocalizedException $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }
}
