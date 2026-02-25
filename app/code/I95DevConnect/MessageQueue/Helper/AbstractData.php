<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Helper;

use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Model\EntityFactory;
use I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory;
use I95DevConnect\MessageQueue\Model\I95DevErpDataRepositoryFactory;
use I95DevConnect\MessageQueue\Model\I95DevErpMQRepositoryFactory;
use I95DevConnect\MessageQueue\Model\I95DevMagMQRepositoryFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Xml\Parser;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\StoreWebsiteRelationInterface;

if (!defined('DS')) {
    DEFINE('DS', DIRECTORY_SEPARATOR);
}

/**
 * Helper Class for Message Queue module
 */
class AbstractData extends AbstractHelper
{
    public const COMPLETE = 5;
    public const SUCCESS = 4;
    public const ERROR = 3;
    public const PROCESSING = 2;
    public const PENDING = 1;
    public const CLOSED = 10;
    public const RETRY_LIMIT = 5;
    public const RETRY_LIMIT_DIRECT = 3;
    public const MAGLOGNAME = 'MagentoToERP';
    public const ERPLOGNAME = 'ERPToMagento';
    public const I95EXC = 'i95devApiException';
    public const CLEAN = 'cleanMQData';
    public const CUSTOM_IDENTIFIER = 'i95dev_extension';
    public const TARGET_KEY = 'targetId';
    public const REF_KEY = 'reference';
    public const SYNCED = 'synced';
    public const MODULE_NAME = 'I95DevConnect_MessageQueue';
    public const MQ_XML = 'messagequeue.xml';
    public const COMPONENT = 'i95dev_messagequeue/I95DevConnect_settings/component';
    public const MQDATA_CLEAN_DAYS = 30;
    public const TARGET_CUSTOMER_ID = 'target_customer_id';
    public const MSG_ID = 'msg_id';
    public const ERROR_ID = 'error_id';
    public const ENTITY_CODE = 'entity_code';
    public const ENTITY_NAME = 'entity_name';
    public const MATRIXPRODUCT = 'matrixproduct';
    public const ORIGIN = 'origin';

    /**
     * @var I95DevErpDataRepositoryFactory
     */
    public $modelEntityUpdateDataFactory;

    /**
     * @var Parser $parser
     */
    public $parser;

    /**
     * @var Reader $reader
     */
    public $reader;

    /**
     * @var string
     */
    public $entityCode;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     *
     * @var Helper $helper
     */
    public $helper;

    /**
     * @var MsgResponseEntityInterface $msgResponse
     */
    public $msgResponse;

    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var  $msgReport
     */
    public $msgReport;

    /**
     * @var $errorModel
     */
    public $errorModel;

    /**
     * @var type|null
     */
    public $entityList;

    /**
     * @var EntityFactory
     */
    public $entityTypeModel;

    /**
     * @var I95DevErpMQRepositoryFactory
     */
    public $erpMessageQueue;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var Region|RegionFactory
     */
    public $regionModel;

    /**
     * @var Manager
     */
    public $moduleManager;

    /**
     * @var I95DevMagMQRepositoryFactory
     */
    public $magentoMessageQueue;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var string[]
     */
    public $isI95DevRestReq = ['isI95DevRestReq' => 'true'];

    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var UrlInterface
     */
    public $urlInterface;

    /**
     * @var RedirectInterface
     */
    public $_redirect; // phpcs:ignore

    /**
     * @var SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;

    /**
     * @var GroupRepositoryInterface
     */
    public $groupRepository;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var StoreWebsiteRelationInterface
     */
    public $storeWebsiteRelation;
    
    /**
     * @var CustomerFactory
     */
    public $customerFactory;

    /**
     * @param I95DevErpDataRepositoryFactory $modelEntityUpdateDataFactory
     * @param Parser $parser
     * @param Reader $reader
     * @param PageFactory $resultPageFactory
     * @param UrlInterface $urlInterface
     * @param DateTime $date
     * @param ErrorUpdateDataFactory $errorModel
     * @param EntityFactory $entityTypeModel
     * @param I95DevErpMQRepositoryFactory $erpMessageQueue
     * @param I95DevMagMQRepositoryFactory $magentoMessageQueue
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry $coreRegistry
     * @param RegionFactory $regionModel
     * @param Manager $moduleManager
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customerFactory
     * @param RedirectInterface $redirect
     * @param RedirectFactory $resultRedirectFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param Context $context
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     * @param object $entityList
     */
    public function __construct( // NOSONAR
        I95DevErpDataRepositoryFactory $modelEntityUpdateDataFactory,
        Parser $parser,
        Reader $reader,
        PageFactory $resultPageFactory,
        UrlInterface $urlInterface,
        DateTime $date,
        ErrorUpdateDataFactory $errorModel,
        EntityFactory $entityTypeModel,
        I95DevErpMQRepositoryFactory $erpMessageQueue,
        I95DevMagMQRepositoryFactory $magentoMessageQueue,
        ScopeConfigInterface $scopeConfig,
        Registry $coreRegistry,
        RegionFactory $regionModel,
        Manager $moduleManager,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        RedirectInterface $redirect,
        RedirectFactory $resultRedirectFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GroupRepositoryInterface $groupRepository,
        Context $context,
        StoreWebsiteRelationInterface $storeWebsiteRelation,
        $entityList = null
    ) {
        $this->modelEntityUpdateDataFactory = $modelEntityUpdateDataFactory;
        $this->parser = $parser;
        $this->reader = $reader;
        $this->date = $date;
        $this->errorModel = $errorModel;
        $this->entityList = $entityList;
        $this->entityTypeModel = $entityTypeModel;
        $this->erpMessageQueue = $erpMessageQueue;
        $this->scopeConfig = $scopeConfig;
        $this->coreRegistry = $coreRegistry;
        $this->regionModel = $regionModel;
        $this->moduleManager = $moduleManager;
        $this->magentoMessageQueue = $magentoMessageQueue;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->urlInterface = $urlInterface;
        // @codingStandardsIgnoreStart
        $this->_redirect = $redirect;
        // @codingStandardsIgnoreEnd
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->groupRepository = $groupRepository;
        $this->storeWebsiteRelation = $storeWebsiteRelation;

        parent::__construct($context);
    }

    /**
     * Check whether the I95DevConnect_MessageQueue is enabled or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            'i95dev_messagequeue/i95dev_extns/enabled',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getStore()->getWebsiteId()
        );
    }

    /**
     * Check whether the I95DevConnect_MessageQueue is enabled in any website
     *
     * @return boolean
     */
    public function isEnabledInAnyWebsite()
    {
        foreach ($this->storeManager->getWebsites() as $website) {
            $isEnabled = $this->scopeConfig->getValue(
                'i95dev_messagequeue/i95dev_extns/enabled',
                ScopeInterface::SCOPE_WEBSITE,
                $website->getId()
            );
            if ($isEnabled) {
                return true; // enabled in at least one website
            }
        }
        return false; // not enabled in any website
    }

    /*
    * Get enabled website ids
    */
    public function getEnabledWebsiteIds()
    {
       $enabledWebsiteIds = [];
       foreach ($this->storeManager->getWebsites() as $website) {
            $isEnabled = $this->scopeConfig->getValue(
                'i95dev_messagequeue/i95dev_extns/enabled',
                ScopeInterface::SCOPE_WEBSITE,
                $website->getId()
            );
            if ($isEnabled) {
               $enabledWebsiteIds[] = $website->getId(); // enabled in at least one website
            }
        }
        return $enabledWebsiteIds; // not enabled in any website
    }
 
    /**
     * Get sync entities
     *
     * @return array
     */
    public function getSyncEntities()
    {
        $canSyncEntity = [];
        $syncOrder = $this->_getSyncOrder();
        asort($syncOrder);
        $allOrderedEntites = array_keys($syncOrder);
        $availableModels = $this->getConfigModels();
        foreach ($allOrderedEntites as $entity) {
            if (array_key_exists($entity, $availableModels)) {
                $canSyncEntity[] = $entity;
            }
        }

        return $canSyncEntity;
    }

    /**
     * Get sync order
     *
     * @return array
     */
    public function _getSyncOrder()// phpcs:ignore
    {
        $xml_data = $this->readXml('etc', self::MODULE_NAME, self::MQ_XML, 'config');
        return (array)$xml_data['syncorder'];
    }

    /**
     * Reads the xml data from the given xml file
     *
     * @param string $dir
     * @param string $module
     * @param string $xml
     * @param string $node
     * @return array
     */
    public function readXml($dir, $module, $xml, $node)
    {
        $xml_data = [];
        try {
            $path = $this->reader->getModuleDir($dir, $module);
            $xmlPath = $path . DS . $xml;
            $xml_data = $this->parser->load($xmlPath)->xmlToArray();
        } catch (LocalizedException $e) {
            $this->logger->createLog(__METHOD__, $e->getMessage(), 'xmlerror', LoggerInterface::CRITICAL);
        }
        return $xml_data[$node];
    }

    /**
     * Get config entities data of messagequeue.xml
     *
     * @return array
     */
    public function getConfigModels()
    {
        $xml_data = $this->readXml('etc', self::MODULE_NAME, self::MQ_XML, 'config');
        $configEntities = (array)$xml_data['entities'];
        foreach ($configEntities as $key => $val) {
            if ($val === 0) {
                unset($configEntities[$key]);
            }
        }
        return $configEntities;
    }

    /**
     * Get entity type list
     *
     * @return array
     */
    public function getEntityTypeList()
    {
        $entityType = [];
        // @updatedBy Arushi Bansal
        $component = $this->getComponent();
        $collectionList = $this->entityTypeModel->create()->getCollection()
            ->setOrder(self::ENTITY_NAME, 'ASC');
        if ($component == "GP") {
            $entity = [self::MATRIXPRODUCT];
            $collectionList->addFieldToFilter(self::ENTITY_CODE, ['nin' => $entity]);
        }
        if ($collectionList->getSize() > 0) {
            foreach ($collectionList as $collection) {
                $entityType[$collection->getEntityCode()] = $collection->getEntityName();
            }
        }
        return $entityType;
    }

    /**
     * Get Entity type inbound list
     *
     * @return array
     */
    public function getEntityTypeInboundList()
    {
        return $this->getEntityTypeListMq("support_for_inbound");
    }

    /**
     * Get Entity Type List Mq
     *
     * @param bool $supportedFor
     * @return array
     */
    public function getEntityTypeListMq($supportedFor)
    {
        $entityType = [];
        // @updatedBy Arushi Bansal
        $component = $this->getComponent();
        // @updatedBy Arushi Bansal
        $collectionList = $this->entityTypeModel->create()->getCollection()
            ->addFieldToFilter($supportedFor, true)
            ->setOrder(self::ENTITY_NAME, 'ASC');
        if ($component == "GP") {
            $entity = [self::MATRIXPRODUCT];
            $collectionList->addFieldToFilter(self::ENTITY_CODE, ['nin' => $entity]);
        }
        //@author Divya Koona. Added to exclude Customer Group entity IBMQ for NAV
        if ($component == "NAV") {
            $entity = ['CustomerGroup'];
            $collectionList->addFieldToFilter(self::ENTITY_CODE, ['nin' => $entity]);
        }
        if ($collectionList->getSize() > 0) {
            foreach ($collectionList as $collection) {
                $entityType[$collection->getEntityCode()] = $collection->getEntityName();
            }
        }
        return $entityType;
    }

    /**
     * Get entity type outbound list
     *
     * @return array
     */
    public function getEntityTypeOutboundList()
    {
        return $this->getEntityTypeListMq("support_for_outbound");
    }

    /**
     * Get Entity type list by sync order
     *
     * @return array
     */
    public function getEntityTypeListBySyncOrder()
    {
        $entityType = [];
        // @updatedBy Arushi Bansal
        $component = $this->getComponent();
        $collectionList = $this->entityTypeModel->create()->getCollection()
            ->setOrder('sort_order', 'ASC');
        if ($component == "GP") {
            $entity = [self::MATRIXPRODUCT];
            $collectionList->addFieldToFilter(self::ENTITY_CODE, ['nin' => $entity]);
        }
        if ($collectionList->getSize() > 0) {
            foreach ($collectionList as $collection) {
                $entityType[$collection->getEntityCode()] = $collection->getEntityName();
            }
        }
        return $entityType;
    }

    /**
     * Get Scope Config Object
     *
     * @param string $value
     * @param string $scope
     * @param int $id
     *
     * @return string
     */
    public function getscopeConfig($value, $scope, $id = null)
    {
        if ($id === null) {
            $id = $this->storeManager->getDefaultStoreView()->getWebsiteId();
        }
        return $this->scopeConfig->getValue($value, $scope, $id);
    }

    /**
     * Get global value
     *
     * @param string $key
     * @return Registry $coreRegistry
     */
    public function getGlobalValue($key)
    {
        $globalKey = self::CUSTOM_IDENTIFIER . '_' . $key;
        return $this->coreRegistry->registry($globalKey);
    }

    /**
     * To set global value
     *
     * @param string $key
     * @param string $value
     */
    public function setGlobalValue($key, $value)
    {
        $globalKey = self::CUSTOM_IDENTIFIER . '_' . $key;
        $this->coreRegistry->register($globalKey, $value);
    }

    /**
     * Unset the global value
     *
     * @param string $key
     */
    public function unsetGlobalValue($key)
    {
        $globalKey = self::CUSTOM_IDENTIFIER . '_' . $key;
        $this->coreRegistry->unregister($globalKey);
    }

   /**
     * Check whether the I95DevConnect_MessageQueue is enabled or not
     *
     * @return boolean
     */
    public function isWebsiteEnabled($website_id)
    {
        return $this->scopeConfig->getValue(
            'i95dev_adapter_configurations/enabled_disabled/enabled',
            ScopeInterface::SCOPE_WEBSITE,
            $website_id
        );
    }
 
}
