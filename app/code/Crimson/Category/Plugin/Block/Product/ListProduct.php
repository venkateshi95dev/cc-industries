<?php

namespace Crimson\Category\Plugin\Block\Product;

use Crimson\Category\Block\Category\Sections\MostViewed;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Helper\Data;


/**
 * Class ListProduct
 * @package Crimson\Category\Plugin\Block\Product
 */
class ListProduct
{
    /**
     * @var MostViewed
     */
    protected $listProductBlock;

    /**
     * @var Configurable
     */
    protected $configurableProduct;

    /**
     * @var Data
     */
    protected $pricingHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;


    public function __construct(
        Configurable $configurableProduct,
        Data $pricingHelper,
        MostViewed $listProductBlock,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->configurableProduct = $configurableProduct;
        $this->pricingHelper = $pricingHelper;
        $this->listProductBlock = $listProductBlock;
        $this->logger = $logger;
    }


    public function aroundGetProductPrice(
        MostViewed $subject,
        \Closure $proceed,
        Product $product
    ) {

        if (Configurable::TYPE_CODE !== $product->getTypeId()) {
            return $proceed($product);
        }

        return $this->getPriceRange($product);
    }

    /**
     * @param $product
     * @return mixed
     * @throws LocalizedException
     */
    public function getPriceRange($product)
    {
        $childProductPrice = [];
        $childProducts = $this->configurableProduct->getUsedProducts($product);
        foreach($childProducts as $child) {
            $price = number_format($child->getPrice(), 2, '.', '');
            $finalPrice = number_format($child->getFinalPrice(), 2, '.', '');
            if($price == $finalPrice) {
                $childProductPrice[] = $price;
            } else if($finalPrice < $price) {
                $childProductPrice[] = $finalPrice;
            }
        }

        $max = $this->pricingHelper->currencyByStore(max($childProductPrice));
        $min = $this->pricingHelper->currencyByStore(min($childProductPrice));

        if($min == $max){
            return $this->getPriceRender($product, "$min", '');
        } else {
            return $this->getPriceRender($product, "$min-$max", '');
        }
    }

    /**
     * @param $product
     * @param $price
     * @param string $text
     * @return mixed
     * @throws LocalizedException
     */
    protected function getPriceRender($product, $price, $text = '')
    {
        return $this->listProductBlock->getLayout()->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('Crimson_Category::product/price/range/price.phtml')
            ->setData('price_id', 'product-price-'.$product->getId())
            ->setData('display_label', $text)
            ->setData('product_id', $product->getId())
            ->setData('display_value', $price)->toHtml();
    }

}
