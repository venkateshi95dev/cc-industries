<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Observer\OutboundObserver;

use I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Config;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\Generic;
use I95DevConnect\MessageQueue\Model\EntityFactory;
use I95DevConnect\MessageQueue\Model\Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\App\State;

/**
 * Base Observer class
 */
abstract class BaseObserver
{
    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var object
     */
    public $baseHelperData;

    /**
     * @var object
     */
    public $magentoMessageQueue;
    /**
     * @var null
     */
    public $observerRouting;
    /**
     * @var string
     */
    public $erpCode = "ERP";
    /**
     * @var objectject
     */
    public $dataObject;
    /**
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    public $I95DevMagMQRepo;
    /**
     * @var Logger
     */
    public $logger;
    /**
     * @var Http
     */
    public $request;
    /**
     * @var Config
     */
    public $helperConfig;
    /**
     * @var I95DevMagMQInterfaceFactory
     */
    public $I95DevMagMQFactory;
    /**
     * @var Generic
     */
    public $generic;
    /**
     * @var TransactionSearchResultInterfaceFactory
     */
    public $transactions;
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var EntityFactory
     */
    public $entityTypeModel;
    /**
     * @var Manager
     */
    public $eventManager;
    /**
     * @var string
     */
    public $additionalInfo = "";
    /**
     * @var int
     */
    private $magentoId;
    /**
     * @var string
     */
    private $entityCode;
    /**
     * @var string
     */
    private $updatedBy = 'Magento';
    /**
     * @var string
     */
    private $statusCode = '1';
    /**
     *
     * @var array
     */
    private $sourceData;

    /**
     * @var OrderCollectionFactory
     */
    public $salesOrderFactory;

    /**
     * @var State
     */
    public $state;

    /**
     * BaseObserver constructor.
     *
     * @param Data $dataHelper
     * @param I95DevMagMQInterfaceFactory $I95DevMagMQFactory
     * @param I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepo
     * @param Logger $logger
     * @param Generic $generic
     * @param TransactionSearchResultInterfaceFactory $transactions
     * @param Http $request
     * @param Config $helperConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param EntityFactory $entityTypeModel
     * @param Manager $eventManager
     * @param OrderCollectionFactory $salesOrderFactory
     * @param object $observerRouting
     */
    public function __construct( // NOSONAR
        Data $dataHelper,
        I95DevMagMQInterfaceFactory $I95DevMagMQFactory,
        I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepo,
        Logger $logger,
        Generic $generic,
        TransactionSearchResultInterfaceFactory $transactions,
        Http $request,
        Config $helperConfig,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        EntityFactory $entityTypeModel,
        Manager $eventManager,
        OrderCollectionFactory $salesOrderFactory,
        State $state,
        $observerRouting = null
    ) {
        $this->dataHelper = $dataHelper;
        $this->I95DevMagMQFactory = $I95DevMagMQFactory;
        $this->I95DevMagMQRepo = $I95DevMagMQRepo;
        $this->observerRouting = $observerRouting;
        $this->logger = $logger;
        $this->request = $request;
        $this->generic = $generic;
        $this->transactions = $transactions;
        $this->helperConfig = $helperConfig;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->entityTypeModel = $entityTypeModel;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->eventManager = $eventManager;
        $this->state = $state;
    }

    /**
     * To get the dataObject
     *
     * @return object
     */
    public function getDataObject()
    {
        return $this->dataObject;
    }

    /**
     * Set dataObject
     *
     * @param obj $dataObject
     */
    public function setDataObject($dataObject)
    {
        $this->dataObject = $dataObject;
    }

    /**
     * Get the source data
     */
    public function getSourceData()
    {
        return $this->sourceData;
    }

    /**
     * Set the source data
     *
     * @param string $sourceData
     */
    public function setSourceData($sourceData)
    {
        $this->sourceData = $sourceData;
    }

    /**
     * To save record
     *
     * @throws LocalizedException
     */
    public function saveRecord()
    {
        try {
            $mageId = $this->getMagentoId();
            $entity_code = $this->getEntityCode();
            if ($this->validateRecord() === false) {
                return;
            }
            $I95DevMagMQ = $this->I95DevMagMQFactory->create();
            $this->erpCode = $this->helperConfig->getConfigValues()->getData('component');
            $I95DevMagMQ->setErpCode(__($this->erpCode));
            $I95DevMagMQ->setEntitycode($entity_code);
            $I95DevMagMQ->setMagentoId($mageId);
            $I95DevMagMQ->setStatus($this->statusCode);
            $I95DevMagMQ->setUpdatedBy($this->updatedBy);
            $additional = $this->getAdditionalInfo();
            if ($additional) {
                $I95DevMagMQ->setAdditionalInfo($additional);
            }
            $this->I95DevMagMQRepo->create()->saveMQData($I95DevMagMQ);
        } catch (LocalizedException $ex) {
            $message = $ex->getMessage();
            throw new LocalizedException(__($message));
        }
    }

    /**
     * Get Magento id
     *
     * @return string
     */
    public function getMagentoId()
    {
        return $this->magentoId;
    }

    /**
     * To set the Magento Id
     *
     * @param string $magentoId
     */
    public function setMagentoId($magentoId)
    {
        $this->magentoId = $magentoId;
    }

    /**
     * Get entity code
     *
     * @return string
     */
    public function getEntityCode()
    {
        return $this->entityCode;
    }

    /**
     * Set entity code
     *
     * @param string $entityCode
     */
    public function setEntitycode($entityCode)
    {
        $this->entityCode = $entityCode;
    }

    /**
     * Validate record
     *
     * @return boolean
     */
    public function validateRecord()
    {
        if ($this->entityCode == "product") {
            $productType = $this->dataObject->getTypeId();
            if ($productType === "configurable") {
                return false;
            }
        }
        return true;
    }

    /**
     * Get additional info
     *
     * @return null|string
     */
    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }

    /**
     * Set additional info
     *
     * @param null|string $additionalInfo
     * @return void
     */
    public function setAdditionalInfo($additionalInfo)
    {
        $this->additionalInfo = $additionalInfo;
    }

    /**
     * Get the values from array
     *
     * @param array $array
     * @param string $key
     * @return string
     */
    public function getValueFromArray($array, $key)
    {
        $value = null;
        if (is_array($array) && isset($array[$key])) {
            $value = $array[$key];
        }
        return $value;
    }

    /**
     * Check method exist or not
     *
     * @param obj $classObject
     * @param string $methodName
     * @return boolean
     */
    public function methodExists($classObject, $methodName)
    {
        if (is_object($classObject) && method_exists($classObject, $methodName)) {
            return true;
        }
        return false;
    }
}
