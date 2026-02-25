<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/27/2019 12:45 PM
 * @brief
 */

namespace Crimson\MachOrder\Model\Api\Build;

use Crimson\MachBase\Model\Api\AbstractApi;
use Crimson\MachBase\Model\Api\ApiContext;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachOrder\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Sales\Model\Order\Item;

/**
 * Class OrderItem
 * @package Crimson\MachOrder\Model\Api\Build
 */
class OrderItem extends AbstractApi
{
    CONST DEFAULT_DROPSHIP_FIELD_VALUE = "#";
    CONST DROPSHIP_ITEM_FIELD_VALUE    = "D";

    /**
     * @var Product
     */
    protected $productResource;

    public function __construct(
        ApiContext $apiContext,
        MachConfig $machConfig,
        Subscriber $subscriberResource,
        Product $productResource,
    ) {
        parent::__construct($apiContext, $machConfig, $subscriberResource);
        $this->productResource = $productResource;
    }

    /**
     * @param Item $orderItem
     *
     * @return array
     * @throws LocalizedException
     */
    public function build(Item $orderItem): array
    {
        $sku   = $this->getMachSku($orderItem->getSku());
        $price = $this->_calculatePrice($orderItem);

        // Adds extra lines to the item description; one per order item custom option
        $name   = array();
        $name[] = $orderItem->getName();
        //if ($orderItem->getData('has_options') ){
        $options = $orderItem->getProductOptions();
        $options = isset($options['options']) ? $options['options'] : false;
        if ($options) {
            foreach ($options as $option) {
                if (isset($option['label']) && isset($option['value'])) {
                    $name[] = sprintf("%s: %s ", $option['label'], $option['value']);
                }
            }
        }
        //}

        //ZIP-994 We use the correct field to specify the dropship info on the Order Item, the field "DropShip"
        //is the one te be used, when an item is dropship we assign "D" excepting international orders, other cases
        //we assign "#"
        $dropshipField = $this->_calculateDropShipField($orderItem);

        $orderItemData = array(
            $this->_soapVar($sku, 'ItemNumber'),
            $this->_soapVar($orderItem->getQtyOrdered(), 'QtyOrdered'),

            //# most always
            $this->_soapVar('#', 'ItemType'),

            //"#" or "D", "#" default value, "D" only dropship items(no international orders)
            $this->_soapVar($dropshipField, 'DropShip'),

            //Price should be the price of a single item.
            $this->_soapVar($price, 'Price'),
            //what is 'CatalogCode'? A: Normally left blank , but could be used to indicate the current catalog that item appears in.
            $this->_soapVar(null, 'CatalogCode'),

        );

        foreach ($name as $itemDesc) {
            $line            = array($this->_soapVar($itemDesc, 'ItemDesc'));
            $orderItemData[] = $this->_soapVar($line, 'DescLine');
        }

        return $orderItemData;
    }

    /**
     * @param Item $orderItem
     * @return string
     */
    protected function _calculateDropShipField(Item $orderItem): string
    {
        $dropshipField  = self::DEFAULT_DROPSHIP_FIELD_VALUE;
        //preventing errors when Order has core charge items
        if (!$orderItem->getId() || !$orderItem->getOrderId()) {
            return $dropshipField;
        }

        $shippingMethod = $orderItem->getOrder()->getShippingMethod();
        if ($shippingMethod == Config::ATYPICAL_REGIONS_SHIPPING_METHOD_CODE) {
            return $dropshipField;
        } else {
            $product       = $orderItem->getProduct();
            if ($product && $product->getId()) {
                $dropship = ($product->getCustomAttribute('ships_from_manufacturer')
                    ? (bool) $product->getCustomAttribute('ships_from_manufacturer')->getValue() : false);
                if($dropship) {
                    $dropshipField = self::DROPSHIP_ITEM_FIELD_VALUE;
                }
            }

            return $dropshipField;
        }
    }

    /**
     * @param Item $orderItem
     * @return float
     */
    protected function _calculatePrice(Item $orderItem): float
    {
        if ($orderItem->getParentItem() && $orderItem->getParentItem()->getProductType() == Configurable::TYPE_CODE) {
            $rowTotal = $orderItem->getParentItem()->getBaseRowTotal();
            $discountAmount = $orderItem->getParentItem()->getBaseDiscountAmount();
            $qtyOrdered = $orderItem->getParentItem()->getQtyOrdered();
        } else {
            $rowTotal = $orderItem->getBaseRowTotal();
            $discountAmount = $orderItem->getBaseDiscountAmount();
            $qtyOrdered = $orderItem->getQtyOrdered();
        }

        //We stop adding core charge to item price when it is a KIT
        return ($rowTotal - $discountAmount) / $qtyOrdered;
    }
}
