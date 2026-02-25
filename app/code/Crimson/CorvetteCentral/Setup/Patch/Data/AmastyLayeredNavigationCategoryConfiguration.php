<?php

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class AmastyLayeredNavigationCategoryConfiguration implements DataPatchInterface
{
    const AMASTY_CATEGORY_CONFIG_PATH = 'amshopby/category_filter/enabled';

    public function __construct(
        private readonly WriterInterface $configWriter,
        private readonly StoreManagerInterface $storeManager
    )
    {
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        try {
            $ccWebsiteId = $this->storeManager->getWebsite(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_CODE)->getId();
            $this->configWriter->save(self::AMASTY_CATEGORY_CONFIG_PATH, '1', ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::AMASTY_CATEGORY_CONFIG_PATH, '1', ScopeInterface::SCOPE_WEBSITE, $ccWebsiteId);
        } catch (\Exception $exception) {
            return;
        }
    }


    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [
            CreateCorvetteCentralWebiste::class
        ];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
