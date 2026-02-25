<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ConfigurableProducts
 */

namespace I95DevConnect\ConfigurableProducts\Observer\Forward;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductRepositoryFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Observer class for order items
 */
class OrderItemObserver implements ObserverInterface
{
    public const DISCOUNT = "discount";

    /**
     * @var ProductRepositoryInterface|ProductRepositoryFactory
     */
    public $productRepo;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var Attribute
     */
    public $eavAttrModel;

    /**
     * @var string
     */
    public $parentSku;

    /**
     * @var object
     */
    public $erpOrderItem;

    /**
     * @var string
     */
    public $parentType;

    /**
     * OrderItemObserver constructor.
     *
     * @param ProductRepositoryInterface $productRepo
     * @param Data $dataHelper
     * @param Attribute $eavAttrModel
     */
    public function __construct(
        ProductRepositoryInterface $productRepo,
        Data $dataHelper,
        Attribute $eavAttrModel
    ) {
        $this->productRepo = $productRepo;
        $this->dataHelper = $dataHelper;
        $this->eavAttrModel = $eavAttrModel;
    }

    /**
     * Set order item info in forward sync
     *
     * @param Observer $observer
     * @author Divya Koona.
     */
    public function execute(Observer $observer)
    {
        $orderItem = $observer->getEvent()->getData("orderItems");
        $this->erpOrderItem = $observer->getEvent()->getData("orderItemsObj");
        $this->parentSku = null;
        $this->parentType = null;
        $component = $this->dataHelper->getComponent();
        if ($orderItem['product_type'] == 'configurable') {
            $itemId = $orderItem['product_id'];
            $this->parentSku = $this->getSkuById($itemId);
            $this->erpOrderItem->productEntity['sku'] = $this->parentSku;
            if ($component == 'D365FO' && isset($orderItem['product_options'])) {
                $this->setProductAttributes($orderItem);
            }
        }
        
        $this->setProductDiscount($orderItem);
        //@Hrusikesh Get Variant Id By Product SKU. Only Work For AX
        $component = $this->dataHelper->getComponent();
        $compArr = ['AX', 'D365FO'];
        if (in_array($component, $compArr) && $this->parentSku != null) {
            $productVariantId = $this->getVariantIdBySku($orderItem->getSku());
            $this->erpOrderItem->productEntity['variantId'] = $productVariantId;
        }

        $this->setProductAttributesInfo($orderItem, $component);
    }

    /**
     * Set product attribute info
     *
     * @param object $orderItem
     * @param string $component
     */
    private function setProductAttributesInfo($orderItem, $component)
    {
        if ($component == 'D365FO' && $this->parentSku != null) {
            $prodOptions = $orderItem->getProductOptions();
            $attributesInfo = [];
            if (isset($prodOptions['info_buyRequest']) && isset($prodOptions['info_buyRequest']['super_attribute'])) {
                foreach ($prodOptions['info_buyRequest']['super_attribute'] as $attr => $val) {
                    $attribute = $this->eavAttrModel->load($attr);
                    $attributeCode = $attribute->getAttributeCode();
                    $optionText = $attribute->getSource()->getOptionText($val);
                    $attributesInfo[] = [
                        "attributeCode" => $attributeCode,
                        "attributeValue" => $optionText
                    ];
                }
            }

            $this->erpOrderItem->productEntity['attributes'] = $attributesInfo;
        }
    }

    /**
     * Set product discount
     *
     * @param object $orderItem
     */
    private function setProductDiscount($orderItem)
    {
        $parentPrice = 0;
        if (is_object($orderItem->getParentItem()) &&
            $orderItem->getParentItem()->getProductType() == 'configurable') {
            $parentItem = $orderItem->getParentItem();
            $parentProductId = $parentItem->getProductId();
            $this->parentSku = $this->getSkuById($parentProductId);
            $this->parentType = $orderItem->getParentItem()->getProductType();
            $parentPrice = $parentItem->getBaseOriginalPrice();
            $parentSpecialPrice = $parentItem->getBasePrice();
            $this->erpOrderItem->productEntity['price'] = $parentPrice;
            $this->erpOrderItem->productEntity['specialPrice'] = $parentSpecialPrice;
            $this->erpOrderItem->productEntity['itemOptions'] = $this->getProductOptions($parentItem->getProductOptions());
                
            $discountEntity = [];
            //Discount entity field changed from discountAmount to discount and removed discount field from entity
            $this->erpOrderItem->productEntity[self::DISCOUNT] = [];
            // @updatedBy Arushi Bansal - changed as string value as issue faced in NAV
            $discountEntity['discountAmount'] = (string)(abs($parentItem->getBaseDiscountAmount()));
            $discountEntity['discountType'] = self::DISCOUNT;
            $this->erpOrderItem->productEntity[self::DISCOUNT][] = $discountEntity;
            $this->erpOrderItem->productEntity['parentSku'] = $this->parentSku;
            $this->erpOrderItem->productEntity['parentType'] = $this->parentType;
        }
    }

    /**
     * Set product attribute
     *
     * @param object $orderItem
     */
    private function setProductAttributes($orderItem)
    {
        $productOptions = $orderItem['product_options'];
        $attributesInfo = [];
        if (isset($productOptions['attributes_info']) && !empty($productOptions['attributes_info'])) {
            foreach ($productOptions['attributes_info'] as $key => $attributeInfo) {
                $attributesInfo[$key]['attributeCode'] =
                    isset($attributeInfo['label']) ? $attributeInfo['label'] : '';
                $attributesInfo[$key]['attributeValue'] =
                    isset($attributeInfo['value']) ? $attributeInfo['value'] : '';
            }
            $this->erpOrderItem->productEntity['attributes'] = $attributesInfo;
        }
    }

    /**
     * Get product sku by id
     *
     * @param int $id
     * @return string
     * @author Divya Koona.
     */
    private function getSkuById($id)
    {
        try {
            $result = $this->productRepo->getById($id);
            return $result->getSku();
        } catch (NoSuchEntityException $ex) {
            throw new NoSuchEntityException($ex->getMessage());
        } catch (LocalizedException $ex) {
            throw new LocalizedException($ex->getMessage());
        }
    }

    /**
     * Get product variant Id from SKU. Only for AX
     *
     * @param type $sku
     * @return text
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @author Hrusikesh Manna
     */
    private function getVariantIdBySku($sku)
    {
        try {
            $product = $this->productRepo->get($sku);
            if ($product->getvariantId()) {
                return $product->getvariantId();
            }
            return null;
        } catch (NoSuchEntityException $ex) {
            throw new NoSuchEntityException($ex->getMessage());
        } catch (LocalizedException $ex) {
            throw new LocalizedException($ex->getMessage());
        }
    }
     public function getProductOptions($options)
    {
       $productOptions = $options; 

        $options = [];

        if (isset($productOptions['options']) && is_array($productOptions['options'])) {
            foreach ($productOptions['options'] as $option) {
                $label = $option['label'] ?? '';
                $value = $option['value'] ?? '';
                $options[] = [
                    'label' => $label,
                    'value' => $value
                ];
               
            }
        }
        return $options;
    }
}
