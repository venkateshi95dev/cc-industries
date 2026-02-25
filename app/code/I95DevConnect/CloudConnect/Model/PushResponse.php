<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model;

use I95DevConnect\CloudConnect\Api\Data\RequestInterfaceFactory;
use I95DevConnect\CloudConnect\Api\PushResponseInterface;
use I95DevConnect\CloudConnect\Helper\ConfigHelper;
use I95DevConnect\CloudConnect\Model\ServiceMethod\ServiceMethodFactory;
use I95DevConnect\I95DevServer\Model\ServiceMethod\ForwardSync\MQToErp\SendIds;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\ReadCustomXml;
use Magento\Framework\Exception\LocalizedException;

/**
 * class to push the response from magento to cloud
 */
class PushResponse extends AbstractAgentCron implements PushResponseInterface
{
    /**
     * @var string
     */
    public $schedulerType = 'PushResponse';

    /**
     * @var ServiceMethod\ServiceMethodFactory
     */
    public $serviceMethod;

    /**
     * @var SendIds
     */
    public $sendIds;

    /**
     * @var string
     */
    public $logFilename = 'PushResponse';

    /**
     * @var RequestFactory
     */
    public $request;

    /**
     * @var Service
     */
    public $service;

    /**
     * @var \I95DevConnect\CloudConnect\Helper\Data
     */
    public $cloudHelper;

    /**
     * @var ReadCustomXml
     */
    public $readCustomXml;

    /**
     * @var ConfigHelper
     */
    public $configHelper;

    /**
     * @var RequestInterfaceFactory
     */
    public $requestInterface;

    /**
     * @var Data
     */
    public $mqHelper;

    /**
     * @var I95DevErpMQRepositoryInterfaceFactory
     */
    public $messageQueueModel;

    /**
     * Constructor for DI
     * @param LoggerFactory $logger
     * @param RequestFactory $request
     * @param Service $service
     * @param ServiceMethodFactory $serviceMethod
     * @param \I95DevConnect\CloudConnect\Helper\Data $cloudHelper
     * @param ReadCustomXml $readCustomXml
     * @param ConfigHelper $configHelper
     * @param SendIds $sendIds
     * @param RequestInterfaceFactory $requestInterface
     * @param Data $mqHelper
     * @param I95DevErpMQRepositoryInterfaceFactory $messageQueueModel
     */
    public function __construct( // NOSONAR
        LoggerFactory $logger,
        RequestFactory $request,
        Service $service,
        ServiceMethodFactory $serviceMethod,
        \I95DevConnect\CloudConnect\Helper\Data $cloudHelper,
        ReadCustomXml $readCustomXml,
        ConfigHelper $configHelper,
        SendIds $sendIds,
        RequestInterfaceFactory $requestInterface,
        Data $mqHelper,
        I95DevErpMQRepositoryInterfaceFactory $messageQueueModel
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->service = $service;
        $this->cloudHelper = $cloudHelper;
        $this->readCustomXml = $readCustomXml;
        $this->serviceMethod = $serviceMethod;
        $this->configHelper = $configHelper;
        $this->sendIds = $sendIds;
        $this->requestInterface = $requestInterface;
        $this->mqHelper = $mqHelper;
        $this->messageQueueModel = $messageQueueModel;
    }

    /**
     * @inheritDoc
     */
    public function syncResponse()
    {
        $this->startCronProcess();
    }

    /**
     * Method to initiate job
     *
     * @param string $schedulerId
     * @param array $schedulerData
     */
    protected function initiateJob($schedulerId, $schedulerData)
    {
        try {
            $this->processDataToCloud($schedulerId);
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                'PushDataCron',
                $ex->getMessage(),
                Logger::EXCEPTION,
                'critical'
            );
        }
    }

    /**
     * Process data to cloud
     *
     * @param string $schedulerId
     * @throws LocalizedException
     */
    public function processDataToCloud($schedulerId)
    {
        $entities = $this->readCustomXml->getXmlDataOrderBySyncOrder();
        $reverseSkipEntities = $this->configHelper->getReverseSkipEntities();

        foreach ($entities as $entity_code => $entity_value) {
            if (!in_array($entity_code, $reverseSkipEntities)) {
                $mqCollection = $this->messageQueueModel->create()->getCollection();
                // $mqCollection->getSelect()->where(
                //     '(status = ' . Data::SUCCESS . ') '
                //     . ' OR (status = ' . Data::ERROR
                //     . ' AND counter < ' . Data::RETRY_LIMIT_DIRECT . ')'
                // );
                $mqCollection->addFieldToFilter('status', array('in' => array(Data::SUCCESS,Data::ERROR)));
                $mqCollection->addFieldToFilter('response_counter',0);
                $mqCollection->addFieldToFilter('entity_code', $entity_code);
                $mqCollection->getSelect()->order('msg_id', 'ASC');

                if (!empty($mqCollection) && $mqCollection->getSize() > 0) {
                    $packetSize = $this->cloudHelper->getPacketSize();
                    $packets = array_chunk($mqCollection->getData(), $packetSize);
                    foreach ($packets as $packetdata) {
                        $requestObject = $this->requestInterface->create();
                        $requestObject->setContext(
                            $this->request->create()->prepareContextObject(
                                $this->schedulerType,
                                $schedulerId
                            )
                        );
                        $requestObject->setRequestData($packetdata);

                        $this->serviceMethod->create()->cloudConnect(
                            $requestObject,
                            $entity_code,
                            $this->schedulerType,
                            'reverseResponse'
                        );
                    }
                }
            }
        }
    }
}
