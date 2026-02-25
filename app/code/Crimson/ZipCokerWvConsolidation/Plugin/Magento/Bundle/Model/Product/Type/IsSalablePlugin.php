<?php
namespace Crimson\ZipCokerWvConsolidation\Plugin\Magento\Bundle\Model\Product\Type;

use Crimson\ZipCokerWvConsolidation\Model\Config;

class IsSalablePlugin
{
    public function aroundIsSalable(
        \Magento\Bundle\Model\Product\Type $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        if (Config::ZIP_WEBSITE_CODE == $product->getStore()->getWebsite()->getCode()) {
            return $proceed($product);
        }
        return true;
    }
}
