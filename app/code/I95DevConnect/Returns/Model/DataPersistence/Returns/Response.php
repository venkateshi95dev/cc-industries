<?php

namespace I95DevConnect\Returns\Model\DataPersistence\Returns;

use I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\ServiceRequest;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use I95DevConnect\Returns\Model\RmaEntityFactory;

class Response
{
    public const LOG_INFO = 'i95devReturnResponse';

    /**
     *
     * @var ServiceRequest
     */
    public $requestHelper;

    /**
     *
     * @var Data
     */
    public $dataHelper;

    /**
     *
     * @var Manager
     */
    public $eventManager;

    /**
     *
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    public $i95DevMagentoMQRepository;

    /**
     *
     * @var I95DevMagMQInterfaceFactory
     */
    public $i95DevMagentoMQData;
    /**
     * @var string
     */
    public $statusCode = '5';

    /**
     *
     * @var String|null
     */
    public $updatedBy = 'ERP';

    /**
     *
     * @var String|null
     */
    public $erpCode;

    /**
     *
     * @var String|null
     */
    public $targetId;

    /**
     *
     * @var String|null
     */
    public $entityCode;
    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;
    /**
     * @var RmaEntityFactory
     */
    // @codingStandardsIgnoreLine
    protected $_i95devRma;

    /**
     * @var LoggerInterfaceFactory
     */
    protected $logger;

    /**
     * @var int
     */
    protected $returnId;

    /**
     * @var int
     */
    protected $messageId;

    /**
     * @var array
     */
    protected $requestData;

    /**
     * @var array
     */
    protected $returnData;

    /**
     * Response constructor.
     * @param Data $dataHelper
     * @param I95DevMagMQRepositoryInterfaceFactory $i95DevMagentoMQRepository
     * @param I95DevMagMQInterfaceFactory $i95DevMMQData
     * @param ServiceRequest $requestHelper
     * @param Manager $eventManager
     * @param LoggerInterfaceFactory $logger
     * @param AbstractDataPersistence $abstractDataPersistence
     * @param RmaEntityFactory $rmaEntity
     */
    public function __construct(
        Data $dataHelper,
        I95DevMagMQRepositoryInterfaceFactory $i95DevMagentoMQRepository,
        I95DevMagMQInterfaceFactory $i95DevMMQData,
        ServiceRequest $requestHelper,
        Manager $eventManager,
        LoggerInterfaceFactory $logger,
        AbstractDataPersistence $abstractDataPersistence,
        RmaEntityFactory $rmaEntity
    ) {
        $this->dataHelper = $dataHelper;
        $this->i95DevMagentoMQRepository = $i95DevMagentoMQRepository;
        $this->i95DevMagentoMQData = $i95DevMMQData;
        $this->requestHelper = $requestHelper;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->abstractDataPersistence = $abstractDataPersistence;
        $this->_i95devRma = $rmaEntity;
    }

    /**
     * Set response
     *
     * @param array $requestData
     * @param string $entityCode
     * @param string $erpCode
     * @return \I95DevConnect\MessageQueue\Api\I95DevResponseInterface|mixed
     */
    public function setResponse($requestData, $entityCode, $erpCode) // NOSONAR
    {
        try {
            if ($erpCode) {
                $this->updatedBy = $erpCode;
            }
            $this->returnId = $this->dataHelper->getValueFromArray("sourceId", $requestData);
            $this->messageId = $this->dataHelper->getValueFromArray("messageId", $requestData);
            $this->requestData = $requestData;
            $this->validateData();
            $this->targetId = $this->dataHelper->getValueFromArray("targetId", $requestData);
            $this->logger->create()->createLog(
                __METHOD__,
                $this->targetId,
                self::LOG_INFO,
                'info'
            );

            if ($this->targetId != '') {
                $this->saveDataInOutboundMQ();
                $this->dataHelper->unsetGlobalValue('i95_observer_skip');
                $this->dataHelper->setGlobalValue('i95_observer_skip', true);

                $returnsRecord = $this->_i95devRma->create()->load($this->returnId, 'return_id');
                $returnsRecord->setTargetReturnId($this->targetId);
                $returnsRecord->setUpdateBy($this->erpCode);
                $origin = $returnsRecord->getData('origin');
                if (empty($origin)) {
                    $returnsRecord->setData('origin', 'website');
                }
                $returnsRecord->save();

                $this->logger->create()->createLog(
                    __METHOD__,
                    "Sync Success",
                    self::LOG_INFO,
                    'info'
                );

                $this->dataHelper->unsetGlobalValue('i95_observer_skip');

                $response['inputData'] = [];
                $response['id'] = 0;
                return $this->abstractDataPersistence->setResponse(
                    Data::SUCCESS,
                    __("Response send successfully"),
                    null
                );
            } else {
                return $this->abstractDataPersistence->setResponse(
                    Data::ERROR,
                    __("Some error occured in response sync"),
                    null
                );
            }
        } catch (LocalizedException $e) {
            $this->logger->create()->createLog(
                __METHOD__,
                $e->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
            $message = $e->getMessage();
            throw new LocalizedException(__($message));
        }
    }

    /**
     * Validating data
     */
    public function validateData()
    {
        try {
            $this->returnData = $this->_i95devRma->create()->load($this->returnId, 'return_id');
            if (!$this->returnData->getReturnId()) {
                $message = "Return Order with id ::" . $this->returnId . " does not exists";
                throw new LocalizedException(__($message));
            }
        } catch (LocalizedException $ex) {
            $message = $ex->getMessage();
            throw new LocalizedException(__($message));
        }
    }

    /**
     * Save data in outbound messagequeue
     */
    public function saveDataInOutboundMQ()
    {
        try {
            $i95DevMMQData = $this->i95DevMagentoMQData->create();
            $i95DevMMQData->setMsgId($this->messageId);
            $i95DevMMQData->setStatus($this->statusCode);
            $i95DevMMQData->setUpdatedby($this->updatedBy);
            $i95DevMMQData->setTargetId($this->targetId);
            $this->i95DevMagentoMQRepository->create()->saveMQData($i95DevMMQData);
        } catch (LocalizedException $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }
    }
}
