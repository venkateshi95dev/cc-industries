<?php

namespace Crimson\Shipping\Model;

use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCatalog\Model\Service\GetProductStockInfo;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Crimson\Shipping\Model
 */
class Config
{
    const XPATH_ENABLE_CART_SIDEBAR_TAX_MESSAGE = 'shipping/cart_sidebar_messaging/enable_cart_sidebar_tax_message';
    const XPATH_CART_SIDEBAR_TAX_MESSAGE = 'shipping/cart_sidebar_messaging/cart_sidebar_tax_message';
    const XPATH_ENABLE_CART_SIDEBAR_SHIPPING_UPDATE = 'shipping/cart_sidebar_messaging/enable_cart_sidebar_shipping_update';
    const XPATH_CART_SIDEBAR_SHIPPING_UPDATE = 'shipping/cart_sidebar_messaging/cart_sidebar_shipping_update';
    protected Escaper $escaper;

    /** @var HealthCheck */
    protected $healthCheck;

    /** @var MachConfig */
    protected $machconfig;

    /** @var GetProductStockInfo */
    protected $getProductStockInfo;

    /** @var ProductRepositoryInterface */
    protected $_productRepository;
    protected ScopeConfigInterface $scopeConfig;

    public function __construct(
        HealthCheck $healthCheck,
        MachConfig $machconfig,
        GetProductStockInfo $getProductStockInfo,
        ProductRepositoryInterface $productRepository,
        ScopeConfigInterface $scopeConfig,
        Escaper $escaper
    ) {
        $this->healthCheck = $healthCheck;
        $this->machconfig = $machconfig;
        $this->getProductStockInfo = $getProductStockInfo;
        $this->_productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        $this->escaper = $escaper;
    }

    /**
     * @return bool
     */
    public function getCanCallMach(): bool
    {
        $result = true;
        if (!$this->machconfig->isEnabled() || !$this->healthCheck->isUp()) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param $item
     * @param $qtyFromMach
     * @return bool
     */
    public function isPartialAvailability($item, $qtyFromMach): bool
    {
        $qtyRequested = $this->getProductQty($item);
        $result = false;
        if ($qtyRequested - $qtyFromMach > 0) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param $item
     * @return int
     */
    public function getProductQty($item): int
    {
        $qty = 0;
        $productId = $item->getProductId();
        if (!$productId) {
            return $qty;
        }

        $children = $item->getChildren();
        if ($children) {
            foreach ($children as $childItem) {
                $qty += $this->getProductQty($childItem);
            }

            return $qty;
        } else {
            return $item->getTotalQty();
        }
    }

    /**
     * @param $item
     * @return bool
     */
    public function isPartialBackorderMagentoStock($item): bool
    {
        $productId = $item->getProduct()->getId();
        try {
            $_product = $this->_productRepository->getById($productId);
        } catch (\Exception $e) {
            return false;
        }

        if ($_product && $_product->getId()) {
            try {
                //Forcing to get the info Magento
                $stockInfo = $this->getProductStockInfo->get($_product, $item->getStoreId(), true);
            } catch (\Exception $exception) {
                return false;
            }

            if (!$stockInfo) {
                return false;
            }

            //checking backorder and partial
            if (isset($stockInfo[GetProductStockInfo::QTY_CODE])) {

                if ((int)$stockInfo[GetProductStockInfo::QTY_CODE] <= 0) {
                    return true;
                }

                $qtyRequested = $this->getProductQty($item);
                if ($qtyRequested - $stockInfo[GetProductStockInfo::QTY_CODE] > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isCartSidebarTaxMessageEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_ENABLE_CART_SIDEBAR_TAX_MESSAGE, ScopeInterface::SCOPE_STORES);
    }

    public function getCartSidebarTaxMessage(bool $escape = true): ?string
    {
        $message = $this->scopeConfig->getValue(self::XPATH_CART_SIDEBAR_TAX_MESSAGE, ScopeInterface::SCOPE_STORES);
        if (!$message) {
            return null;
        }

        if (!$escape) {
            return $message;
        }

        return $this->escaper->escapeHtml($message, ['span', 'strong', 'br', 'ul', 'ol', 'li', 'p', 'div']);
    }

    public function isCartSidebarShippingUpdateEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_ENABLE_CART_SIDEBAR_SHIPPING_UPDATE, ScopeInterface::SCOPE_STORES);
    }

    public function getCartSidebarShippingUpdate(bool $escape = true): ?string
    {
        $message = $this->scopeConfig->getValue(self::XPATH_CART_SIDEBAR_SHIPPING_UPDATE, ScopeInterface::SCOPE_STORES);
        if (!$message) {
            return null;
        }

        if (!$escape) {
            return $message;
        }

        return $this->escaper->escapeHtml($message, ['span', 'strong', 'br', 'ul', 'ol', 'li', 'p', 'div']);
    }
}
