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

namespace Ebizcharge\Ebizcharge\Model;

use DateTime;
use Ebizcharge\Ebizcharge\Api\Data\ProductInterface;
use Ebizcharge\Ebizcharge\Api\Data\SoapApiModelInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory as EbizConfigFactory;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Product as EbizProductResourceModel;
use Ebizcharge\Ebizcharge\Model\TranApiFactory as SoapApiModelFactory;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductLinkExtensionFactory;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ScopedProductTierPriceManagementInterface;
use Magento\Catalog\Api\TierPriceStorageInterface;
use Magento\Catalog\Helper\Data as TaxHelper;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\FilterProductCustomAttribute as FilterProductCustomAttribute;
use Magento\Catalog\Model\Indexer\Product\Eav\Processor;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor as ProductFlatProcessor;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as ProductPriceProcessor;
use Magento\Catalog\Model\Product as CoreProductModel;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Configuration\Item\OptionFactory as OptionItemFactory;
use Magento\Catalog\Model\Product\Image\CacheFactory;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\Product\Media\Config as ProductConfigModel;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Url;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductLink\CollectionProvider;
use Magento\Catalog\Model\ProductRepository as CoreProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogRule\Model\CatalogRuleRepository;
use Magento\CatalogRule\Model\Product\PriceModifier;
use Magento\CatalogRule\Model\Rule;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory as EmailTemplateCollectionFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Model\Context;
use Magento\Framework\Module\Manager;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Indexer\Model\Indexer\CollectionFactory as IndexerCollectionFactory;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Setup\Exception;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use SoapFault;

/*

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\InventoryCatalogApi\Model\SourceItemsProcessorInterface;
*/

/**
 * Product Model Class
 *
 * Class Product
 */
class Product extends CoreProductModel implements ProductInterface
{

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected ProductAttributeRepositoryInterface $_metadataService;


    protected EbizConfigFactory $configFactory;

    /**
     * @var OptionItemFactory
     */
    protected $_itemOptionFactory;

    /**
     * @var StockItemInterfaceFactory
     */
    protected $_stockItemFactory;

    /**
     * @var OptionFactory
     */
    protected OptionFactory $_optionFactory;

    /**
     * @var Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var Status
     */
    protected $_catalogProductStatus;

    /**
     * @var ProductConfigModel
     */
    protected $_catalogProductMediaConfig;

    /**
     * @var Type
     */
    protected $_catalogProductType;

    /**
     * @var Manager
     */
    protected Manager $_moduleManager;

    /**
     * @var ProductHelper
     */
    protected $_catalogProduct;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var Url
     */
    protected $_urlModel;

    /**
     * @var Link
     */
    protected $_linkInstance;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var IndexerRegistry
     */
    protected IndexerRegistry $_indexerRegistry;

    /**
     * @var ProductFlatProcessor
     */
    protected $_productFlatIndexerProcessor;

    /**
     * @var ProductPriceProcessor
     */
    protected $_productPriceIndexerProcessor;

    /**
     * @var Processor
     */
    protected $_productEavIndexerProcessor;

    /**
     * @var CategoryRepositoryInterface
     */
    protected CategoryRepositoryInterface $_categoryRepository;

    /**
     * @var CacheFactory
     */
    protected CacheFactory $_imageCacheFactory;

    /**
     * @var CollectionProvider
     */
    protected CollectionProvider $_entityCollectionProvider;

    /**
     * @var LinkTypeProvider
     */
    protected LinkTypeProvider $_linkTypeProvider;

    /**
     * @var ProductLinkInterfaceFactory
     */
    protected ProductLinkInterfaceFactory $_productLinkFactory;

    /**
     * @var ProductLinkExtensionFactory
     */
    protected ProductLinkExtensionFactory $_productLinkExtensionFactory;

    /**
     * @var EntryConverterPool
     */
    protected EntryConverterPool $_mediaGalleryEntryConverterPool;

    /**
     * @var DataObjectHelper
     */
    protected DataObjectHelper $_dataObjectHelper;

    /**
     * @var JoinProcessorInterface
     */
    protected JoinProcessorInterface $_joinProcessor;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var SoapApiModelFactory
     */
    protected SoapApiModelFactory $tranApiFactory;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected CategoryLinkManagementInterface $_categoryLinkManagement;

    /**
     * @var SyncAssetsFactory
     */
    protected SyncAssetsFactory $_syncAssetsFactory;


    protected $_getSalableQuantityDataBySku;


    protected ProductCollectionFactory $_productCollectionFactory;

    /**
     * @var TaxCalculationInterface
     */
    protected TaxCalculationInterface $_taxCalculationInterface;

    /**
     * @var Image
     */
    protected Image $_imageHelper;

    /**
     * @var Config
     */
    protected Config $_configResource;

    /**
     * @var PriceHelper
     */
    protected PriceHelper $_priceHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $_timezoneInterface;

    /**
     * @var DirectoryList
     */
    protected DirectoryList $_directoryList;

    /**
     * @var TaxHelper
     */
    protected TaxHelper $_taxHelper;

    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $_recurringFactory;

    /**
     * @var CoreProductRepository
     */
    protected CoreProductRepository $_productRepository;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var StateInterface
     */
    protected StateInterface $_stateInterface;

    /**
     * @var TransportBuilder
     */
    protected TransportBuilder $_transportBuilder;

    /**
     * @var PriceModifier
     */
    protected PriceModifier $_priceRuleModifier;

    /**
     * @var Rule
     */
    protected Rule $_catalogRuleModel;

    /**
     * @var CatalogRuleRepository
     */
    protected CatalogRuleRepository $_catalogRuleRepository;

    /**
     * @var EmailTemplateCollectionFactory
     */
    protected EmailTemplateCollectionFactory $_emailTemplateCollectionFctory;

    /**
     * @var Image
     */
    protected Image $_productImage;

    /**
     * @var TierPriceStorageInterface
     */
    protected TierPriceStorageInterface $_tierPriceStorage;

    /**
     * @var CustomerGroupCollectionFactory
     */
    protected CustomerGroupCollectionFactory $_customerGroupCollectionFactory;

    /**
     * @var ProductTierPriceInterfaceFactory
     */
    protected ProductTierPriceInterfaceFactory $_productTierPriceInterfaceFactory;

    /**
     * @var ScopedProductTierPriceManagementInterface
     */
    protected ScopedProductTierPriceManagementInterface $_scopedProductTierPriceManagement;

    /**
     * @var IndexerFactory
     */
    protected IndexerFactory $indexFactory;

    /**
     * @var IndexerCollectionFactory
     */
    protected IndexerCollectionFactory $indexCollection;

    protected $sourceItemsProcessor;

    protected StockRegistryInterface $stockRegistryInterface;
    /**
     * @var $_sourceItemsSave
     */
    protected $_sourceItemsSave;

    protected $_sourceItemInterfaceFactory;
    /**
     * @var EbizProductResourceModel
     */
    protected EbizProductResourceModel $productResourceModel;
    protected ProductRepositoryInterface $productRepository;
    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $sessionManagerInterface;

    public function __construct(
        Context                                   $context,
        Registry                                  $registry,
        ExtensionAttributesFactory                $extensionFactory,
        AttributeValueFactory                     $customAttributeFactory,
        StoreManagerInterface                     $storeManager,
        ProductAttributeRepositoryInterface       $metadataService,
        Url                                       $url,
        Link                                      $productLink,
        OptionItemFactory                         $itemOptionFactory,
        OptionFactory                             $catalogProductOptionFactory,
        StockItemInterfaceFactory                 $stockItemFactory,
        Visibility                                $catalogProductVisibility,
        Status                                    $catalogProductStatus,
        ProductConfigModel                        $catalogProductMediaConfig,
        Type                                      $catalogProductType,
        Manager                                   $moduleManager,
        ProductHelper                             $catalogProduct,
        EbizProductResourceModel                  $productResourceModel,
        CoreProductRepository                     $productRepository,
        Collection                                $resourceCollection,
        CollectionFactory                         $collectionFactory,
        Filesystem                                $filesystem,
        DirectoryList                             $directoryList,
        CustomerFactory                           $customerFactory,
        PriceCurrencyInterface                    $priceCurrency,
        IndexerRegistry                           $indexerRegistry,
        ProductFlatProcessor                      $productFlatIndexerProcessor,
        ProductPriceProcessor                     $productPriceIndexerProcessor,
        Processor                                 $productEavIndexerProcessor,
        TimezoneInterface                         $timezoneInterface,
        CategoryRepositoryInterface               $categoryRepository,
        CacheFactory                              $imageCacheFactory,
        CollectionProvider                        $entityCollectionProvider,
        LinkTypeProvider                          $linkTypeProvider,
        ProductLinkInterfaceFactory               $productLinkFactory,
        ProductLinkExtensionFactory               $productLinkExtensionFactory,
        EntryConverterPool                        $mediaGalleryEntryConverterPool,
        DataObjectHelper                          $dataObjectHelper,
        JoinProcessorInterface                    $joinProcessor,
        EbizchargeLogger                          $ebizchargeLogger,
        CategoryLinkManagementInterface           $categoryLinkManagement,
        SoapApiModelFactory                       $tranApiFactory,
        ConfigFactory                             $configFactory,
        TaxCalculationInterface                   $taxCalculationInterface,
        Image                                     $imageHelper,
        RecurringFactory                          $recurringFactory,
        EmailTemplateCollectionFactory            $emailTemplateCollectionFctory,
        StateInterface                            $stateInterface,
        PriceModifier                             $priceRuleModifier,
        Rule                                      $catalogRuleModel,
        CatalogRuleRepository                     $catalogRuleRepository,
        TransportBuilder                          $transportBuilder,
        ProductCollectionFactory                  $productCollectionFactory,
        SyncAssetsFactory                         $syncAssetsFactory,
        TaxHelper                                 $taxHelper,
        PriceHelper                               $priceHelper,
        Image                                     $productImage,
        TierPriceStorageInterface                 $tierPriceStorage,
        CustomerGroupCollectionFactory            $customerGroupCollectionFactory,
        ProductTierPriceInterfaceFactory          $productTierPriceInterfaceFactory,
        ScopedProductTierPriceManagementInterface $scopedProductTierPriceManagement,
        IndexerFactory                            $indexFactory,
        IndexerCollectionFactory                  $indexCollection,
        SessionManagerInterface                   $sessionManagerInterface,
        ProductRepositoryInterface                $productRepositoryInterface,
        StockRegistryInterface                    $stockRegistryInterface,
        EavConfig                                 $config = null,
        FilterProductCustomAttribute              $filterCustomAttribute = null,
        array                                     $data = []
    )
    {
        /** Parent Construct  */
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager,
            $metadataService,
            $url,
            $productLink,
            $itemOptionFactory,
            $stockItemFactory,
            $catalogProductOptionFactory,
            $catalogProductVisibility,
            $catalogProductStatus,
            $catalogProductMediaConfig,
            $catalogProductType,
            $moduleManager,
            $catalogProduct,
            $productResourceModel,
            $resourceCollection,
            $collectionFactory,
            $filesystem,
            $indexerRegistry,
            $productFlatIndexerProcessor,
            $productPriceIndexerProcessor,
            $productEavIndexerProcessor,
            $categoryRepository,
            $imageCacheFactory,
            $entityCollectionProvider,
            $linkTypeProvider,
            $productLinkFactory,
            $productLinkExtensionFactory,
            $mediaGalleryEntryConverterPool,
            $dataObjectHelper,
            $joinProcessor,
            $data,
            $config,
            $filterCustomAttribute
        );
        /** @var _metadataService */
        $this->_metadataService = $metadataService;
        /** @var _itemOptionFactory */
        $this->_itemOptionFactory = $itemOptionFactory;
        /** @var _optionFactory */
        $this->_optionFactory = $catalogProductOptionFactory;
        /** @var _catalogProductVisibility */
        $this->_catalogProductVisibility = $catalogProductVisibility;
        /** @var _catalogProductStatus */
        $this->_catalogProductStatus = $catalogProductStatus;
        /** @var _catalogProductMediaConfig */
        $this->_catalogProductMediaConfig = $catalogProductMediaConfig;
        /** @var _catalogProductType */
        $this->_catalogProductType = $catalogProductType;
        /** @var _moduleManager */
        $this->_moduleManager = $moduleManager;
        /** @var _catalogProduct */
        $this->_catalogProduct = $catalogProduct;
        /** @var _collectionFactory */
        $this->_collectionFactory = $collectionFactory;
        /** @var _urlModel */
        $this->_urlModel = $url;
        /** @var _productRepository */
        $this->_productRepository = $productRepository;
        /** @var _linkInstance */
        $this->_linkInstance = $productLink;
        /** @var _filesystem */
        $this->_filesystem = $filesystem;
        /** @var _indexerRegistry */
        $this->_indexerRegistry = $indexerRegistry;
        /** @var _productFlatIndexerProcessor */
        $this->_productFlatIndexerProcessor = $productFlatIndexerProcessor;
        /** @var _productPriceIndexerProcessor */
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
        /** @var _productEavIndexerProcessor */
        $this->_productEavIndexerProcessor = $productEavIndexerProcessor;
        /** @var _categoryRepository */
        $this->_categoryRepository = $categoryRepository;
        /** @var _imageCacheFactory */
        $this->_imageCacheFactory = $imageCacheFactory;
        /** @var _entityCollectionProvider */
        $this->_entityCollectionProvider = $entityCollectionProvider;
        /** @var _linkTypeProvider */
        $this->_linkTypeProvider = $linkTypeProvider;
        /** @var _productLinkFactory */
        $this->_productLinkFactory = $productLinkFactory;
        /** @var _productLinkExtensionFactory */
        $this->_productLinkExtensionFactory = $productLinkExtensionFactory;
        /** @var _mediaGalleryEntryConverterPool */
        $this->_mediaGalleryEntryConverterPool = $mediaGalleryEntryConverterPool;
        /** @var _dataObjectHelper */
        $this->_dataObjectHelper = $dataObjectHelper;
        /** @var _joinProcessor */
        $this->_joinProcessor = $joinProcessor;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var  tranApiFactory */
        $this->tranApiFactory = $tranApiFactory;
        /** @var _categoryLinkManagement */
        $this->_categoryLinkManagement = $categoryLinkManagement;
        /** @var _syncAssetsFactory */
        $this->_syncAssetsFactory = $syncAssetsFactory;
        /** @var _productCollectionFactory */
        $this->_productCollectionFactory = $productCollectionFactory;
        /** @var _taxCalculationInterface */
        $this->_taxCalculationInterface = $taxCalculationInterface;
        /** @var _imageHelper */
        $this->_imageHelper = $imageHelper;
        /** @var  configFactory */
        $this->configFactory = $configFactory;
        /** @var _priceHelper */
        $this->_priceHelper = $priceHelper;
        /** @var  _priceCurrency */
        $this->_priceCurrency = $priceCurrency;
        /** @var _timeZoneInterface */
        $this->_timezoneInterface = $timezoneInterface;
        /** @var _directoryList */
        $this->_directoryList = $directoryList;
        /** @var _taxHelper */
        $this->_taxHelper = $taxHelper;
        /** @var _recurringFactory */
        $this->_recurringFactory = $recurringFactory;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _transportBuilder */
        $this->_transportBuilder = $transportBuilder;
        /** @var _stateInterface */
        $this->_stateInterface = $stateInterface;
        /** @var _emailTemplateCollectionFctory */
        $this->_emailTemplateCollectionFctory = $emailTemplateCollectionFctory;
        /** @var _catalogRuleModel */
        $this->_catalogRuleModel = $catalogRuleModel;
        /** @var _catalogRuleRepository */
        $this->_catalogRuleRepository = $catalogRuleRepository;
        /** @var _priceRuleModifier */
        $this->_priceRuleModifier = $priceRuleModifier;
        /** @var  _productImage */
        $this->_productImage = $productImage;
        /** @var  _tierPriceStorage */
        $this->_tierPriceStorage = $tierPriceStorage;
        /** @var  _customerGroupCollectionFactory */
        $this->_customerGroupCollectionFactory = $customerGroupCollectionFactory;
        /** @var  _productTierPriceInterfaceFactory */
        $this->_productTierPriceInterfaceFactory = $productTierPriceInterfaceFactory;
        /** @var  _scopedProductTierPriceManagement */
        $this->_scopedProductTierPriceManagement = $scopedProductTierPriceManagement;
        /** @var  indexFactory */
        $this->indexFactory = $indexFactory;
        /** @var  indexCollection */
        $this->indexCollection = $indexCollection;
        /** @var  sessionManagerInterface */
        $this->sessionManagerInterface = $sessionManagerInterface;
        $this->productResourceModel = $productResourceModel;
        $this->stockRegistryInterface = $stockRegistryInterface;
        /**
         * Product Repository
         */
        $this->productRepository = $productRepositoryInterface;


    }

    /**
     * Get Salable Qty
     *
     * @param $sku
     * @return mixed
     */
    public function getSalableQty($sku = ''): mixed
    {
        $salableQty = $this->_getSalableQuantityDataBySku->execute($sku);
        return $salableQty[0]['qty'];
    }

    /**
     * Make Available Inventory for Order
     *
     * @param Product $product
     * @return mixed
     */
    public function makeAvailableInventoryforOrder($product): mixed
    {
        $sku = $product->getSku();
        return $this->productResourceModel->updateInventoryReservationBySku($sku);
    }

    /**
     * Set Product Source Stock
     *
     * @param string $productSku
     * @param array $stockParams
     * @return void
     * @throws InputException
     */
    public function setProductSourceStock($productSku = "", $stockParams = []): void
    {
        $stockSource = isset($stockParams["source"]) ? $stockParams["source"] : "default";
        $status = isset($stockParams["status"]) ? $stockParams["status"] : "1";
        $stockQty = isset($stockParams["quantity"]) ? $stockParams["quantity"] : "1";
        $stockData = [
            [
                'source_code' => $stockSource,
                'status' => $status,
                'quantity' => $stockQty
            ],
        ];

        $this->sourceItemsProcessor->execute(
            $productSku,
            $stockData
        );
    }

    /**
     * Set Salable Stock Qty
     *
     * @param string $sku
     * @param array $stockParams
     * @return bool
     */
    public function setSalableStockQty(string $sku = '', array $stockParams = []): bool
    {
        try {
            $product = $this->loadByAttribute('sku', $sku);

            $stockItem = $this->stockRegistryInterface->getStockItem($product->getEntityId());
            $stockItem->setData('is_in_stock', $stockParams['is_in_stock']); //set updated data as your requirement
            $stockItem->setData('qty', $stockParams['qty']); //set updated quantity
            $stockItem->setData('manage_stock', $stockParams['manage_stock']);
            $stockItem->setData('use_config_notify_stock_qty', ProductInterface::DEFAULT_MANAGE_STOCK);

            $stockItem->save(); //save stock of item
            $product->save(); //  also save product

            $this->_ebizchargeLogger->addInfo('Stock updated for the SKU:' . $sku);

            return true;
        } catch (\Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during updating Stock Error: " .
                $exception->getMessage()));
            return false;
        }
    }

    /**
     * Get Ec Item Sync Status
     *
     * @return mixed|null
     */
    public function getEcItemSyncStatus()
    {
        return $this->getData(ProductInterface::EC_ITEM_SYNC_STATUS);
    }

    /**
     * Prepare prepareProductParamsFromEbizOrderItem
     *
     * @param array $orderItemParams
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareProductParamsFromEbizOrderItem($orderItemParams = []): array
    {
        if (count($orderItemParams) == 0) {
            return [];
        }

        return [
            'ItemId' => isset($orderItemParams['itemid']) ? $orderItemParams['itemid'] : 0,
            'ItemInternalId' => isset($orderItemParams['ItemInternalId']) ?
                trim($orderItemParams['ItemInternalId']) : '',
            'Name' => isset($orderItemParams['name']) ? trim($orderItemParams['name']) : '',
            'SKU' => isset($orderItemParams['name']) ? trim($orderItemParams['name']) : '',
            'Description' => isset($orderItemParams['description']) ? trim($orderItemParams['description']) : '',
            'UnitPrice' => isset($orderItemParams['unitprice']) ? $orderItemParams['unitprice'] : 0,
            'UnitCost' => isset($orderItemParams['unitprice']) ? $orderItemParams['unitprice'] : 0,
            'weight' => isset($orderItemParams['weight']) ? $orderItemParams['weight'] : 1,
            'Taxable' => isset($orderItemParams['taxable']) ? $orderItemParams['taxable'] : false,
            'TaxRate' => isset($orderItemParams['taxrate']) ? $orderItemParams['taxrate'] : '',
            'UnitOfMeasure' => isset($orderItemParams['unitofmeasure']) ? $orderItemParams['unitofmeasure'] : '',
            'ItemType' => isset($orderItemParams['ItemType']) ? $orderItemParams['ItemType'] : 'simple',
            'QtyOnHand' => isset($orderItemParams['qty']) ? $orderItemParams['qty'] : 0,
            'Active' => isset($orderItemParams['active']) ? $orderItemParams['active'] : 0,
            'ImageUrl' => isset($orderItemParams['ImageUrl']) ? $orderItemParams['ImageUrl'] : '',
            'DivisionId' => $orderItemParams['DivisionId'] ?? $this->tranApiFactory->create()->getDivisionId(),
            'SoftwareId' => $orderItemParams['SoftwareId'] ?? $this->tranApiFactory->create()->getSoftwareId(),
            'itemcustomfields' => isset($orderItemParams['itemcustomfields']) ?
                (array)$orderItemParams['itemcustomfields'] : []
        ];
    }

    /**
     * Get Division Id
     *
     * @return string
     */
    public function getDivisionId(): string|null
    {
        return (string)$this->getData(ProductInterface::EBIZCHARGE_DIVISION_ID);
    }

    /**
     * Get Software Id
     *
     * @return string
     */
    public function getSoftwareId(): string|null
    {
        return (string)$this->getData(ProductInterface::EBIZCHARGE_SOFTWARE_ID);
    }

    /**
     * Get Total Active Local Products
     *
     * @param mixed $active
     * @return int
     */
    public function getTotalActiveLocalProducts($active = 1)
    {
        return count($this->getLatestLocalProducts($active));
    }

    /**
     * Get Latest Local Products
     *
     * @param mixed $active
     * @return Collection|AbstractDb
     */
    public function getLatestLocalProducts($active = 1)
    {
        $active = $active === 1 ?? 0;

        $storeId = $this->configFactory->create()->getStoreId();
        $envPrefix = $this->configFactory->create()->getEnvoirnmentPrefix($storeId);

        /** @var $productsCollection */
        $productsCollection = $this->_productCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'status',
                [
                    'eq' => $active
                ]
            )
            ->addAttributeToFilter('type_id', Type::TYPE_SIMPLE);

        $productsCollection->getSelect()->where(ProductInterface::EBIZCHARGE_DIVISION_ID .
            ' like(\'' . $envPrefix . '%\') ');

        $conditions = ProductInterface::EC_ITEM_INTERNALID . ' IS NULL  OR ';
        $conditions .= ProductInterface::EC_ITEM_INTERNALID . '=""   OR ';
        $conditions .= ProductInterface::EC_ITEM_ID . ' IS NULL   OR ';
        $conditions .= ProductInterface::EC_ITEM_ID . '=""   OR ';
        $conditions .= ProductInterface::EBIZCHARGE_DIVISION_ID . ' IS NULL   OR ';
        $conditions .= ProductInterface::EBIZCHARGE_DIVISION_ID . '=""  ';

        /** adding others to collection */
        $productsCollection->getSelect()
            ->Where($conditions);
        //var_dump($productsCollection->getSelect()->__toString(), count($productsCollection));exit;

        return $productsCollection;
    }

    /**
     * Is Product Exists At EBizCharge
     *
     * @param mixed $ebizchargeInternalId
     * @param mixed $ebizchargeItems
     * @return bool
     */
    public function isProductExistsAtEbizcharge($ebizchargeInternalId, $ebizchargeItems)
    {
        /** @var $isEbizProductExists */
        $isEbizProductExists = false;

        if (count($ebizchargeItems) > 0) {
            foreach ($ebizchargeItems as $ebizchargeItem) {
                /** Ebizcharge Item */
                if ($ebizchargeItem->ItemInternalId === $ebizchargeInternalId) {
                    $isEbizProductExists = true;
                }
            }
        }
        return $isEbizProductExists;
    }

    /**
     * Get currency With Format
     *
     * @param mixed $price
     * @return string
     */
    public function getCurrencyWithFormat($price)
    {
        return $this->_priceCurrency->format($price, true, 2);
    }

    /**
     * Get Round Price
     *
     * @param mixed $price
     * @return float
     */
    public function getRoundedPrice($price)
    {
        return $this->_priceCurrency->round($price);
    }

    /**
     * Get Currency Symbol
     *
     * @return string
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->_priceCurrency->getCurrencySymbol();
    }

    /**
     * Get EbizDivisionId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getEbizChargeDivisionId()
    {
        return $this->tranApiFactory->create()->getDivisionId();
    }

    /**
     * Get EbizSoftware Id
     *
     * @return string
     */
    public function getEbizChargeSoftwareId()
    {
        return $this->tranApiFactory->create()->getSoftwareId();
    }

    /**
     * @param $productItem
     * @return array
     * @throws NoSuchEntityException
     */
    public function uploadItemToEbizcharge($productItem = null): array
    {
        /** @var $itemResponse */
        $itemResponse = [
            'error' => true,
            'status' => 'error',
            'message' => __(''),
            ProductInterface::EC_ITEM_ID => '',
            ProductInterface::EC_ITEM_INTERNALID => '',
            ProductInterface::EBIZCHARGE_SOFTWARE_ID => '',
            ProductInterface::EBIZCHARGE_DIVISION_ID => '',
            ProductInterface::EC_CREATED_IN => ''
        ];

        $ebizItemResp = [];
        $storeId = $this->getStore()->getId();
        $soapApiFactory = $this->tranApiFactory->create();
        $configFactory = $this->configFactory->create();
        $isEbizActive = $configFactory->isActive($storeId);
        $isItemsDownloadEnabled = $configFactory->isEconnectDownlaodEnabled($storeId);
        $isItemUpload = $configFactory->isUploadItemsEnabled($storeId);
        $isProductUpload = $isItemsDownloadEnabled && $isItemUpload;
        /**
         * if EBizCharge is not active
         * if $is Product Upload is not active
         */
        if (!$isEbizActive || !$isProductUpload) {
            return $itemResponse;
        }

        /** adding/updating item to EBizCharge */
        try {
            $product = $this->load($productItem->getId());
            $productId = $product->getId();
            $envPrefix = $configFactory->getEnvoirnmentPrefix($storeId);
            $productItemId = !empty($product->getEcItemId()) ? $product->getEcItemId() : $productId;

            if (!empty($envPrefix)) {
                $productItemId = !empty($product->getEcItemId()) ? $product->getEcItemId() : $envPrefix . "-" . $productId;
            }

            $productItemId = (string)$productItemId;
            $productTypeId = $product->getTypeId();
            $storeId = $this->_storeManager->getStore()->getId();
            $itemInternalId = $product->getEcItemInternalId();
            $productName = (string)($product->getName() ?? "");
            $productSku = (string)($product->getSku() ?? "");

            /**
             * if configurable product plz do not upload only simple product should be uploaded
             */
            if (in_array($productTypeId, ["configurable", "bundle", "grouped"])) {
                return $itemResponse;
            }

            $ebizItemId = trim(str_replace(" ", "", $productItemId));
            $ebizDivisionId = !empty($product->getDivisionId()) ? $product->getDivisionId() : $configFactory->getDivisionID($storeId);
            $ebizSoftwareId = !empty($product->getSoftwareId()) ? $product->getSoftwareId() : $configFactory->getSoftwareId();
            $lastSyncDate = !empty($product->getEcItemLastSyncDate()) ? $product->getEcItemLastSyncDate() : date('Y-m-d H:i:s');
            $ebizCreatedIn = !empty($product->getEcCreatedIn()) ? $product->getEcCreatedIn() : $configFactory->getSoftwareId();


            /** @var CoreProductModel $product */
            $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue() ?? 0;
            $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue() ?? 0;
            $weight = $product->getWeight() ?? 0;

            $productStock = $product->getQuantityAndStockStatus() ?? [];

            /** @var $productType */
            $productType = ($productItem->getTypeId() === Type::TYPE_VIRTUAL) ? 'Service' : 'inventory';
            $salableQty = isset($productStock["qty"]) ? $productStock["qty"] : 0;

            $productTaxClassId = $productItem->getTaxClassId() ?? false;
            $productDescription = $product->getShortDescription() ?
                substr($product->getShortDescription(), 0, 200) : '';
            $productImage = $this->getProductImage($product);

            /** @var $taxRate */
            $taxAmount = $this->_taxCalculationInterface->getDefaultCalculatedRate(
                $productTaxClassId,
                null,
                $storeId
            );
            $priceInclTaxAmount = $this->getPriceInclTax($product) ?? 0;

            /** @var $categoryIds */
            $categoryName = isset($this->getCategoryNames($product)[0]) ? $this->getCategoryNames($product)[0] : '';
            /** @var $unitOfMeasure */
            $unitOfMeasure = $this->configFactory->create()->getUnitOfMeasure($storeId);

            $costPrice = $product->getCostPrice() ?? $regularPrice;
            $finalPrice = $finalPrice > 0 ? $finalPrice : $regularPrice;
            $discountAmount = (double)$regularPrice - (double)$finalPrice;

            /** @var $customFields */
            $productCustomAttributes = $this->prepareCustomAttributes($product);

            /** add Product to EBizCharge via SOAP Api */
            $itemDetails = [
                'ItemId' => $ebizItemId,
                'Name' => trim((string)$productName),
                'SKU' => $productSku,
                'Description' => trim(strip_tags(substr((string)$product->getDescription(), 0, 200)) ?? "") ?? "",
                'UnitPrice' => (string)$finalPrice,
                'UnitCost' => (string)$costPrice,
                'UnitOfMeasure' => (string)$unitOfMeasure,
                'Active' => (string)($product->getStatus() ?? "0"),
                'Weight' => (string)$weight,
                'ItemType' => (string)$productType,
                'QtyOnHand' => (string)$salableQty,
                'SoftwareId' => (string)($ebizSoftwareId ?? $this->tranApiFactory->create()->getSoftwareId()),
                'UPC' => trim((string)($productSku ?? "")),
                'Taxable' => $productTaxClassId ? "1" : "0",
                'TaxRate' => (string)$taxAmount,
                'ItemCategoryId' => trim((string)($categoryName ?? "")),
                'TaxCategoryID' => $productTaxClassId,
                'ImageUrl' => (string)$productImage,
                'ItemNotes' => trim(strip_tags((string)$productDescription) ?? ""),
                'GrossPrice' => (string)$regularPrice,
                'WarrantyDiscount' => (string)$discountAmount,
                'DivisionId' => (string)$ebizDivisionId,
                'SalesDiscount' => (string)$discountAmount
            ];

            if (count($productCustomAttributes) > 0) {
                /** custom Fields */
                $itemDetails['ItemCustomFields'] = (array)$productCustomAttributes;
            }
            $soapClient = $soapApiFactory->getClient();

            if ($soapClient) {
                $ebizItemResp = $this->loadProductFromEbizChargeByEbizItemId($ebizItemId);

                if (count($ebizItemResp) > 0) {
                    $itemInternalId = isset($ebizItemResp["ItemInternalId"]) ? $ebizItemResp["ItemInternalId"] : "";
                    $itemResponse = [
                        'error' => false,
                        'status' => 'success',
                        'message' => __('Success, the product found at EBizCharge gateway.'),
                        ProductInterface::EC_ITEM_ID => $ebizItemId,
                        ProductInterface::EC_ITEM_INTERNALID => isset($ebizItemResp["ItemInternalId"]) ? $ebizItemResp["ItemInternalId"] : "",
                        ProductInterface::EBIZCHARGE_SOFTWARE_ID => $ebizSoftwareId,
                        ProductInterface::EBIZCHARGE_DIVISION_ID => $ebizDivisionId,
                        ProductInterface::EC_CREATED_IN => $ebizDivisionId
                    ];
                    $this->_ebizchargeLogger->addInfo(__("Product " . $productSku . " found at EBizCharge Gateway."));
                } else {

                    $addItemParams = [
                        'securityToken' => $soapApiFactory->getUeSecurityToken(),
                        'itemDetails' => $itemDetails
                    ];

                    /** @var  $addItemResponse */
                    $gatewayProductObj = $soapApiFactory->getClient()->AddItem($addItemParams);
                    $gatewayAddProductResp = isset($gatewayProductObj->AddItemResult) ? (array)$gatewayProductObj->AddItemResult : [];

                    if (isset($gatewayAddProductResp["ItemInternalId"]) && !empty($gatewayAddProductResp["ItemInternalId"])) {
                        $itemInternalId = isset($gatewayAddProductResp["ItemInternalId"]) ? $gatewayAddProductResp["ItemInternalId"] : "";
                        $itemResponse = [
                            'error' => false,
                            'status' => 'success',
                            'message' => __('Success, the product has been added at EBizCharge gateway.'),
                            ProductInterface::EC_ITEM_ID => $ebizItemId,
                            ProductInterface::EC_ITEM_INTERNALID => $itemInternalId,
                            ProductInterface::EBIZCHARGE_SOFTWARE_ID => $ebizSoftwareId,
                            ProductInterface::EBIZCHARGE_DIVISION_ID => $ebizDivisionId,
                            ProductInterface::EC_CREATED_IN => $ebizDivisionId
                        ];
                        $this->_ebizchargeLogger->addInfo(__("Product " . $productSku . " has been added successfully."));
                    }
                }

                if (!empty($itemInternalId)) {
                    $itemDetails["ItemInternalId"] = $itemInternalId;
                    $updateItemParams = [
                        'securityToken' => $soapApiFactory->getUeSecurityToken(),
                        'itemDetails' => $itemDetails
                    ];
                    /** @var  $addItemResponse */
                    $soapApiFactory->getClient()->UpdateItem($updateItemParams);
                    $this->_ebizchargeLogger->addInfo(__("Product " . $productSku . " has been updated successfully."));
                }

            }

        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during adding product to EBizCharge Exception:" . $soapFault->getMessage()
            ));
            // phpcs:disable
            $itemResponse['message'] = __('Exception occurred during upload item Error:' .
                $soapFault->getMessage());
        }

        // phpcs:enable
        return $itemResponse;
    }

    /**
     * Get EC Item Id
     *
     * @return mixed|null
     */
    public function getEcItemId()
    {
        return $this->getData(ProductInterface::EC_ITEM_ID);
    }

    /**
     * Get Ec Item Internal id
     *
     * @return mixed|null
     */
    public function getEcItemInternalId()
    {
        return $this->getData(ProductInterface::EC_ITEM_INTERNALID);
    }

    /**
     * Get Ec Item Last Sync Date
     *
     * @return mixed|null
     */
    public function getEcItemLastSyncDate()
    {
        return $this->getData(ProductInterface::EC_ITEM_LASTSYNCDATE);
    }

    /**
     * Get Ec Created In
     *
     * @return mixed|null
     */
    public function getEcCreatedIn()
    {
        return $this->getData(ProductInterface::EC_CREATED_IN);
    }

    /**
     * Get Product Image
     *
     * @param Product $product
     * @return mixed|string
     */
    public function getProductImage(Product $product)
    {
        $mediaGalleryImages = $product->getMediaGalleryImages();
        $productMainImage = '';
        if (count($mediaGalleryImages) > 0) {
            foreach ($mediaGalleryImages as $mediaGalleryImage) {
                $productMainImage = $mediaGalleryImage->getUrl();
            }
        }
        return $productMainImage;
    }

    /**
     * Get Price Including Tax
     *
     * @param Product $product
     * @return mixed
     */
    public function getPriceInclTax(Product $product)
    {
        return $this->_taxHelper->getTaxPrice($product, $product->getFinalPrice(), true);
    }

    /**
     * Get Category Names
     *
     * @param Product|null $product
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCategoryNames(Product $product = null): array
    {
        $categoryNames = [];
        $categoryIds = $product->getCategoryIds();
        if (count($categoryIds) > 0) {
            foreach ($categoryIds as $categoryId) {
                $category = $this->_categoryRepository->get($categoryId);
                //  $categoryNames[] = $category->getName();
                $categoryNames[] = $category->getId() . ":" . $category->getName();
            }
        }
        return $categoryNames;
    }

    /**
     * Prepare Custom Attributes
     *
     * @param Product|null $product
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareCustomAttributes(Product $product = null): array
    {
        $productCustomAttributesResp = [];

        if (!$product) {
            return $productCustomAttributesResp;
        }
        $productCustomAttributes = $product->getCustomAttributes();

        if (count($productCustomAttributes) > 0) {
            foreach ($productCustomAttributes as $productCustomAttribute) {
                $attributeValue = $productCustomAttribute->getValue();

                if ($productCustomAttribute->getAttributeCode() === 'category_ids') {
                    $categoryNames = $this->getCategoryNames($product);
                    /** @var  $attributeValue */
                    $attributeValue = implode(',', $categoryNames);
                }
                $productCustomAttributesResp[] = [
                    'FieldId' => $productCustomAttribute->getAttributeCode(),
                    'FieldCaption' => $productCustomAttribute->getAttributeCode(),
                    'FieldName' => $productCustomAttribute->getAttributeCode(),
                    'FieldValue' => $attributeValue,
                    'FieldType' => 'text',
                    'FieldDataType' => 'text',
                    'FieldDescription' => $attributeValue
                ];
            }
        }

        $ebizPriceList = $this->getSoapTierPrices($product);
        if ($ebizPriceList) {
            $productCustomAttributesResp[] = $ebizPriceList;
        }

        return $productCustomAttributesResp;
    }

    /**
     * Get Soap Request Tier Prices List
     *
     * @param Product $product
     * @return array
     */
    public function getSoapTierPrices(Product $product)
    {
        $ebizPriceList = [];
        $tierPrices = $product->getTierPrices();

        if (!$tierPrices) {
            return $ebizPriceList;
        }

        try {
            $priceValues = [];
            $tierPriceStorage = $this->_tierPriceStorage->get([$product->getSku()]);
            foreach ($tierPriceStorage as $tierPrice) {
                $priceValues[] = [
                    'GroupCode' => $tierPrice->getCustomerGroup(),
                    'Quantity' => $tierPrice->getQuantity(),
                    'DiscountType' => ucfirst($tierPrice->getPriceType()),
                    'Amount' => $tierPrice->getPrice(),
                    'Valid' => true,
                    'RowVersion' => '',
                    'Guid' => ''
                ];
            }

            $ebizPriceList = [
                'FieldId' => ProductInterface::EBIZCHARGE_PRODUCT_SOAP_NODE_PRICE_LIST,
                'FieldCaption' => ProductInterface::EBIZCHARGE_PRODUCT_SOAP_NODE_PRICE_LIST,
                'FieldName' => ProductInterface::EBIZCHARGE_PRODUCT_SOAP_NODE_PRICE_LIST,
                'FieldValue' => json_encode($priceValues),
                'FieldType' => 'string',
                'FieldDataType' => 'string',
                'FieldDescription' => ProductInterface::EBIZCHARGE_PRODUCT_SOAP_NODE_PRICE_LIST
            ];
        } catch (\Exception $exception) {
            $this->_ebizchargeLogger->addError($exception->getMessage());
        }

        return $ebizPriceList;
    }

    /**
     * Load Product from EBizCharge By Ebiz Item Id
     *
     * @param $ebizItemId
     * @param $ebizInternalId
     * @return array|false|mixed
     * @throws NoSuchEntityException
     */
    public function loadProductFromEbizChargeByEbizItemId($ebizItemId, $ebizInternalId = '')
    {
        $ebizHubProduct = [];
        if (!$ebizItemId) {
            return $ebizHubProduct;
        }
        /** @var $ebizItemId */
        $ebizItemId = trim(str_replace(" ", "", $ebizItemId));

        /** @var $itemInternalId */
        $itemInternalId = trim($ebizInternalId) !== '' ? trim(str_replace(" ", "", $ebizInternalId)) : '';

        /** fetching products */
        try {
            /** @var $params */
            $params = [
                'securityToken' => $this->tranApiFactory->create()->getUeSecurityToken(),
                'itemId' => $ebizItemId,
                'itemInternalId' => $itemInternalId,
                'filters' => [
                    'SearchFilter' => []
                ],
                'start' => 0,
                'limit' => 1
            ];

            /** @var $ebizProductsObj */
            $ebizProductsObj = $this->tranApiFactory->create()->getClient()->SearchItems($params);

            $ebizProducts = [];

            /** if we have Products at Ebizcharge */
            if ($ebizProductsObj && $ebizProductsObj->SearchItemsResult) {
                $ebizItems = (array)($ebizProductsObj->SearchItemsResult ?? []);
                if (count($ebizItems) > 0) {
                    foreach ($ebizItems as $ebizItem) {
                        $ebizProducts[] = (array)$ebizItem;
                    }
                    $ebizHubProduct = $ebizProducts[0] ?? [];
                } else {
                    $this->_ebizchargeLogger->addError(__(
                        'Error occurred during fetching items from EBizhCarge Gateway Item id: ' . $ebizItemId
                    ));
                }
            }

        } catch (SoapFault $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during fetching products Soap Error: " . $exception->getMessage()
            ));

        }
        return $ebizHubProduct;
    }

    /**
     * @param string $itemInternalId
     * @param string $itemId
     * @param array $params
     * @param string $eq_or_neq
     * @param int $startPosition
     * @param int $limit
     * @param string|null $sort
     * @return array
     * @throws NoSuchEntityException
     */
    public function getItemsFromEbizcharge(
        string $itemInternalId = '',
        string $itemId = '',
        array  $params = [],
        string $eq_or_neq = 'equal',
        int    $startPosition = 0,
        int    $limit = SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT,
        string $sort = null
    ): array
    {

        $itemsCollection = [];
        $securityToken = $this->tranApiFactory->create()->getUeSecurityToken();
        $start = $startPosition;
        $maxSize = 0;
        $storeId = $this->configFactory->create()->getStoreId();
        $divisionId = $this->configFactory->create()->getDivisionID($storeId);

        $searchFilters = [
            "SearchFilter" => [
                "FieldName" => "DivisionId",
                "ComparisonOperator" => "eq",
                "FieldValue" => $divisionId
            ]
        ];

        try {
            // $limit = 2;
            /**
             * Searching do while
             */
            do {
                /** @var  $searchItemsParams */
                $searchItemsParams = [
                    'securityToken' => $securityToken,
                    'filters' => $searchFilters,
                    'start' => $start,
                    'limit' => $limit,
                    'sort' => $sort
                ];

                /**
                 * Sending request Ebizcharge get the latest customers
                 * @Ebizcharge SOAP Api Gateway
                 */
                /** @var  $ebizchargeCustomers */
                $searchItemsResponse = $this->tranApiFactory->create()->getClient()->SearchItems($searchItemsParams);
                //  dump($searchItemsResponse->SearchItemsResult);exit;

                /** fetching customer results */
                if (!isset($searchItemsResponse->SearchItemsResult)) {
                    $itemsCollection = [];
                    $resultCount = 0;

                } elseif (isset($searchItemsResponse->SearchItemsResult->ItemDetails) && !is_object($searchItemsResponse->SearchItemsResult->ItemDetails)
                    && (count((array)$searchItemsResponse->SearchItemsResult->ItemDetails)) > 1) {

                    $ebizItems = $searchItemsResponse->SearchItemsResult->ItemDetails;
                    $resultCount = count($searchItemsResponse->SearchItemsResult->ItemDetails);
                    $itemsCollection = [...$itemsCollection, ...$ebizItems];

                } else {
                    $ebizItem[] = isset($searchItemsResponse->SearchItemsResult->ItemDetails) ? $searchItemsResponse->SearchItemsResult->ItemDetails : [];
                    $itemsCollection = [...$itemsCollection, ...$ebizItems];
                    $resultCount = 1;
                }

                /** result count */
                if ($resultCount < $limit) {
                    $maxSize = 1;
                }
                $start += $limit;
                //  var_dump("start=" . $start, "maxsize=" . $maxSize, "limit=" . $limit);

            } while ($maxSize === 0);

        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during fetching items from EBizCharge Hub. Error:" .
                $exception->getMessage()));
            // var_dump($exception->getMessage());exit;
        }
        // var_dump("<pre>", $searchItemsParams, $itemsCollection, count($itemsCollection)); exit;

        return $itemsCollection;
    }

    /**
     * Get Items From EBizCharge
     *
     * @param string $itemInternalId
     * @param string $itemId
     * @param array $params
     * @param string $eq_or_neq
     * @param int $startPosition
     * @param int $limit
     * @param string|null $sort
     * @return array
     * @throws NoSuchEntityException
     */
    // phpcs:disable

    /**
     * Get Ebizcharge Division Id
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getEbizDivisionId()
    {
        return $this->tranApiFactory->create()->getDivisionId();
    }
    // phpcs:enable

    /**
     * Get Items From EBizCharge Payment Hub
     *
     * @param string $itemInternalId
     * @param string $itemId
     * @param array $params
     * @param string $eq_or_neq
     * @param int $startPosition
     * @param int $limit
     * @return array
     * @throws NoSuchEntityException
     */
    public function getItemsFromEbizchargeDeprecated(
        string $itemInternalId = '',
        string $itemId = '',
        array  $params = [],
        string $eq_or_neq = 'equal',
        int    $startPosition = 0,
        int    $limit = SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT
    ): array
    {
        /** @var $productType */
        $productType = isset($params['product_type']) ? $params['product_type'] : 'inventory';

        /**
         * Search Items from Ebizcharge Gateway
         */
        $maxSize = 0;
        $start = $startPosition !== 0 ? $startPosition : 0;
        $limit = $limit ? $limit : SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT;

        /** @var  $ebizchargeItems */
        $ebizchargeItems = [];

        if ($this->tranApiFactory->create()
            ->getClient()) {

            try {
                $limit = 2;
                do {
                    /** @var $searchItemsParams */
                    $searchItemsParams = [
                        'securityToken' => $this->tranApiFactory->create()->getUeSecurityToken(),
                        'itemInternalId' => $itemInternalId,
                        'itemId' => $itemId,
                        'start' => $start,
                        'limit' => $limit,
                        'sort' => 'ItemId',
                        'filters' => []
                    ];

                    /*
                    $searchItemsParams['filters'] = [
                        'SearchFilter' =>
                        [
                            'FieldName' => 'SoftwareId',
                            'ComparisonOperator' => $eq_or_neq,
                            'FieldValue' => $this->getEbizSoftwareId()
                        ]
                    ];
                    */

                    /** @var $searchItemsResponse */
                    $searchItemsResponse = $this->tranApiFactory->create()
                        ->getClient()
                        ->SearchItems($searchItemsParams);
                    //   var_dump($searchItemsResponse->SearchItemsResult->ItemDetails);exit;

                    /** fetching customer results */
                    if (!isset($searchItemsResponse->SearchItemsResult->ItemDetails)) {
                        $ebizchargeItems = [];
                        $resultCount = 0;

                    } elseif (is_array((array)$searchItemsResponse->SearchItemsResult->ItemDetails)
                        && (count((array)$searchItemsResponse->SearchItemsResult->ItemDetails)) > 1) {

                        $ebizItem = (array)$searchItemsResponse->SearchItemsResult->ItemDetails;
                        $resultCount = count((array)$searchItemsResponse->SearchItemsResult->ItemDetails);
                        $ebizchargeItems = array_merge($ebizchargeItems, $ebizItem);

                    } else {
                        $ebizchargeItems = (array)$searchItemsResponse->SearchItemsResult->ItemDetails;
                        $resultCount = 1;

                    }
                    /** result count */
                    if ((int)$resultCount < (int)$limit) {
                        $maxSize = 1;
                    }
                    $start = $start + $limit;

                } while ($maxSize === 0);

                /** sending logs to logger */
                $this->_ebizchargeLogger->addInfo(
                    __('Success, found items at EBizCharge Items(' . count($ebizchargeItems) . ')'),
                    $searchItemsParams
                );

            } catch (SoapFault $soapFault) {
                $this->_ebizchargeLogger->addCritical(__(
                    'Soap fault occurred during fetching items from EBizCharge Soap Error: ' .
                    $soapFault->getMessage()
                ));

            }
        } else {
            $this->_ebizchargeLogger->addCritical(__('Soap client issue occurred: '));
        }

        return $ebizchargeItems;
    }

    /**
     * Get Ebiz Software Id
     *
     * @return string
     */
    public function getEbizSoftwareId()
    {
        return $this->tranApiFactory->create()->getSoftwareId();
    }

    /**
     * Get Image Url by Product
     *
     * @param Product $product
     * @return string
     * @throws NoSuchEntityException
     */
    public function getImageUrlByProduct($product)
    {
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl . '/catalog/product/' . $product->getImage();
    }

    /**
     * Get EBizCharge Product Collection
     *
     * @return array|bool
     */
    public function getEbizProductCollection()
    {
        /** feching the Ebizcharge items */
        try {
            $storeId = $this->_storeManager->getStore();
            return $this->_syncAssetsFactory->create()->getLatestEbizchargeItems();
        } catch (\Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during fetching items from EBizCharge Hub. Exception: " . $exception->getMessage()
            ));
            return false;
        }
    }

    /**
     * Update Ebizcharge Product to Magento
     *
     * @param mixed $ebizInternalItemId
     * @param array $productParams
     * @return bool
     * @throws \Exception
     */
    public function updateEbizchargeProductToMagento($ebizInternalItemId, $productParams = [])
    {
        /** Ebizcharge Internal Item Id */
        return $this->createEbizchargeProductToMagento($productParams, $ebizInternalItemId);
    }

    /**
     * @param $productParams
     * @param $ebizInternalItemId
     * @param $isDownload
     * @return float
     * @throws \Exception
     */
    public function createEbizchargeProductToMagento(
        $productParams = [],
        $ebizInternalItemId = '',
        $isDownload = false
    )
    {

        /** @var  $isProductSaved */
        $productId = 0;
        $productName = isset($productParams['Name']) && !empty($productParams['Name']) ? $productParams['Name'] : ProductInterface::EBIZCHARGE_PRODUCT_DEFAULT_NAME;
        $productSku = isset($productParams['SKU']) && !empty($productParams['Name']) ? $productParams['SKU'] : ProductInterface::EBIZCHARGE_PRODUCT_DEFAULT_NAME;
        $productUrl = str_replace([" ", "/"], ["-", "-"], $productSku);

        try {
            $configFactory = $this->configFactory->create();

            $storeId = $configFactory->getStoreId();
            $envPrefix = $configFactory->getEnvoirnmentPrefix($storeId);

            /** create new Model of Product */
            $ebizchargeItemId = isset($productParams['ItemId']) ? $productParams['ItemId'] : "";
            $ebizchargeItemInternalId = $productParams['ItemInternalId'] ?? "";
            $productName = $productParams['Name'] ?? ProductInterface::EBIZCHARGE_PRODUCT_DEFAULT_NAME;
            $productSku = isset($productParams['SKU']) ? $productParams['SKU'] : $this->prepareSkuFromEbizchargeItem($productName);

            $productDescription = $productParams['Description'] ?? ProductInterface::EBIZCHARGE_PRODUCT_DEFAULT_DESCRIPTION;
            $productPrice = $productParams['UnitPrice'] ?? ProductInterface::EBIZCHARGE_PRODUCT_DEFAULT_PRICE;
            $productCost = $productParams['UnitCost'] ?? ProductInterface::EBIZCHARGE_PRODUCT_DEFAULT_PRICE;
            $productWeight = isset($productParams['weight']) ? $productParams['weight'] : 0;
            $isProductTaxable = $productParams['Taxable'] ?? ProductInterface::EBIZCHARGE_PRODUCT_DEFAULT_TAXABLE;
            $productTaxRate = isset($productParams['TaxRate']) ? $productParams['TaxRate'] : 0;
            $productUnitOfMeasure = $productParams['UnitOfMeasure'] ??
                ProductInterface::EBIZCHARGE_PRODUCT_DEFAULT_UNIT_OF_MEASUREMEANT;
            $productGrossPrice = isset($productParams['UnitPrice']) ? $productParams['UnitPrice'] : 0;
            $productClass = isset($productParams['Taxable']) ? $productParams['Taxable'] : '';
            $productType = isset($productParams['ItemType']) ? $productParams['ItemType'] : TyPE::TYPE_SIMPLE;
            $productQty = isset($productParams['QtyOnHand']) ? $productParams['QtyOnHand'] : 0;
            $productActive = isset($productParams['Active']) ? $productParams['Active'] : 0;
            $productTaxable = isset($productParams['Taxable']) ? $productParams['Taxable'] : 0;
            $productCreatedIn = isset($productParams['SoftwareId']) ? $productParams['SoftwareId'] : '';
            $imageUrl = isset($productParams['ImageUrl']) ? $productParams['ImageUrl'] : '';
            $itemLastSyncDateTime = $productParams['ItemLastSyncDateTime'] ??
                $this->_syncAssetsFactory->create()->getCurrentDateTime();

            $divisionPrefix = $this->configFactory->create()->getEnvoirnmentPrefix($storeId);
            $ebizDivisionId = isset($productParams['DivisionId']) && !empty($productParams['DivisionId']) ? $productParams['DivisionId'] : $this->configFactory->create()->getDivisionID($storeId);
            $ebizSoftwareId = isset($productParams['SoftwareId']) && !empty($productParams['SoftwareId']) ? $productParams['SoftwareId'] : $this->tranApiFactory->create()->getSoftwareId();
            /** @var $websiteId */
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();

            /** @var  $product */
            $product = $this;

            $this->sessionManagerInterface->unsIsDownload();
            $this->sessionManagerInterface->setIsDownload($isDownload);

            /** if ebizcharge Internal Item Id */
            if ($ebizchargeItemId !== "") {
                $product = $this->loadByEbizItemId($ebizchargeItemId);
            }

            /** @var  $product */
            $urlKey = $productUrl . "-" . (string)rand(9999, 99999999);
            if ($product->getId()) {
                $urlKey = $product->getUrlKey();
            }
            $product->setSku($productSku);
            $product->setName($productName); // set your Product Name of Product
            $product->setDescription($productDescription);
            $product->setShortDescription($productDescription);
            $product->setTypeId($productType);
            $product->setAttributeSetId(4); // Attribute set id
            $product->setStatus($productActive); // Status on product enabled/ disabled 1/0

            // visibilty of product (catalog / search / catalog, search / Not visible individually)
            $product->setVisibility(Visibility::VISIBILITY_BOTH);
            $product->setPrice($productPrice); // price of product

            $product->setEcItemInternalId($ebizchargeItemInternalId);
            $product->setEcItemId($ebizchargeItemId);
            $product->setEcItemLastSyncDate($itemLastSyncDateTime);
            $product->setEcItemSyncStatus(true);
            $product->setEcCreatedIn($productCreatedIn);
            $product->setUrlKey($urlKey);

            $product->setAttributeSetId(ProductInterface::DEFAULT_ATTRIBUTE_SET_ID); // set attribute id
            $product->setStatus(Status::STATUS_ENABLED); // status enabled/disabled 1/0
            $product->setWeight($productWeight); // set weight of product
            $product->setVisibility(Visibility::VISIBILITY_BOTH); // visibility of product
            $product->setWebsiteIds([$websiteId]);
            $product->setTaxClassId($isProductTaxable); // Tax class ID
            $product->setTypeId(Type::TYPE_SIMPLE);
            $product->setStoreId($this->getStoreId());
            $product->setDivisionId($ebizDivisionId);
            $product->setSoftwareId($ebizSoftwareId);
            $product->setIsDownlaod($isDownload);

            if ($productQty < 1) {
                // phpcs:ignore
                $productQty = $productQty + 1;
            }

            /** saving product to current */
            $product->save();

            /** Assigning Root Category Id */
            /** @var $rootCategoryId */
            $rootCategoryId = $this->getStore()->getRootCategoryId();
            $mediaProductFile = $this->getDefaultImagePlaceHolder("image");

            /** assign Images to the Products */
            $imagePath = $imageUrl; // path of the image

            /** In case if Image path is null */
            if (!empty($imagePath)) {
                // phpcs:ignore
                $fileName = basename($imagePath);
                /** @var $productImageStream */
                // phpcs:ignore
                $productImageStream = @file_get_contents($imagePath) ?? false;

                if ($productImageStream) {
                    /** @var  $mediaDir */
                    $mediaDir = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                    $mediaPath = $mediaDir->getAbsolutePath('product');

                    /** image path */
                    if ($productImageStream) {
                        $mediaProductFile = $mediaPath . $fileName;
                        // phpcs:ignore
                        file_put_contents($mediaProductFile, $productImageStream);
                        /** Product Media Path */
                        $product->addImageToMediaGallery(
                            $mediaProductFile,
                            ['image', 'small_image', 'thumbnail'],
                            false,
                            false
                        );
                    }
                }
            }

            // ** Start ** To save PriceList (Tier Price) if exist
            $ebizCustomFields = [];

            if (isset($productParams['ItemCustomFields'])) {
                $itemCustomFields = $productParams['ItemCustomFields'];
                if (is_object($itemCustomFields)) {
                    $productCustomField = (array)$itemCustomFields->EbizCustomField;
                    $ebizCustomFields = array_merge($ebizCustomFields, $productCustomField);
                } else {
                    $ebizCustomFields = isset($itemCustomFields["ItemCustomField"]) ?
                        (array)$itemCustomFields["ItemCustomField"] : [];

                }
            }
            $categoryIds = "";

            if (count($ebizCustomFields) > 0) {
                foreach ($ebizCustomFields as $customField) {
                    if (!$customField) {
                        continue;
                    }
                    if (isset($customField->FieldId) && $customField->FieldId !==
                        ProductInterface::EBIZCHARGE_PRODUCT_SOAP_NODE_PRICE_LIST) {
                        continue;
                    }

                    $priceList = json_decode($customField->FieldValue);

                    foreach ($priceList as $groupPrice) {
                        // Get Customer Group Id by Customer Group Code
                        $groupCode = $groupPrice->GroupCode;
                        $customerGroup = $this->_customerGroupCollectionFactory->create()
                            ->addFieldToFilter('customer_group_code', $groupCode)
                            ->getFirstItem();
                        $groupId = $customerGroup->getData('customer_group_id');

                        // Create & Set Tier Price Data
                        $tierPriceData = $this->_productTierPriceInterfaceFactory->create();
                        $tierPriceData->setCustomerGroupId($groupId)
                            ->setQty((float)$groupPrice->Quantity)
                            ->setValue((float)$groupPrice->Amount);

                        // To set Price Type value (e.g Fixed, Discount etc)
                        $extensionAttributes = $tierPriceData->getExtensionAttributes();

                        if ($extensionAttributes) {
                            $extensionAttributes->setPercentageValue($groupPrice->DiscountType);
                            $tierPriceData->setExtensionAttributes($extensionAttributes);
                            // Add Tier Price to the product
                            $tierPrice = $this->_scopedProductTierPriceManagement->add($productSku, $tierPriceData);
                        }
                    }

                }
                // ** End ** To save PriceList (Tier Price) if exist
                foreach ($ebizCustomFields as $customField) {
                    if ($customField->FieldId === "category_ids") {
                        $categoryIds = $customField->FieldValue ?? "";
                    }
                }
            }

            $ebizCategoryIds = [];
            $ebizCategoryNames = [];

            if ($categoryIds !== "") {
                $categories = explode(",", $categoryIds);
                if (count($categories) > 0) {
                    foreach ($categories as $categoryId) {
                        $category = explode(":", $categoryId);
                        $ebizCategoryIds[] = isset($category[0]) ? $category[0] : "";
                        $ebizCategoryNames[] = isset($category[1]) ? $category[1] : "";
                    }
                }
            }

            /** @var  $categoryIds */
            $categoryIds = $rootCategoryId;
            if (count($ebizCategoryIds) > 0) {
                $categoryIds = implode(",", $ebizCategoryIds);
            }
            /** @var  $categoryIds */
            $categoryIds = [$categoryIds];

            // assign your product to category using Category Id
            $category = $this->_categoryLinkManagement->assignProductToCategories($productSku, $categoryIds);
            /** saving Product Saleable Quantity */
            // $this->setSaleableQty($productSku, $productQty);
            $this->_ebizchargeLogger->addInfo(__(
                "Success, the Product $productName has been added locally successfully"
            ));

            /** Set Stock Data */
            /*
            $this->setSalableStockQty(
                $productSku,
                [
                    'use_config_manage_stock' => ProductInterface::DEFAULT_MANAGE_CONFIG_STOCK,
                    'manage_stock' => ProductInterface::DEFAULT_MANAGE_STOCK,
                    'is_salable' => 1,
                    'is_in_stock' => ProductInterface::DEFAULT_IS_PRODUCT_IN_STOCK,
                    'qty' => (double)$productQty
                ]
            );

            /** Make available Product inventory for placing order  */
            // $this->makeAvailableInventoryforOrder($product);

            /*
            $sourceStock = [
                'source' => "default",
                'status' => 1,
                'quantity' => (double)$productQty
            ];
           // $this->setProductSourceStock($productSku, $sourceStock);

           // $indexerId = "cataloginventory_stock";
            /**
             * running indexer
             */
            //  $this->runIndexer($indexerId);
           // $this->runIndexer($indexerId);

            $indexerId = "inventory";
            // $this->runIndexer($indexerId);

            $productId = $product->getId();

        } catch (LocalizedException $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during adding product to local database. Error: " . $exception->getMessage()
            ));
            // phpcs:ignore
            printf("Error: Product: [" . $productName . "] " . $exception->getMessage() . $exception->getLine() . "\n");

        }
        $this->sessionManagerInterface->unsIsDownload();

        return  (float)$productId;
    }

    /**
     * Prepare Sku from Ebizcharge Item
     *
     * @param string $productSku
     * @return string
     */
    public function prepareSkuFromEbizchargeItem($productSku = ''): string
    {
        if (!$productSku) {
            return '';
        }
        $productSku = trim(strtolower($productSku));
        $productSku = str_replace(' ', '-', ($productSku));
        $productSku = preg_replace("/\s+/", "", trim($productSku));
        $productSku = preg_replace('/[^a-zA-Z0-9_ -]/s', '', $productSku);
        /** @var  $product */
        $product = $this->loadByAttribute('sku', $productSku);

        /** Product SKU */
        if ($product && $product->getId()) {
            // phpcs:ignore
            $productSku = $productSku . '-' . mt_rand(100000, 999999);
        }

        return $productSku;
    }

    /**
     * Set Ec Item Internal Id
     *
     * @param mixed $ecItemInternalId
     * @return Product
     */
    public function setEcItemInternalId($ecItemInternalId)
    {
        return $this->setData(ProductInterface::EC_ITEM_INTERNALID, $ecItemInternalId);
    }

    /**
     * Set EC Item Id
     *
     * @param mixed $ecItemId
     * @return void
     */
    public function setEcItemId($ecItemId)
    {
        $this->setData(ProductInterface::EC_ITEM_ID, $ecItemId);
    }

    /**
     * Set Ec Item Last Sync Date
     *
     * @param mixed $ecItemLastSyncDate
     * @return Product
     */
    public function setEcItemLastSyncDate($ecItemLastSyncDate)
    {
        return $this->setData(ProductInterface::EC_ITEM_LASTSYNCDATE, $ecItemLastSyncDate);
    }

    /**
     * Set Item Sync Status
     *
     * @param mixed $ecItemSyncStatus
     * @return Product
     */
    public function setEcItemSyncStatus($ecItemSyncStatus)
    {
        return $this->setData(ProductInterface::EC_ITEM_SYNC_STATUS, $ecItemSyncStatus);
    }

    /**
     * Set Ec Created In
     *
     * @param mixed $ecCreatedIn
     * @return void
     */
    public function setEcCreatedIn($ecCreatedIn)
    {
        $this->setData(ProductInterface::EC_CREATED_IN, $ecCreatedIn);
    }

    /**
     * Set Division Id
     *
     * @param mixed $ecDivisionId
     * @return ProductInterface
     */
    public function setDivisionId($ecDivisionId): ProductInterface
    {
        return $this->setData(ProductInterface::EBIZCHARGE_DIVISION_ID, $ecDivisionId);
    }

    /**
     * Set Software Id
     *
     * @param mixed $ecSoftwareId
     * @return ProductInterface
     */
    public function setSoftwareId($ecSoftwareId): ProductInterface
    {
        return $this->setData(ProductInterface::EBIZCHARGE_SOFTWARE_ID, $ecSoftwareId);
    }

    /**
     * Get Default Image Place Holder
     *
     * @param string $image
     * @return string
     */
    public function getDefaultImagePlaceHolder(string $image = ""): string
    {
        if ($image !== "") {
            return $this->_productImage->getDefaultPlaceholderUrl("image");
        }
        return $this->_productImage->getDefaultPlaceholderUrl("small_image");
    }

    /**
     * Load By Ebizcharge Internal Id
     *
     * @param string $ebizItemId
     * @return Product
     */
    public function loadByEbizItemId($ebizItemId = '')
    {
        $productId = $this->productResourceModel->getIdByEbizItemId($ebizItemId);
        return $this->load($productId);
    }

    /**
     * Sync Items Stock To EbizCharge
     *
     * @return array
     * @throws \Exception
     */
    public function syncItemsStockToEbizCharge()
    {
        $itemStocksResponse = [
            "error" => true,
            "status" => false
        ];

        try {
            $productsCollection = $this->getCollection()
                ->addFieldToFilter("status", true);
            if (count($productsCollection) > 0) {
                foreach ($productsCollection as $product) {
                    $productId = $product->getId();
                    $product = $this->load($productId);
                    if ($product->getId()) {
                        // phpcs:ignore
                        echo "\r" . "\n" . " Syncing item stock to EBizCharge Product Id: " . $productId;
                        $syncdItems = $this->syncItemToEbizcharge($product);
                    }
                }
            }
        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during syncing items stock. Exception: " . $exception->getMessage()
            ));
        }
        // phpcs:ignore
        echo "\r" . "\n";
        return $itemStocksResponse;
    }

    /**
     * Sync Item to EBizCharge
     *
     * @param null|mixed $productItem
     * @return bool|AbstractModel
     * @throws \Exception
     */
    public function syncItemToEbizcharge($productItem = null)
    {
        $product = $this->loadByAttribute('entity_id', $productItem->getEntityId());
        return $product->save();
    }

    /**
     * Run Indexer
     *
     * @param string $indexerId
     * @return void
     * @throws \Exception
     */
    public function runIndexer($indexerId = "")
    {
        $indexerCollection = $this->indexCollection->create();
        $indexerIds = $indexerCollection->getAllIds();

        if ($indexerId !== "") {
            $indexidarray = $this->indexFactory->create()->load($indexerId);
            // echo "reindexing the indexerId: ".$indexerId;
            $indexidarray->reindexAll($indexerId);
        } else {
            foreach ($indexerIds as $indexerId) {
                $indexidarray = $this->indexFactory->create()->load($indexerId);
                // echo "reindexing the indexerId: ".$indexerId;
                $indexidarray->reindexAll($indexerId);
            }
        }
    }

    /**
     * Is Product Need To be Updated
     *
     * @param mixed $startDateModified
     * @param mixed $endDateModified
     * @return bool
     * @throws Exception
     */
    public function isProductNeedTobeUpdated($startDateModified = null, $endDateModified = null)
    {
        //var_dump($startDateModified, $endDateModified);
        $isNeedTobeUpdated = false;
        $startDateTime = new DateTime($startDateModified);
        $endDateTime = new DateTime($endDateModified);
        $diffDateTime = $startDateTime->diff($endDateTime);

        $diffDays = $diffDateTime->days;
        $diffHours = $diffDateTime->h;
        $diffMinutes = $diffDateTime->i;

        if ((int)$diffDays > 0 || (int)$diffHours > 0 || (int)$diffMinutes) {
            $isNeedTobeUpdated = true;
        }

        return $isNeedTobeUpdated;
    }

    /**
     * Load By Ebizcharge Internal Id
     *
     * @param string $ebizItemInternalId
     * @return Product
     */
    public function loadByEbizInternalId($ebizItemInternalId = '')
    {
        $productId = $this->productResourceModel->getIdByEbizInternalId($ebizItemInternalId);
        return $this->load($productId);
    }

    /**
     * Get Ebiz Internal Id
     *
     * @return mixed|null
     */
    public function getEbizInternalId()
    {
        return $this->getData(ProductInterface::EC_ITEM_INTERNALID);
    }

    /**
     * Set Saleable Qty
     *
     * @param mixed $productSku
     * @param mixed $productQty
     * @return bool|SourceItemInterface
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws ValidationException
     */
    public function setSaleableQty($productSku, $productQty)
    {
        try {
            $productQty = (int)$productQty;
            $_sourceItemsSaveInterface = $this->_sourceItemsSave;
            $sourceItem = $this->_sourceItemInterfaceFactory->create();
            $sourceItem->setSourceCode('default'); // default : stock source
            $sourceItem->setSku($productSku);
            $sourceItem->setQuantity($productQty);
            $sourceItem->setStatus(ProductInterface::DEFAULT_IS_PRODUCT_IN_STOCK);

            $this->_ebizchargeLogger->addInfo(__("Saving the Saleable Quantity of the product"));
            $_sourceItemsSaveInterface->execute([$sourceItem]);

            /** setting stock */
            $stockItem = $this->_stockRegistry->getStockItemBySku($productSku);
            $stockItem->setQty($productQty);
            $stockItem->setIsInStock((ProductInterface::DEFAULT_IS_PRODUCT_IN_STOCK));
            $this->_stockRegistry->updateStockItemBySku($productSku, $stockItem);

            return $sourceItem;
        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception Occurred during saving the Saleable Quantity of product Error: " .
                $exception->getMessage()
            ));
            return false;
        }
    }

    /**
     * Is Product exists Locally
     *
     * @param string $ebizProductInternalId
     * @return bool|Product
     */
    public function isProductExistsLocal($ebizProductInternalId = '')
    {
        /** @var $productId */
        $productId = $this->productResourceModel->getIdByEbizInternalId($ebizProductInternalId);
        /** if found product id  */
        if ($productId) {
            return $this->load($productId);
        }
        return false;
    }

    /**
     * Check if product Exists
     *
     * @param array $productParams
     * @return bool|AbstractModel
     */
    public function isProductExists($productParams = [])
    {
        /**
         * collecting data from Product Params
         */
        $productName = $productParams['name'] ?? ProductInterface::EBIZCHARGE_PRODUCT_DEFAULT_NAME;
        $itemSku = $this->prepareSkuFromEbizchargeItem($productName);

        /** @var load product via Sku $product */
        $product = $this->loadByAttribute('sku', $itemSku);

        /** if product is already exists */
        if ($product) {
            $this->_ebizchargeLogger->addInfo(__("Product already exists in local Magento"));
            return $product;
        }
        $this->_ebizchargeLogger->addError(__("Product does not exist to local system"));

        return false;
    }

    /**
     * Get Stock for Website
     *
     * @param string $websiteCode
     * @return int|null
     */
    public function getStockIdForWebsite(string $websiteCode): ?int
    {
        /** @var $result */
        $result = $this->productResourceModel->getStockIdForWebsite($websiteCode);
        /** if results */
        if (count($result) === 0) {
            return 0;
        }
        return (int)reset($result);
    }

    /**
     * Render Product Search listings
     *
     * @param array $searchParams
     * @return string
     */
    public function renderProductSearchListing($searchParams = [])
    {
        $htmlOutput = '<ul id="suggestion-list-rows">';
        try {
            /** @var $productListItems */
            $productListItems = $this->getProductListings($searchParams);

            /** Product List Items */
            if (count($productListItems) > 0) {
                foreach ($productListItems as $productListItem) {
                    $productId = $productListItem->getId();
                    $product = $this->load($productId);

                    $productName = $productListItem->getName();
                    $productType = $product->getTypeId();

                    $productSku = $productListItem->getSku();
                    $productPrice = $productListItem->getFinalPrice();
                    $productPrice = $this->getPriceAfterApplyRules($product, $productPrice);

                    $price = $this->_priceHelper->currency($productPrice, true, false);
                    //  $productTitle = $productName . ' [' . $productSku . '] - (' . $price . ')';
                    $productTitle = $productName . ' (' . $price . ')';

                    $htmlOutput .= '<li data-key="' . $productTitle . '" data-value="' . $productId . '" id="' .
                        $productId . '">' . $productTitle . '</li>';
                }
            } else {
                $htmlOutput .= '<li>Not Found</li>';
            }
            $htmlOutput .= '</ul>';
        } catch (\Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception Occurred during fetching Product: " . $exception->getMessage()
            ));
            $htmlOutput = '<ul id="suggestion-list-rows">';
            $htmlOutput .= '<li>Not Found</li>';
            $htmlOutput .= '</ul>';
            return $htmlOutput;
        }

        return $htmlOutput;
    }

    /**
     * Get Product Listings
     *
     * @param array $searchParams
     * @return Collection|AbstractDb
     */
    public function getProductListings($searchParams = [])
    {
        /** @var  $keywords */
        $keywords = isset($searchParams['keywords']) ? $searchParams['keywords'] : '';
        /** @var  $limit */
        $limit = isset($searchParams['limit']) ? $searchParams['limit'] : 0;

        $productListItems = $this->_productCollectionFactory
            ->create()
            ->addFieldToSelect('*')
            ->addAttributeToFilter('type_id', ['eq' => Type::TYPE_SIMPLE])
            ->addFieldToFilter('visibility', Visibility::VISIBILITY_BOTH)
            ->addFieldToFilter('status', Status::STATUS_ENABLED)
            ->addFieldToFilter(
                [
                    ['attribute' => 'name', 'like' => '%' . $keywords . '%'],
                    ['attribute' => 'sku', 'like' => '%' . $keywords . '%'],
                    ['attribute' => 'entity_id', 'like' => '%' . $keywords . '%']
                ]
            )
            ->setOrder('entity_id', "asc");

        /**
         * setting limits
         */
        if ($limit > 0) {
            $productListItems->setPageSize($limit);
        }

        return $productListItems;
    }

    /**
     * Get Price After Apply Rules
     *
     * @param null|mixed $product
     * @param int $productPrice
     * @return int|mixed|null
     */
    public function getPriceAfterApplyRules($product = null, $productPrice = 0)
    {
        $productFinalPrice = $productPrice ? $productPrice : 0;
        if ($product) {
            $productFinalPrice = $this->_priceRuleModifier->modifyPrice($productPrice, $product);
        }
        return $productFinalPrice;
    }

    /**
     * Get Local Products
     *
     * @param int $status
     * @param int $limit
     * @return Collection|AbstractDb
     */
    public function getProductCollection($status = 1, $limit = 10)
    {
        /** @var $productCollection */
        $productCollection = $this->_productCollectionFactory
            ->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('status', 1);

        return $productCollection;
    }

    /**
     * Prepare Stock Items for Low Stock Email
     *
     * @param string $appState
     * @return array
     * @phpcs:disable
     */
    public function prepareStockItemsForLowStockEmail($appState = ''): array
    {
        /** @var $checkStockItemResp */
        $checkStockItemResp = [
            'error' => true,
            'low_stock_status' => false,
            'message' => __(' '),
            'success' => [],
            'failure' => []
        ];
        /** @var  $successNotifications */
        $successNotifications = [];
        /** @var $failureNotifications */
        $failureNotifications = [];

        try {
            /** @var  $recurringOrderItems */
            $recurringOrderItems = $this->_recurringFactory->create()
                ->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter(
                    Recurring::REC_STATUS,
                    Recurring::EBIZCHARGE_RECURRING_STATUS_ACTIVE
                );
            $this->_ebizchargeLogger->addInfo(__('Total Active Items are: ' . count($recurringOrderItems)));

            if (count($recurringOrderItems) > 0) {
                foreach ($recurringOrderItems as $recurringOrderItem) {
                    /** @var $productId */
                    $productId = $recurringOrderItem->getMageItemId();
                    $product = $this->load($productId);
                    $currentState = $this->_appState->getAreaCode();
                    $this->_ebizchargeLogger->addInfo(__('Check stock for Item ' . $productId));

                    if ($appState == 'cli') {
                        print_r("\n" . 'checking stock for ' . $product->getSku());
                    }

                    if (!$product) {
                        $this->_ebizchargeLogger->addError(__('Error occurred, as this ' . $productId .
                            ' product doest not exists'));
                        $failureNotifications[] = 'Item:' . $productId . ' ';
                        continue;
                    }
                    /** @var  $qtyOrdered */
                    $qtyOrdered = $recurringOrderItem->getQtyOrdered();
                    $mageProductType = $product->getTypeId();
                    $this->_ebizchargeLogger->addInfo(__('found Item: ' . $product->getSku()));

                    /** @var  $customerId */
                    $customerId = $recurringOrderItem->getMageCustId();
                    /** @var $customer load customer */
                    $customer = $this->_customerFactory->create()->load($customerId);

                    if (!$customer->getId()) {
                        $this->_ebizchargeLogger->addError(__('Error occurred, as this ' . $customerId .
                            ' Customer doest not exists'));
                        $failureNotifications[] = 'Item:' . $productId . ' ';
                        continue;
                    }
                    /** @var  $customerEmail */
                    $customerEmail = $customer->getEmail();

                    if (!$customerEmail) {
                        $this->_ebizchargeLogger->addError(__('Error occurred, as the customer email : ' .
                            $customerEmail . ' is not valid'));
                        $failureNotifications[] = 'Item:' . $productId . ' ';
                        continue;
                    }
                    /** @var  $customerName */
                    $customerName = $customer->getName();

                    /** @var  $regularPrice */
                    $regularPrice = $product->getPriceInfo()
                        ->getPrice('regular_price')
                        ->getAmount()
                        ->getValue();
                    $productFinalPrice = $product->getPriceInfo()
                        ->getPrice('final_price')
                        ->getAmount()
                        ->getValue();

                    /** @var $ebizProductType */
                    $ebizProductType = ProductInterface::EBIZCHARGE_PRODUCT_TYPE_SERVICE;

                    /** if product is Simple or Bundle */
                    if ($mageProductType == Type::TYPE_SIMPLE
                        || $mageProductType == Type::TYPE_BUNDLE) {
                        $regularPrice = $product->getPriceInfo()
                            ->getPrice('regular_price')
                            ->getAmount()
                            ->getValue();
                        $productFinalPrice = $product->getPriceInfo()
                            ->getPrice('final_price')
                            ->getAmount()
                            ->getValue();
                        $ebizProductType = ProductInterface::EBIZCHARGE_PRODUCT_TYPE_SALE;
                    }

                    /** product is Configurable */
                    if ($mageProductType == ProductInterface::PRODUCT_TYPE_CONFIGURABLE) {
                        $basePrice = $product->getPriceInfo()->getPrice('regular_price')->getAmount();
                        $regularPrice = $basePrice->getMinRegularAmount();
                        $productFinalPrice = $product->getFinalPrice();
                        $ebizProductType = ProductInterface::EBIZCHARGE_PRODUCT_TYPE_SALE;
                    }

                    /** @var $totalAmount */
                    $totalAmount = (float)$productFinalPrice * (float)$qtyOrdered;

                    /** @var $productStockItem */
                    $productStockItem = $this->_stockRegistry->getStockItem($productId);

                    $productMinQty = $productStockItem->getMinSaleQty();
                    $productSaleQty = $productStockItem->getQty();

                    /** if we have stock item */
                    if (!empty($productStockItem) && $productStockItem->getIsInStock()) {
                        $productMinQty = $productStockItem->getMinSaleQty();
                        $productSaleQty = $productStockItem->getQty();
                    }
                    /** sending stock qty */
                    $this->_ebizchargeLogger->addInfo(__('Current Stock of item: ' . $product->getSku() .
                        ' the Saleable Qty:' . $productSaleQty . ' Minimum Sale Qty:' . $productMinQty));

                    /** @var $itemData */
                    $itemData = [
                        'product_id' => $productId,
                        'product_name' => (string)$product->getName(),
                        'product_sku' => (string)$product->getSku(),
                        'qty_ordered' => $qtyOrdered,
                        'regular_price' => (double)$regularPrice,
                        'final_price' => (double)$productFinalPrice,
                        'saleable_qty' => $productSaleQty,
                        'item_type' => $ebizProductType,
                        'exclude_amount' => $totalAmount,
                        'customer_id' => $customerId,
                        'customer_name' => $customerName,
                        'customer_email' => $customerEmail,
                        'store_admin_emails' => $this->configFactory->create()->getStoreAdminEmails()
                    ];

                    /** @var $remainingQty */
                    $remainingQty = (double)$productSaleQty - (double)$qtyOrdered;

                    if ($productMinQty >= $remainingQty || $remainingQty <= 0) {
                        $this->_ebizchargeLogger->addInfo(__($product->getName() .
                            ' - Item, Qty is not available.so Sending notification to customer'));

                        //Because We would have to track Subscriptions to re-enable them
                        // when the product comes back in stock.
                        // $this->tranApiFactory->create()->suspendScheduledRecurringPaymentStatus($productId, 1);

                        // Send email to customer and Cc to admin
                        $lowStockEmailResp = $this->sendLowStockEmailToCustomer($itemData);

                        /** low stock response */
                        if ($lowStockEmailResp['error'] == true) {
                            $this->_ebizchargeLogger->addError(__(
                                'Error occurred during sending low stock email with customer & administrators '
                            ));
                            $failureNotifications[] = 'Item:' . $productId . ' to :' . $customerEmail;
                            continue;
                        }

                        /** Low Stock Email Response */
                        if ($lowStockEmailResp['error'] == false) {
                            $checkStockItemResp = [
                                'error' => false,
                                'low_stock_status' => true,
                                'message' => __(
                                    'Success, sent Low stock email to Customer at Customer Email:' .
                                    $customerEmail
                                )
                            ];
                            $successNotifications[] = 'Item:' . $product->getSku() . ' to :' . $customerEmail;

                            $this->_ebizchargeLogger->addInfo(__(
                                'Success, the low stock email has been shared with customer and administrators'
                            ));
                            if ($appState === 'cli') {
                                // phpcs:ignore
                                print_r("\n" . 'Success low stock email shared for ' . $product->getSku() .
                                    ' to Customer Email:' . $customerEmail);
                            }
                        }
                    }
                }
            } else {
                $this->_ebizchargeLogger->addInfo(__('No subscribed items are found active '));
                $checkStockItemResp['message'] = __('No subscribed items are found active ');
            }

            return $checkStockItemResp;
        } catch (\Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                'Exception occurred during checking stock for recurring items Error:' . $exception->getMessage()
            ));

            /** check if app state is cli */
            if ($appState === 'cli') {
                print_r("\n" . 'Found exception during sending email ' . $exception->getMessage());
            }
            $checkStockItemResp['message'] = __(
                'Found exception during sending Low Stock Email to The Customer Exception: ' .
                $exception->getMessage()
            );
            return $checkStockItemResp;
        }
    }
    // phpcs:disable

    /**
     * Send Low Stock Email to Customer
     *
     * @param array $itemData
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws MailException
     */
    public function sendLowStockEmailToCustomer($itemData = []): array
    {
        /** @var  $sendLowStockEmailResponse */
        $sendLowStockEmailResponse = [
            'error' => true,
            'status' => false,
            'message' => __()
        ];

        try {
            $this->_ebizchargeLogger->addInfo(__METHOD__);
            $store = $this->_storeManager->getStore();
            $storeId = $store->getId();

            $fromAdmingEmail = $itemData['store_admin_emails']['salesEmail'];
            $name = $itemData['store_admin_emails']['generalName'];
            $toAdmingEmail = $itemData['store_admin_emails']['generalEmail'];
            $templateOptions = [
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId
            ];

            /** @var $customerEmail */
            $customerEmail = $itemData['customer_email'];

            /** @var  $templateVars */
            $templateVars = [
                'storeName' => $store->getName(),
                'store' => $store,
                'itemId' => $itemData["product_id"],
                'itemSku' => $itemData['product_sku'],
                'itemPrice' => $itemData['final_price'],
                'itemName' => $itemData["product_name"],
                'customerName' => $itemData['customer_name'],
                'orderedQty' => $itemData['qty_ordered'],
                'customerEmail' => $itemData['customer_email'],
            ];
            /** @var  $fromEmail */
            $fromEmail = [
                'email' => $fromAdmingEmail,
                'name' => $name
            ];
            /** sending email to customer */
            $this->_ebizchargeLogger->addInfo(__('sending email to Customer Email:' . $customerEmail));

            $this->_stateInterface->suspend();

            /** @var $toEmails */
            $toEmails = [$toAdmingEmail, $customerEmail];

            /** @var $lowStockEmailTemplateId */
            $configLowStockEmailTemplateId = $this->configFactory->create()->getLowStockEmailTemplate();

            /** @var $transport */
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier(
                    $configLowStockEmailTemplateId
                )
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($fromEmail)
                ->addTo($toEmails)
                ->getTransport();

            /** Transport Send Email */
            $transport->sendMessage();

            $this->_stateInterface->resume();
            $this->_ebizchargeLogger->info('Email has been sent to the customers and Store admins ! Email:' .
                $customerEmail);

            /** @var  $sendLowStockEmailResponse */
            $sendLowStockEmailResponse = [
                'error' => false,
                'status' => true,
                'message' => __('Success, the Email has been sent to Customers and Administrators')
            ];

            return $sendLowStockEmailResponse;
        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical('Email could not be sent! Error:' .
                $exception->getMessage());
            $sendLowStockEmailResponse['message'] = __('Exception occurred during sending Email, ' .
                $exception->getMessage());
            return $sendLowStockEmailResponse;
        }
    }

    /**
     * Get Email Template by id
     *
     * @param string $templateId
     * @return int|mixed
     */
    public function getEmailTemplateIdById($templateId = '')
    {
        /** @var  $emailCollection */
        $emailCollection = $this->_emailTemplateCollectionFctory->create();
        $emailCollection->load();
        $emailTemplate = 1;

        if (count($emailCollection) > 0) {
            foreach ($emailCollection as $emailItem) {
                if ($emailItem->getTemplateId() == $templateId) {
                    return $emailItem;
                }
                $emailTemplate = $emailItem;
            }
        }
        return $emailTemplate;
    }
}
