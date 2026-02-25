<?php
namespace Crimson\ZipCokerWvConsolidation\Preference\Silk\Coker\Model\Product;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Crimson\ZipCokerWvConsolidation\Model\Config;
use Magento\Catalog\Model\Product\Url as OriginalClass;

class Url extends \Silk\Coker\Model\Product\Url
{
    public function getUrl(\Magento\Catalog\Model\Product $product, $params = [])
    {
        if (in_array($product->getStore()->getWebsite()->getCode(), [Config::ZIP_WEBSITE_CODE, CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_CODE])) {
            return OriginalClass::getUrl($product, $params);
        }

        return parent::getUrl($product, $params);
    }
}
