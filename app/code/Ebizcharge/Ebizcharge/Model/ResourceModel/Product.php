<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\ResourceModel;

use Ebizcharge\Ebizcharge\Api\Data\ProductInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Ebizcharge\Ebizcharge\Model\ProductFactory as EbizProductFactory;
use Magento\Catalog\Api\Data\ProductInterface as ProductInterfaceAlias;
use Magento\Catalog\Model\Factory;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\Product\Attribute\DefaultAttributes;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product as coreProductResource;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\Entity\Attribute\UniqueValidationInterface;
use Magento\Eav\Model\Entity\Context;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Indexer\Model\Indexer\CollectionFactory as indexCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Resource Model Product Class
 *
 * Class Product
 */
class Product extends coreProductResource
{

    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $_resourceConnection;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var indexCollectionFactory
     */
    protected indexCollectionFactory $_indexCollection;

    /**
     * @var EbizProductFactory
     */
    protected EbizProductFactory $_productFactory;

    /**
     * @var ConfigFactory
     */
    protected ConfigFactory $configFactory;

    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $sessionManagerInterface;


    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Factory $modelFactory
     * @param CollectionFactory $categoryCollectionFactory
     * @param Category $catalogCategory
     * @param ManagerInterface $eventManager
     * @param SetFactory $setFactory
     * @param TypeFactory $typeFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param indexCollectionFactory $indexCollection
     * @param ResourceConnection $resourceConnection
     * @param EbizProductFactory $productFactory
     * @param DefaultAttributes $defaultAttributes
     * @param ConfigFactory $configFactory
     * @param SessionManagerInterface $sessionManagerInterface
     * @param TableMaintainer|null $tableMaintainer
     * @param UniqueValidationInterface|null $uniqueValidator
     * @param AttributeManagementInterface|null $eavAttributeManagement
     * @param $data
     */
    public function __construct(
        Context                      $context,
        StoreManagerInterface        $storeManager,
        Factory                      $modelFactory,
        CollectionFactory            $categoryCollectionFactory,
        Category                     $catalogCategory,
        ManagerInterface             $eventManager,
        SetFactory                   $setFactory,
        TypeFactory                  $typeFactory,
        EbizchargeLogger             $ebizchargeLogger,
        indexCollectionFactory       $indexCollection,
        ResourceConnection           $resourceConnection,
        EbizProductFactory           $productFactory,
        DefaultAttributes            $defaultAttributes,
        ConfigFactory                $configFactory,
        SessionManagerInterface      $sessionManagerInterface,
        TableMaintainer              $tableMaintainer = null,
        UniqueValidationInterface    $uniqueValidator = null,
        AttributeManagementInterface $eavAttributeManagement = null,
                                     $data = []
    )
    {
        /** @var _resourceConnection */
        $this->_resourceConnection = $resourceConnection;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _indexCollection */
        $this->_indexCollection = $indexCollection;
        /** @var _productFactory */
        $this->_productFactory = $productFactory;
        /** @var  configFactory */
        $this->configFactory = $configFactory;
        /** @var  sessionManagerInterface */
        $this->sessionManagerInterface = $sessionManagerInterface;

        parent::__construct(
            $context,
            $storeManager,
            $modelFactory,
            $categoryCollectionFactory,
            $catalogCategory,
            $eventManager,
            $setFactory,
            $typeFactory,
            $defaultAttributes,
            $data,
            $tableMaintainer,
            $uniqueValidator,
            $eavAttributeManagement
        );
    }


    /**
     * Get Id By Ebiz Internal Id
     *
     * @param string $ebizInternalId
     * @return string
     */
    public function getIdByEbizInternalId($ebizInternalId = '')
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getEntityTable(), 'entity_id')
            ->where(ProductInterface::EC_ITEM_INTERNALID . ' = :' . ProductInterface::EC_ITEM_INTERNALID);
        $bind = [':' . ProductInterface::EC_ITEM_INTERNALID => (string)$ebizInternalId];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Get Id By Ebiz Item ID
     *
     * @param string $ebizSID
     * @return string
     */
    public function getIdByEbizItemId($ebizSID = '')
    {
        /** @var  $connection */
        $connection = $this->getConnection();
        /** @var  $select */
        $select = $connection->select()
            ->from($this->getEntityTable(), 'entity_id')
            ->where(ProductInterface::EC_ITEM_ID . ' = :' . ProductInterface::EC_ITEM_ID);
        $bind = [':' . ProductInterface::EC_ITEM_ID => $ebizSID];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * @param string $ebizSku
     * @return string
     */
    public function loadBySku(string $ebizSku = "")
    {
        /** @var  $connection */
        $connection = $this->getConnection();
        /** @var  $select */
        $select = $connection->select()
            ->from($this->getEntityTable(), 'entity_id')
            ->where(ProductInterfaceAlias::SKU . ' = :' . ProductInterfaceAlias::SKU);
        $bind = [':' . ProductInterfaceAlias::SKU => $ebizSku];
        return $connection->fetchOne($select, $bind);
    }


    /**
     * @param DataObject $object
     * @return $this|void
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws LocalizedException
     */
    public function afterSave(DataObject $object)
    {
        parent::afterSave($object);

        if ($object->getId()) {
            $object = $this->_productFactory->create()->load($object->getId());
        }
        /** save Item  */
        // phpcs:ignore
        $this->saveEbizchargeFields($object);
        return $this;
    }

    /**
     * @param $product
     * @return $this
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws LocalizedException
     */
    public function saveEbizchargeFields($product)
    {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStore()->getId();
        $isEbizActive = $configFactory->isActive($storeId);
        $isItemsDownloadEnabled = $configFactory->isEconnectDownlaodEnabled($storeId);
        $isItemUpload = $configFactory->isUploadItemsEnabled($storeId);
        $isItemUploadActive = $isItemsDownloadEnabled && $isItemUpload;
        $isDownload = $product->getIsDownload() ?? $this->sessionManagerInterface->getisDownload();

        if (!$isEbizActive || !$isItemUploadActive) {
            return $this;
        }

        /** @var $connection */
        $connection = $this->getConnection();
        $productId = $product->getId();
        $ebizInternalId = $product->getEcItemInternalId();
        $evtPrefix = $configFactory->getEnvoirnmentPrefix($storeId);
        $productItemId = $product->getEcItemId() ?? $productId;

        if (!empty($evtPrefix)) {
            $productItemId = $product->getEcItemId() ?? $evtPrefix . "-" . $productId;
        }

        $ebizDivisionId = $product->getDivisionId() ?? $configFactory->getDivisionID($storeId);
        $ebizSoftwareId = $product->getSoftwareId() ?? $configFactory->getSoftwareId();
        $lastSyncDate = $product->getEcItemLastSyncDate() ?? date('Y-m-d H:i:s');
        $ebizCreatedIn = $product->getEcCreatedIn() ?? $configFactory->getSoftwareId();


        /** Updating Product resource getting Product from EBizCharge */
        $ebizProductParams = $this->_productFactory->create()->uploadItemToEbizcharge($product);
        $ebizInternalId = $ebizProductParams[ProductInterface::EC_ITEM_INTERNALID] ?? $ebizInternalId;
        $ebizSoftwareId = $ebizProductParams[ProductInterface::EBIZCHARGE_SOFTWARE_ID] ?? $ebizSoftwareId;
        $ebizCreatedIn = $ebizProductParams[ProductInterface::EC_CREATED_IN] ?? $ebizCreatedIn;
        $ebizDivisionId = $ebizProductParams[ProductInterface::EBIZCHARGE_DIVISION_ID] ?? $ebizDivisionId;
        $lastSyncDate = $ebizProductParams[ProductInterface::EC_ITEM_LASTSYNCDATE] ?? $lastSyncDate;

        /** @var $whereClause */
        $whereClause = $connection->quoteInto('entity_id' . " = ?", $productId);

        /** Binding the EBizCharge Fields $bind */
        $bind = [
            ProductInterface::EC_ITEM_SYNC_STATUS => 1,
            ProductInterface::EC_ITEM_INTERNALID => $ebizProductParams[ProductInterface::EC_ITEM_INTERNALID] ?? $ebizInternalId,
            ProductInterface::EC_ITEM_ID => $ebizProductParams[ProductInterface::EC_ITEM_ID] ?? $productItemId,
            ProductInterface::EBIZCHARGE_SOFTWARE_ID => $ebizSoftwareId,
            ProductInterface::EBIZCHARGE_DIVISION_ID => $ebizDivisionId,
            ProductInterface::EC_CREATED_IN => $ebizCreatedIn,
            ProductInterface::EC_ITEM_LASTSYNCDATE => $lastSyncDate,
        ];

        /** updating the extra fields via Customer Resource Model */
        $this->getConnection()->update($this->getEntityTable(), $bind, $whereClause);

        return $this;
    }
}
