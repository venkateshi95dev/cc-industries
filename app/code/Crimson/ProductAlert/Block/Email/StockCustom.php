<?php

namespace Crimson\ProductAlert\Block\Email;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\Filter\Input\MaliciousCode;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\ProductAlert\Block\Email\Stock;

class StockCustom extends Stock
{

    protected $_template = 'Crimson_ProductAlert::email/stock.phtml';

    public function __construct(
        Context                $context,
        MaliciousCode          $maliciousCode,
        PriceCurrencyInterface $priceCurrency,
        protected Configurable $configurableProduct,
        protected ProductRepositoryInterface $productRepositoryInterface,
        ImageBuilder           $imageBuilder,
        array                  $data = []
    )
    {
        parent::__construct($context, $maliciousCode, $priceCurrency, $imageBuilder, $data);
    }

    public function isProductAPossibleChild(Product $product): bool
    {
        return $product->getTypeId() === Type::TYPE_SIMPLE && $product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE;
    }

    public function getProductForImageAndUrl(Product $product): ProductInterface|Product
    {
        if (!$this->isProductAPossibleChild($product)) {
            return $product;
        }

        try {
            $configurableProducts = $this->configurableProduct->getParentIdsByChild($product->getId());

            return isset($configurableProducts[0]) ? $this->productRepositoryInterface->getById($configurableProducts[0]) : $product;
        } catch (\Exception $e) {
            return $product;
        }
    }
}
