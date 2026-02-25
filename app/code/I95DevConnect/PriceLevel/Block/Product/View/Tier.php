<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Block\Product\View;

use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use I95DevConnect\PriceLevel\Model\ItemPrice as ItemPrice;
use I95DevConnect\PriceLevel\Helper\Data;
use Magento\Framework\View\Element\Template\Context;

/**
 * Product Price List
 * @api
 */
class Tier extends Template
{
    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     *
     * @var ItemPrice
     */
    public $itemPrice;

    /**
     *
     * @var Session
     */
    public $customerSessionFactory;

    /**
     * @var Data
     */
    public $helper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Session $customerSession
     * @param ItemPrice $itemPrice
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Session $customerSession,
        ItemPrice $itemPrice,
        Data $helper,
        array $data = []
    ) {

        $this->coreRegistry = $registry;
        $this->itemPrice = $itemPrice;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get current product sku
     *
     * @return null|string
     */
    public function getProductSku()
    {
        $product = $this->coreRegistry->registry('product');
        return $product ? $product->getSku() : null;
    }

    /**
     * Get product's price levels list
     *
     * @return array
     */
    public function getProductPriceList()
    {
        $finalPrices = [];
        $tierQty = [];
        $customerSession = $this->customerSession;
        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getCustomerId();
            $customer = $this->itemPrice->getCustomer($customerId);
            $SKU = $this->getProductSku();
            $parentSkus = $this->helper->getParentIdsByChildSku($SKU);
            if (!empty($parentSkus)) {
                $parentSku = isset($parentSkus[0]) ? $parentSkus[0] : '';
                $tierPricesCollection = $this->itemPrice
                    ->getItemPriceList($customer['target_customer_id'], $customer['pricelevel'], $parentSku)
                    ->setOrder('qty', 'ASC')->setOrder('price', 'ASC')->getData();
            } else {
                $tierPricesCollection = $this->itemPrice
                    ->getItemPriceList($customer['target_customer_id'], $customer['pricelevel'], $SKU)
                    ->setOrder('qty', 'ASC')->setOrder('price', 'ASC')->getData();
            }
            foreach ($tierPricesCollection as $tierPrices) {
                if (!in_array($tierPrices['qty'], $tierQty)) {
                    $tierQty[] = $tierPrices['qty'];
                    $tier = [];
                    $tier['qty'] = $tierPrices['qty'];
                    $tier['price'] = $this->itemPrice
                        ->getItemTierPrice(
                            $customer['target_customer_id'],
                            $customer['pricelevel'],
                            $SKU,
                            $tierPrices['qty']
                        );
                    $finalPrices[] = $tier;
                }
            }
        }
        return $finalPrices;
    }

    /**
     * Get product final price
     *
     * @return float
     */
    public function getFinalPrice()
    {
        /** @var float $productPrice is a minimal available price */
        return $this->coreRegistry->registry('product')->getFinalPrice();
    }

    /**
     * Get save percentage from price
     *
     * @param float $price
     * @return float
     */
    public function getSavePercent($price)
    {
        $savePercent = 0;
        $finalPrice = $this->getFinalPrice();
        if ($finalPrice !== 0) {
            $discount = $this->getFinalPrice() - $price;
            $savePercent = ($discount * 100) / $this->getFinalPrice();
        }
        return ceil($savePercent);
    }
}
