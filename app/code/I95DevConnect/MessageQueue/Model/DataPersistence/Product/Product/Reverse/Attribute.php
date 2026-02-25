<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev (https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product\Reverse;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterfaceFactory;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Eav\Api\Data\AttributeFrontendLabelInterfaceFactory;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Eav\Model\AttributeManagementFactory;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Attribute for adding new attribute for products
 */
class Attribute
{
    public const DEFAULT_GROUP = 'General';
    public const I95DEV_DEFAULT_ATTRIBUTE_TYPE = 'select';
    public const ATTRIBUTECODE = 'attributeCode';

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var AttributeManagementFactory
     */
    public $attributeManagement;

    /**
     * @var ProductAttributeOptionManagementInterfaceFactory
     */
    public $productAttributeOption;

    /**
     * @var ProductAttributeRepositoryInterfaceFactory
     */
    public $productAttributeRepo;

    /**
     * @var ProductAttributeInterfaceFactory
     */
    public $productAttributeFactory;

    /**
     * @var AttributeOptionInterfaceFactory
     */
    public $attributeOptionFactory;

    /**
     * @var AttributeFrontendLabelInterfaceFactory
     */
    public $attributeFrontendFactory;

    /**
     * @var AttributeOptionLabelInterfaceFactory
     */
    public $attributeOptionLabel;

    /**
     * @var AttributeFactory
     */
    public $eavAttribute;

    /**
     * @var CollectionFactory
     */
    public $attributeSetCollection;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var Repository
     */
    public $productAttributeRepository;

    /**
     *
     * @param Data $dataHelper
     * @param AttributeManagementFactory $attributeManagement
     * @param ProductAttributeOptionManagementInterfaceFactory $productAttributeOption
     * @param ProductAttributeRepositoryInterfaceFactory $productAttributeRepo
     * @param ProductAttributeInterfaceFactory $productAttributeFactory
     * @param AttributeOptionInterfaceFactory $attributeOptionFactory
     * @param AttributeFrontendLabelInterfaceFactory $attributeFrontendFactory
     * @param AttributeOptionLabelInterfaceFactory $attributeOptionLabel
     * @param AttributeFactory $eavAttribute
     * @param CollectionFactory $attributeSetCollection
     * @param StoreManagerInterface $storeManager
     * @param Repository $productAttributeRepository
     */
    public function __construct( // NOSONAR
        Data $dataHelper,
        AttributeManagementFactory $attributeManagement,
        ProductAttributeOptionManagementInterfaceFactory $productAttributeOption,
        ProductAttributeRepositoryInterfaceFactory $productAttributeRepo,
        ProductAttributeInterfaceFactory $productAttributeFactory,
        AttributeOptionInterfaceFactory $attributeOptionFactory,
        AttributeFrontendLabelInterfaceFactory $attributeFrontendFactory,
        AttributeOptionLabelInterfaceFactory $attributeOptionLabel,
        AttributeFactory $eavAttribute,
        CollectionFactory $attributeSetCollection,
        StoreManagerInterface $storeManager,
        Repository $productAttributeRepository
    ) {
        $this->dataHelper = $dataHelper;
        $this->attributeManagement = $attributeManagement;
        $this->productAttributeOption = $productAttributeOption;
        $this->productAttributeRepo = $productAttributeRepo;
        $this->productAttributeFactory = $productAttributeFactory;
        $this->attributeOptionFactory = $attributeOptionFactory;
        $this->attributeFrontendFactory = $attributeFrontendFactory;
        $this->attributeOptionLabel = $attributeOptionLabel;
        $this->eavAttribute = $eavAttribute;
        $this->attributeSetCollection = $attributeSetCollection;
        $this->storeManager = $storeManager;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * Add attribute and attribute option to attribute set
     *
     * @param string $attributeWithKeyList
     *
     * @return array
     * @throws LocalizedException
     */
    public function processAttributeWithKey($attributeWithKeyList)
    {
        try {
            $postData = [];
            if (empty($attributeWithKeyList)) {
                return $postData;
            }
            
            foreach ($attributeWithKeyList as $attributeWithKey) {
                $erpAttributeCode = $this->dataHelper->getValueFromArray(
                    self::ATTRIBUTECODE,
                    $attributeWithKey
                );
                $attributeCodeString = str_replace(' ', '_', $erpAttributeCode); // Replaces all spaces with hyphens.
                $getAttributeCode = preg_replace(
                    '/[^A-Za-z0-9\_]/',
                    '',
                    $attributeCodeString
                ); // Removes special chars.
                $attributeValue = $this->dataHelper->getValueFromArray(
                    "attributeValue",
                    $attributeWithKey
                );
                $attributeCode = strtolower($getAttributeCode);
                $options = $this->prepareOptions($attributeCode, $attributeValue, $attributeWithKey);
                $this->assignAttributeSet($attributeCode);
                $attributeType = $this->dataHelper->getValueFromArray("attributeType", $attributeWithKey);
                $attributeType = isset($attributeType) ? $attributeType : self::I95DEV_DEFAULT_ATTRIBUTE_TYPE;
                $attributeValue =
                    $this->getAttributeOptionValue($attributeCode, $attributeType, $attributeValue, $options);

                $postData[] = [
                self::ATTRIBUTECODE => $attributeCode,
                "value" => $attributeValue
                ];
            }
            
            return $postData;
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __($e->getMessage()),
                null,
                $e->getMessage()
            );
        }
    }

    /**
     * Get attribute option value
     *
     * @param string $attributeCode
     * @param string $attributeType
     * @param mixed $attributeValue
     * @param array $options
     * @return mixed
     */
    public function getAttributeOptionValue($attributeCode, $attributeType, $attributeValue, $options)
    {
        if (strtolower($attributeType) == 'select') {
            $count = 0;
            foreach ($options as $option) {
                if (strtolower($option->getLabel()) == strtolower($attributeValue)) {
                    $attributeValue = $option->getValue();
                    $count = 1;
                    break;
                }
            }

            if ($count === 0) {
                $this->addAttrOptionToExistingAttr($attributeCode, $attributeValue);
                $attributeValue = $this->getAttributeValue($attributeCode, $attributeValue);
            }
        }
        return $attributeValue;
    }

    /**
     * Prepare Options
     *
     * @param string $attributeCode
     * @param string $attributeValue
     * @param object $attributeWithKey
     * @return mixed
     */
    public function prepareOptions($attributeCode, $attributeValue, $attributeWithKey)
    {
        try {
            $options = $this->productAttributeRepository->get($attributeCode)->getOptions();
        } catch (LocalizedException $e) {
            // if attribute does not exist then magento throws exception, below code is to create attribute
            $result = $this->createAttribute($attributeCode, $attributeValue, $attributeWithKey);
            if (empty($result->getAttributeCode())) {
                $message = "There is some issue in creation of attribute " . $attributeCode;
                throw new LocalizedException(
                    __($message),
                    null,
                    105
                );
            }
            $attributeType = $this->dataHelper->getValueFromArray("attributeType", $attributeWithKey);
            $attributeType = isset($attributeType) ? $attributeType : self::I95DEV_DEFAULT_ATTRIBUTE_TYPE;
            if (strtolower($attributeType) == 'select') {
                $options = $this->productAttributeRepository->get($attributeCode)->getOptions();
            } else {
                $options = [];
            }
        }

        return $options;
    }

    /**
     * Create product Attribute in magento
     *
     * @param string $attributeCode
     * @param string $attributeValue
     * @param array $attributeWithKey
     * @return Object
     * @throws LocalizedException
     */
    public function createAttribute($attributeCode, $attributeValue, $attributeWithKey)
    {
        $attributeType = $this->dataHelper->getValueFromArray("attributeType", $attributeWithKey);
        $attributeType = $frontendInput = isset($attributeType) ? $attributeType : self::I95DEV_DEFAULT_ATTRIBUTE_TYPE;
        $attributeType = $frontendInput = strtolower($attributeType);
        if (in_array($frontendInput, ['decimal','integer'])) {
            $attributeType = $frontendInput = 'text';
        }
        //@author Divya Koona. Getting attribute name from string code modified
        $attributeName = $this->dataHelper->getValueFromArray(self::ATTRIBUTECODE, $attributeWithKey);
        $dataType = $this->eavAttribute->create()->getBackendTypeByInput($attributeType);
        //@author Divya Koona. $this->productAttributeFactory changed to $productAttributeInterface as
        //I am getting fatal error Invalid method create() while syncing multiple records at a time
        $productAttributeInterface = $this->productAttributeFactory->create();
        $productAttributeInterface->setAttributeCode($attributeCode);
        $productAttributeInterface->setFrontendInput($frontendInput);
        $productAttributeInterface->setBackendType($dataType);
        $productAttributeInterface->setDefaultFrontendLabel($attributeName);
        $productAttributeInterface->setFrontendLabels([$this->prepareAttributeFrontend($attributeName)]);
        $productAttributeInterface->setIsUserDefined(true);
        $productAttributeInterface->setIsFilterable(true);
        $productAttributeInterface->setIsVisible(true);
        $productAttributeInterface->setIsSearchable(true);
        if (strtolower($attributeType) == 'select') {
            $productAttributeInterface->setOptions([$this->prepareAttributeOption($attributeValue)]);
        }
        try {
            $attribute = $this->productAttributeRepo->create()->save($productAttributeInterface);
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
        return $attribute;
    }

    /**
     * Prepare attribute frontend object
     *
     * @param string $attributeName
     * @return object
     * @author Arushi Bansal
     */
    public function prepareAttributeFrontend($attributeName)
    {
        //@author Divya Koona. $this->attributeFrontend changed to $attributeFrontendFactory as
        //I am getting fatal error Invalid method create() while syncing multiple records at a time
        $attributeFrontend = $this->attributeFrontendFactory->create();
        $attributeFrontend->setStoreId(0);
        $attributeFrontend->setLabel($attributeName);

        return $attributeFrontend;
    }

    /**
     * Prepare object of attribute options
     *
     * @param string $attributeValue
     * @return Object
     * @author Arushi Bansal
     */
    public function prepareAttributeOption($attributeValue)
    {
        //@author Divya Koona. $this->attributeOptionFactory changed to $attributeOption as
        //I am getting fatal error Invalid method create() while syncing multiple records at a time
        $attributeOption = $this->attributeOptionFactory->create();
        $attributeOption->setLabel($attributeValue);

        // @author Arushi Bansal adding setValue to fix - 22550019
        $attributeOption->setValue($attributeValue);
        $storeLabels = $this->getStoreLabels($attributeValue);
        if (!empty($storeLabels)) {
            $attributeOption->setStoreLabels($storeLabels);
        }

        return $attributeOption;
    }

    /**
     * Get store labels
     *
     * @param string $attributeValue
     *
     * @return array
     * @author Arushi Bansal
     */
    public function getStoreLabels($attributeValue)
    {
        $storeManagerDataList = $this->storeManager->getStores();
        $storeLabels = [];

        foreach ($storeManagerDataList as $store) {
            $attributeOptionLabelData = $this->attributeOptionLabel->create();
            $attributeOptionLabelData->setStoreId($store->getStoreId());
            $attributeOptionLabelData->setLabel($attributeValue);

            $storeLabels[] = $attributeOptionLabelData;
        }

        return $storeLabels;
    }

    /**
     * Assign attribute set
     *
     * @param string $attributeCode
     * @throws LocalizedException
     * @author Kavya Koona
     */
    public function assignAttributeSet($attributeCode)
    {
        try {
            $attrSetId = $this->dataHelper->getscopeConfig(
                'i95dev_messagequeue/I95DevConnect_settings/attribute_set',
                ScopeInterface::SCOPE_WEBSITE,
                $this->storeManager->getDefaultStoreView()->getWebsiteId()
            );
            $defaultGroupId = $this->dataHelper->getscopeConfig(
                'i95dev_messagequeue/I95DevConnect_settings/attribute_group',
                ScopeInterface::SCOPE_WEBSITE,
                $this->storeManager->getDefaultStoreView()->getWebsiteId()
            );
            $this->attributeManagement->create()->assign(
                'catalog_product',
                $attrSetId,
                $defaultGroupId,
                $attributeCode,
                $this->attributeSetCollection->create()->count() * 10
            );
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __($e->getMessage()),
                null,
                $e->getCode()
            );
        }
    }

    /**
     * Add attribute options to existing attribute
     *
     * @param string $attributeCode
     * @param string $attributeValue
     *
     * @return string
     */
    public function addAttrOptionToExistingAttr($attributeCode, $attributeValue)
    {
        try {
            //@author Divya Koona. $this->attributeOptionFactory changed to $attributeOption as
            //I am getting fatal error Invalid method create() while syncing multiple records at a time
            $attributeOption = $this->attributeOptionFactory->create();
            $attributeOption->setLabel($attributeValue);
            //@author Divya Koona. $this->attributeOptionFactory->setValue($attributeValue) removed
            //I am getting SQL state issue if we pass int value to attribute option like style: 10
            $storeLabels = $this->getStoreLabels($attributeValue);
            if (!empty($storeLabels)) {
                $attributeOption->setStoreLabels($storeLabels);
            }

            return $this->productAttributeOption->create()->add($attributeCode, $attributeOption);
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __($e->getMessage()),
                null,
                $e->getCode()
            );
        }
    }

    /**
     * Get Attribute Value
     *
     * @param string $attributeCode
     * @param string $attributeValue
     * @return mixed
     */
    public function getAttributeValue($attributeCode, $attributeValue)
    {
        $options = $this->productAttributeRepository->get($attributeCode)->getOptions();
        foreach ($options as $option) {
            if ($option->getLabel() == $attributeValue) {
                $attributeValue = $option->getValue();
            }
        }

        return $attributeValue;
    }
}
