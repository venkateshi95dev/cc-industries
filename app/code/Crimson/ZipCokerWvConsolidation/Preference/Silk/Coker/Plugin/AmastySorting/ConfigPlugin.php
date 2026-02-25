<?php
namespace Crimson\ZipCokerWvConsolidation\Preference\Silk\Coker\Plugin\AmastySorting;

use Crimson\ZipCokerWvConsolidation\Model\Config;
use Magento\Store\Model\StoreManagerInterface;

class ConfigPlugin
{
    public function __construct(
        private readonly StoreManagerInterface $storeManager
    )
    {
    }

    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $config, $options)
    {
        if ($this->storeManager->getWebsite()->getCode() == Config::ZIP_WEBSITE_CODE) {
            return $options;
        }
        $options['relevance'] = __('Relevance');
        return $options;
    }
}

