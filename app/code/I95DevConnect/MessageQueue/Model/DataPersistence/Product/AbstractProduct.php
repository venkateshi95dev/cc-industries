<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev (https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Product;

use I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product\Reverse\AttributeFactory;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product\Reverse\Stock;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory;
use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductRepositoryFactory;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Decoder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AbstractProduct parent class of product persistance.
 * @updatedBy Arushi Bansal
 */
class AbstractProduct extends AbstractDataPersistence
{
    /**
     * @var int
     */
    public $qty = 0;

    /**
     * @var int
     */
    public $storeId = 0;

    /**
     *
     * @var array
     */
    public $validateFields = [
        'sku' => 'i95dev_prod_005',
        'price' => 'i95dev_prod_014',
    ];

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var object
     */
    public $attribute;

    /**
     * @var ProductInterfaceFactory
     */
    public $productInterface;

    /**
     * @var ProductRepositoryFactory
     */
    public $productRepo;

    /**
     * @var Product\Reverse\Stock
     */
    public $productStock;

    /**
     * @var ProductExtensionInterfaceFactory
     */
    public $productExtensionInterface;

    /**
     * @var int
     */
    public $isNewItem;

    /**
     * @var array
     */
    public $erpAttributes = [];

    /**
     * @var string
     */
    public $component = '';

    /**
     * @var object
     */
    public $productExtension;

    /**
     *
     * @param Data $dataHelper
     * @param StoreManagerInterface $storeManager
     * @param AttributeFactory $attribute
     * @param ProductInterfaceFactory $productInterface
     * @param ProductRepositoryFactory $productRepo
     * @param Stock $productStock
     * @param ProductExtensionInterfaceFactory $productExtensionInterface
     * @param Decoder $jsonDecoder
     * @param I95DevResponseInterfaceFactory $i95DevResponse
     * @param ErrorUpdateDataFactory $messageErrorModel
     * @param I95DevErpMQInterfaceFactory $i95DevErpMQ
     * @param LoggerInterfaceFactory $logger
     * @param I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository
     * @param DateTime $date
     * @param Manager $eventManager
     * @param Validate $validate
     * @param I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository
     */
    public function __construct( // NOSONAR
        Data $dataHelper,
        StoreManagerInterface $storeManager,
        AttributeFactory $attribute,
        ProductInterfaceFactory $productInterface,
        ProductRepositoryFactory $productRepo,
        Stock $productStock,
        ProductExtensionInterfaceFactory $productExtensionInterface,
        Decoder $jsonDecoder,
        I95DevResponseInterfaceFactory $i95DevResponse,
        ErrorUpdateDataFactory $messageErrorModel,
        I95DevErpMQInterfaceFactory $i95DevErpMQ,
        LoggerInterfaceFactory $logger,
        I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository,
        DateTime $date,
        Manager $eventManager,
        Validate $validate,
        I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository
    ) {
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
        $this->attribute = $attribute->create();
        $this->productInterface = $productInterface;
        $this->productRepo = $productRepo;
        $this->productStock = $productStock;
        $this->productExtensionInterface = $productExtensionInterface;

        parent::__construct(
            $jsonDecoder,
            $i95DevResponse,
            $messageErrorModel,
            $i95DevErpMQ,
            $logger,
            $i95DevErpMQRepository,
            $date,
            $eventManager,
            $validate,
            $i95DevERPDataRepository
        );
    }

    /**
     * Set basic details of a product
     *
     * @throws LocalizedException
     */
    public function setBasicDetails()
    {
        /** @updatedBy kavya.k. added setCurrentStore* */
        $this->storeManager->setCurrentStore(Store::ADMIN_CODE);
        $this->productInterface = $this->productInterface->create();
        $this->validate->validateFields = $this->validateFields;
        $this->validate->validateData($this->stringData);
        $this->prepareDataForApi();
    }

    /**
     * Prepare post data array
     */
    public function prepareDataForApi()
    {
        $sku = $this->dataHelper->getValueFromArray("sku", $this->stringData);
        $this->component = $this->dataHelper->getComponent();
        /**
         * @updatedBy Debashis S. Gopal
         * $this->processAttributeWithKey() and directly calling $this->attribute->processAttributeWithKey()
         * if $attributeWithKeyList is set. Because facing issue if new attribute creation and assign to product,
         * at same time.Option values are not showing as selected
         */
        $attributeWithKeyList = $this->dataHelper->getValueFromArray("attributeWithKey", $this->stringData);
        if (!empty($attributeWithKeyList) && is_array($attributeWithKeyList)) {
            $this->erpAttributes = $this->attribute->processAttributeWithKey($attributeWithKeyList);
        }
        $this->productInterface->setSku($sku);

        $productId = $this->getProductPrimaryId($sku);
        $this->productExtension = $this->productExtensionInterface->create();
        $websiteIds = $this->dataHelper->getValueFromArray("websiteIds", $this->stringData);

        if ($productId > 0) {
            $status = $this->dataHelper->getValueFromArray("status", $this->stringData);
            if (($this->component == "GP" || $this->component == "NAV") && isset($status) && $status != '') {
                $this->productInterface->setStatus($status);
            }

            if (!empty($websiteIds)) {
                $storeId = $this->storeManager->getWebsite($websiteIds)->getDefaultStore()->getId();
                $this->productInterface->setStoreId($storeId);
                $websiteIds = explode(',', $websiteIds);
                $productData = $this->getProductBySku($sku);
                $existing_websites = $productData->getWebsiteIds();
                if(is_array($existing_websites)){
                    $websiteIds = array_unique(array_merge($websiteIds,$existing_websites));
                }
                $this->productExtension->setWebsiteIds($websiteIds);
            }
            $existing_packages = $productData->getCustomAttribute("cc_packages");
            if($existing_packages){
                $this->productInterface->setCustomAttribute("cc_packages",$existing_packages->getValue());
            }
        } else {
            $this->isNewItem = true;
            $name = $this->dataHelper->getValueFromArray("name", $this->stringData);
            if ($name == '') {
                throw new LocalizedException(
                    __('i95dev_prod_001'),
                    null,
                    104
                );
            }
            $attributeSetId = $this->dataHelper->getscopeConfig(
                'i95dev_messagequeue/I95DevConnect_settings/attribute_set',
                ScopeInterface::SCOPE_WEBSITE,
                $this->storeManager->getDefaultStoreView()->getWebsiteId()
            );
            $this->productInterface->setAttributeSetId($attributeSetId);
            $this->productInterface->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE);
            $this->productInterface->setStatus(Status::STATUS_DISABLED);
            $this->productInterface->setName($this->dataHelper->getValueFromArray("name", $this->stringData));
            if (!empty($websiteIds)) {
                $storeId = $this->storeManager->getWebsite($websiteIds)->getDefaultStore()->getId();
                $this->productExtension->setWebsiteIds(explode(',', $websiteIds));
                $this->productInterface->setStoreId($storeId);
            } else {
                // @updatedBy Arushi Bansal added getDefaultStoreView() to support multiwebsite default selection
                $this->productExtension->setWebsiteIds([$this->storeManager->getDefaultStoreView()->getWebsiteId()]);
            }
        }
        $price = $this->dataHelper->getValueFromArray("price", $this->stringData);
        $this->productInterface->setPrice(round((float)$price, 2));

        $this->productInterface->setTypeId("simple");

        $weight = $this->dataHelper->getValueFromArray("weight", $this->stringData);
        if ($weight) {
            $this->productInterface->setWeight($weight);
        }
        $this->addCustomAttributesToProduct();
    }

    /**
     * Get id of product by sku
     *
     * @param string $sku
     * @return int
     */
    public function getProductPrimaryId($sku)
    {
        try {
            $result = $this->productRepo->create()->get($sku);

            if (!empty($result->getId())) {
                return $result->getId();
            } else {
                return 0;
            }
        } catch (NoSuchEntityException $e) {
            return 0;
        }
    }

    /**
     * Add custom attibutes to product
     * Attributes [description,short_description,cost,tax_class_id,update_by,targetproductstatus,
     * and other variant attribute from ERP]
     *
     * @createdBy Debashis S. Gopal
     */
    public function addCustomAttributesToProduct()
    {
        // updatedBy R Ranjith, Sync description and short description only in product creation
        if ($this->isNewItem) {
            $description = $this->dataHelper->getValueFromArray("description", $this->stringData);
            if ($description) {
                $this->productInterface->setCustomAttribute("description", $description);
            }
            $shortDescription = $this->dataHelper->getValueFromArray("shortDescription", $this->stringData);
            if ($shortDescription) {
                $this->productInterface->setCustomAttribute("short_description", $shortDescription);
            }
        }

        $cost = $this->dataHelper->getValueFromArray("cost", $this->stringData);
        if ($cost) {
            $this->productInterface->setCustomAttribute("cost", $cost);
        }
        $taxClassId = $this->dataHelper->getValueFromArray("taxClassId", $this->stringData);
        if ($taxClassId !== null) {
            $this->productInterface->setCustomAttribute("tax_class_id", $taxClassId);
        }

        if ($this->component) {
            $this->productInterface->setCustomAttribute("update_by", $this->component);
        }

        $this->productInterface->setCustomAttribute(
            "targetproductstatus",
            Data::SYNCED
        );

        if (!empty($this->erpAttributes)) {
            foreach ($this->erpAttributes as $value) {
                $this->productInterface->setCustomAttribute($value["attributeCode"], $value["value"]);
            }
        }
    }

    /**
     * Assign product attribute set and create attribute
     *
     * @param type $attributeCode
     *
     * @return void
     */
    public function assignAttrSet($attributeCode)
    {
        return $this->attribute->assignAttributeSet($attributeCode);
    }

    /**
     * Set product stock information
     *
     * @param bool $manageStock
     */
    public function setStockInformation($manageStock = true)
    {
        $stockItem = $this->productStock->setStockInformation($this->stringData, $manageStock);

        $this->productExtension->setStockItem($stockItem);
    }

    /**
     * Get id of product by sku
     *
     * @param string $sku
     * @return int
     */
    public function getProductBySku($sku)
    {
        try {
            $result = $this->productRepo->create()->get($sku);

            if (!empty($result->getId())) {
                return $result;
            } else {
                return 0;
            }
        } catch (LocalizedException $e) {
            return 0;
        }
    }

  
}
