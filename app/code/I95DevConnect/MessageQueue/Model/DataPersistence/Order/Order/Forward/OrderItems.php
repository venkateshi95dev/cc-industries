<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward;

use I95DevConnect\MessageQueue\Helper\Generic;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class responsible for preparing order item data which will be added in order result to ERP
 * @createdBy Sravani Polu
 */
class OrderItems
{
    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var Generic
     */
    public $generic;

    /**
     * @var object
     */
    public $productEntity;

    /**
     *
     * @param Manager $eventManager
     * @param Generic $generic
     */
    public function __construct(
        Manager $eventManager,
        Generic $generic
    ) {
        $this->eventManager = $eventManager;
        $this->generic = $generic;
    }

    /**
     * Preparation of Order Item Entity
     *
     * @param array $order
     * @return array
     * @throws LocalizedException
     * @createdBy Sravani Polu
     */
    public function getOrderItemEntities($order)
    {
        $orderItemsData = [];
        try {
            $orderItems = $order->getItems();
            $supportedProductType = $this->generic->getSupportedProductTypesForOrder();
            foreach ($orderItems as $item) {
                $this->productEntity = [];
                if (!in_array($item->getProductType(), $supportedProductType)) {
                    //@author Divya Koona. Exception message string concatenation changed.
                    throw new LocalizedException(
                        __("i95dev_unsupported_product %1", $item->getProductType()),
                        null,
                        104
                    );
                }

                $this->productEntity['sku'] = $item->getSku();
                $this->productEntity['itemId'] = $item->getItemId();
                $this->productEntity['typeId'] = $item->getProductType();
                $this->productEntity['price'] = (float)$item->getBaseOriginalPrice();
                $this->productEntity['qty'] = (int)$item->getQtyOrdered();
                $this->productEntity['itemTaxAmount'] = (float)$item->getBaseTaxAmount();
                $this->productEntity['taxPercent'] = (float)$item->getTaxPercent();
                $this->productEntity['specialPrice'] = (float)$item->getBasePrice();

                // @updatedBy Arushi B - converted discount to string to fix discount not applying issue
                $discountEntity['discountAmount'] = (float)(abs($item->getBaseDiscountAmount()));
                $discountEntity['discountType'] = 'discount';
                $this->productEntity['discount'][] = $discountEntity;
                // sending product options
                $this->productEntity['itemOptions'] = $this->getProductOptions($item->getProductOptions());
                $this->eventManager->dispatch(
                    "erpconnect_forward_orderproductinfo",
                    ['orderItems' => $item, 'orderItemsObj' => $this]
                );
                $orderItemsData[] = $this->productEntity;
            }

            return $orderItemsData;
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
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
