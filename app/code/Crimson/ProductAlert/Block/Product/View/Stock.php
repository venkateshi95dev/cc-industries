<?php

namespace Crimson\ProductAlert\Block\Product\View;

use Crimson\Catalog\Service\ServiceProduct;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\ProductAlert\Block\Product\View;
use Magento\ProductAlert\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;


/**
 * Swaps out \Magento\ProductAlert\Block\Product\View\Stock, via layout catalog_product_view.xml
 */
class Stock extends View
{

    public function __construct(
        Context $context,
        Data $helper,
        Registry $registry,
        PostHelper $coreHelper,
        protected ServiceProduct $serviceProduct,
        protected HttpContext $httpContext,
        protected StockRegistryInterface $stockRegistry,
        protected readonly StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $helper, $registry, $coreHelper, $data);
    }

    /**
     * Prepare stock info
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        return parent::setTemplate($this->shouldRender() ? $template : '');
    }

    public function getSignupUrl(): string
    {
        return (string)$this->_helper->getSaveUrl('stock');
    }

    public function getBaseSignupUrl(): string
    {
        return (string)$this->_urlBuilder->getUrl('productalert/add/stock');
    }

    public function getUenc(): string
    {
        return (string)$this->_helper->getEncodedUrl();
    }

    public function getTypeId(): string
    {
        return (string)$this->_helper->getProduct()->getTypeId();
    }

    public function shouldRender(): bool
    {
        return (
            $this->_helper->isStockAlertAllowed() &&
            $this->getProduct() &&
            !$this->isBundle($this->getProduct()) &&
            !$this->getIsServiceProduct() &&
            !$this->isDropshipItem($this->getProduct()) &&
            !$this->customInStockCheck($this->getProduct())
        );
    }

    public function isBundle(Product $product): bool
    {
        return $product->getTypeId() === Bundle::TYPE_CODE;
    }

    public function isDropshipItem(Product $product): bool
    {
        return (int)$product->getData('ships_from_manufacturer') === 1;
    }

    public function customInStockCheck(Product $product): bool
    {
        $stockData = $product->getData('quantity_and_stock_status');
        return ($stockData['qty'] ?? 0) > 0;
    }

    public function getIsServiceProduct(): bool
    {
        return $this->serviceProduct->is($this->getProduct());
    }

    public function shouldRenderButton(): bool
    {
        $product = $this->getProduct();

        $websiteCode = $this->storeManager->getWebsite()->getCode();
        if ($websiteCode === "corvette_central_webiste") {
            $attributeValue = $product->getData('item_discount_group');

            if ($attributeValue && strtolower($attributeValue) === "disc") {
                return false;
            }
        }


        return !$product->isSalable() && !$this->hasBackorders($product);
    }

    protected function hasBackorders(Product $product): bool
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());

        if ((int)$stockItem->getUseConfigBackorders()) {
            $configValue = (int)$this->_scopeConfig->getValue(
                'cataloginventory/item/backorders',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            return $configValue !== 0;
        }

        return (int)$stockItem->getBackorders() !== 0;
    }
}
