<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_ConfigurableProducts
 */

namespace I95DevConnect\ConfigurableProducts\Model\DataPersistence\ConfigurableProducts;

use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\AbstractProduct;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product\Reverse\AttributeFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\ProductRepositoryFactory;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product\Reverse\Stock;
use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;
use Magento\Framework\Json\Decoder;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterfaceFactory;
use I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory;
use I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Event\Manager;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterfaceFactory;
use Magento\ConfigurableProduct\Api\OptionRepositoryInterfaceFactory;
use Magento\ConfigurableProduct\Api\Data\OptionInterfaceFactory;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterfaceFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory;

/**
 * Create class for configurable product sync
 */
class Create extends AbstractProduct
{
    /**
     * @var object
     */
    public $productExtension;

    /**
     * @var array
     */
    public $childProductIdsList;

    /**
     * @var array
     */
    public $attributeLists;

    /**
     * @var OptionRepositoryInterfaceFactory
     */
    public $configOptionRepo;

    /**
     *
     * @var OptionInterfaceFactory
     */
    public $optionInterface;

    /**
     *
     * @var OptionValueInterfaceFactory
     */
    public $optionValue;

    /**
     *
     * @var ProductAttributeRepositoryInterface
     */
    public $productAttrRepo;

    /**
     *
     * @var \I95DevConnect\ConfigurableProducts\Helper\Data
     */
    public $configurableData;

    /**
     *
     * @var ProductAttributeInterfaceFactory
     */
    public $productAttributeInterface;

    /**
     *
     * @var array
     */
    public $validateFields = [
        'targetId' => 'configurable_pro_02',
        'configurableEntity' => 'configurable_pro_03',
        'typeId' => 'configurable_pro_04'
    ];

    /**
     * Create constructor.
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
     * @param OptionRepositoryInterfaceFactory $configOptionRepo
     * @param OptionInterfaceFactory $optionInterface
     * @param OptionValueInterfaceFactory $optionValue
     * @param ProductAttributeRepositoryInterfaceFactory $productAttrRepo
     * @param \I95DevConnect\ConfigurableProducts\Helper\Data $configurableData
     * @param ProductAttributeInterfaceFactory $productAttributeInterface
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
        I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository,
        OptionRepositoryInterfaceFactory $configOptionRepo,
        OptionInterfaceFactory $optionInterface,
        OptionValueInterfaceFactory $optionValue,
        ProductAttributeRepositoryInterfaceFactory $productAttrRepo,
        \I95DevConnect\ConfigurableProducts\Helper\Data $configurableData,
        ProductAttributeInterfaceFactory $productAttributeInterface
    ) {
        $this->configOptionRepo = $configOptionRepo;
        $this->optionInterface = $optionInterface;
        $this->optionValue = $optionValue;
        $this->productAttrRepo = $productAttrRepo;
        $this->configurableData = $configurableData;
        $this->productAttributeInterface = $productAttributeInterface;

        parent::__construct(
            $dataHelper,
            $storeManager,
            $attribute,
            $productInterface,
            $productRepo,
            $productStock,
            $productExtensionInterface,
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
     * Create configurable product in magento
     *
     * @param array $stringData
     * @param string $entityCode
     * @return I95DevResponseInterface
     */
    public function createConfigurableProduct($stringData, $entityCode)
    {
        try {
            $this->stringData = $stringData;
            $this->stringData['qty'] = 0;
            $this->validateData();
            $this->setBasicDetails();

            // Custom attribute mapping
            $ccEcommerceLeadDays = $this->dataHelper->getValueFromArray('ecommerceLeadDays', $this->stringData);
            if ($ccEcommerceLeadDays !== null && $ccEcommerceLeadDays !== '') {
                $this->productInterface->setCustomAttribute('cc_ecommerce_lead_days', $ccEcommerceLeadDays);
            }
            $hasDiscount = $this->dataHelper->getValueFromArray('hasDiscount', $this->stringData);
            $hasDiscount = (int) ($hasDiscount ?? 0);
            $this->productInterface->setCustomAttribute('cc_hasdiscount', $hasDiscount);

            $ccSearchdescription = $this->dataHelper->getValueFromArray('searchDescription', $this->stringData);
            $this->productInterface->setCustomAttribute('cc_searchdescription', $ccSearchdescription);

            $gmItemNo = $this->dataHelper->getValueFromArray('gmItemNo', $this->stringData);
            $this->productInterface->setCustomAttribute('cc_gmpartnumber', $gmItemNo);

            $paragonItemNo = $this->dataHelper->getValueFromArray('paragonItemNo', $this->stringData);
            $this->productInterface->setCustomAttribute('cc_paragonitemno', $paragonItemNo);

            $ccNetWeight = $this->dataHelper->getValueFromArray('ccNetWeight', $this->stringData);
            $this->productInterface->setCustomAttribute('cc_net_weight', $ccNetWeight);

            $lastEcommDateChange = $this->dataHelper->getValueFromArray('lastEcommDateChange', $this->stringData);
            $lastEcommDateChange = (!empty($lastEcommDateChange))
            ? date('Y-m-d', strtotime($lastEcommDateChange))
            : null;
            $this->productInterface->setCustomAttribute('cc_ecomleaddays_lastupdate_date', $lastEcommDateChange);

            $airShipmentOk = $this->dataHelper->getValueFromArray('airShipmentOk', $this->stringData);
            $airShipmentOk = (int) ($airShipmentOk ?? 0);
            $this->productInterface->setCustomAttribute('cc_airshipmentok', $airShipmentOk);

            $this->productInterface->setTypeId('configurable');
            $variantAttributes = $this->checkForProductAttributes();

            /* isNewItem coming from parent class - check for product existence */
            if ($this->isNewItem) {
                $this->productInterface->setVisibility(Visibility::VISIBILITY_BOTH);
                $this->setStockInformation(false);
            }
            $this->productExtension->setConfigurableProductLinks($this->childProductIdsList);
            if (empty(!$variantAttributes)) {
                $this->saveOptionValues(array_keys($variantAttributes));
            }
            $this->productInterface->setExtensionAttributes($this->productExtension);
            $beforeeventname = 'erpconnect_messagequeuetomagento_beforesave_' . $entityCode;
            $this->eventManager->dispatch($beforeeventname, ['currentObject' => $this]);
            $i95dev_skip = 'i95_observer_skip';
            $this->dataHelper->unsetGlobalValue($i95dev_skip);
            $this->dataHelper->setGlobalValue($i95dev_skip, true);

            $product = $this->productRepo->create()->save($this->productInterface);
            if (empty($product->getId())) {
                throw new LocalizedException(
                    __("There was some error in creating product.")
                );
            }
            $this->dataHelper->unsetGlobalValue('i95_observer_skip');
            $aftereventname = 'erpconnect_messagequeuetomagento_aftersave_' . $this->getEntityCode();
            $this->eventManager->dispatch($aftereventname, ['currentObject' => $this]);
        } catch (LocalizedException $ex) {
            return $this->setResponse(Data::ERROR, $ex->getMessage());
        }
        return $this->setResponse(Data::SUCCESS, "Record Successfully Synced", $product->getId());
    }

    /**
     * Add option values
     *
     * @param array $attributeCodes
     */
    public function saveOptionValues($attributeCodes)
    {
        $optionArray = [];
        foreach ($attributeCodes as $_attributeCode) {
            $attribute = $this->productAttrRepo->create()->get(strtolower($_attributeCode));
            $configOptionValue = [];
            foreach ($attribute->getOptions() as $option) {
                if ($option->getValue() != '') {
                    $configOptionValue[] = $this->optionValue->create()->setValueIndex($option->getValue());
                }
            }
            $optionObj = $this->optionInterface->create();
            $optionObj->setAttributeId($attribute->getAttributeId());
            $optionObj->setLabel($attribute->getDefaultFrontendLabel());
            $optionObj->setValues($configOptionValue);
            $optionArray[] = $optionObj;
        }
        $this->productExtension->setConfigurableProductOptions($optionArray);
    }

    /**
     * Validate ERP data.
     *
     * @createdBy Debashis S. Gopal
     * @throws    LocalizedException
     */
    public function validateData()
    {
        /* checking I95DevConfigurabelProduct is enabled or Not */
        if ($this->configurableData->isEnabled()) {
            $this->validate->validateFields = $this->validateFields;
            $this->validate->validateData($this->stringData);
            $itemtype = $this->dataHelper->getValueFromArray("typeId", $this->stringData);
            if ($itemtype !== 'Configurable') {
                throw new LocalizedException(__('configurable_pro_05'));
            }
            $configurableEntity = $this->dataHelper->getValueFromArray("configurableEntity", $this->stringData);
            $childSkus = $this->dataHelper->getValueFromArray("childSkus", $configurableEntity);
            if ($childSkus != "") {
                $this->childProductIdsList = $this->validateChildProducts($childSkus);
            }
            $this->attributeLists = explode(
                ",",
                $this->dataHelper->getValueFromArray("attributes", $configurableEntity)
            );
            if (empty($this->attributeLists)) {
                throw new LocalizedException(__("configurable_pro_06"));
            }
        } else {
            throw new LocalizedException(__("configurable_pro_01"));
        }
    }

    /**
     * Check if child Skus exists or not
     *
     * @param  array $childSkus
     * @return array
     */
    public function validateChildProducts($childSkus)
    {
        $childSkuList = explode(",", $childSkus);
        $childProductIdsLists = [];
        foreach ($childSkuList as $childSku) {
            $childProductId = $this->getProductPrimaryId($childSku);

            if ($childProductId > 0) {
                $childProductIdsLists[] = $childProductId;
            }
        }
        return $childProductIdsLists;
    }

    /**
     * Check if given product attributes are available or not, If not then through error
     *
     * @return array
     * @throws LocalizedException
     * @throws LocalizedException
     */
    public function checkForProductAttributes()
    {
        $variantAttributes = [];
        foreach ($this->attributeLists as $attribute) {
            try {
                $result = $this->productAttrRepo->create()->get(strtolower($attribute));
                $attributeId = $result->getAttributeId();
            } catch (NoSuchEntityException $ex) {
                throw new LocalizedException(__($ex->getMessage()));
            }
            $variantAttributes[strtolower($attribute)] = $attributeId;
        }
        return $variantAttributes;
    }
}
