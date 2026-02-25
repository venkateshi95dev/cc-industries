<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Crimson\CokerWV\Api\WVStoreInterface;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ConfigForAmastyShopby implements DataPatchInterface
{
    const AMASTY_SHOPBY_BRAND_PATH = 'amshopby_brand/general/attribute_code';

    public function __construct(
        private readonly \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        private readonly \Magento\Store\Model\StoreManagerInterface            $storeManager
    )
    {
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        $cokerWebsiteId = $this->storeManager->getWebsite(CokerStoreInterface::COKER_WEBSITE_CODE)->getId();
        $WVWebsiteId = $this->storeManager->getWebsite(WVStoreInterface::WV_WEBSITE_CODE)->getId();
        $zipWebsiteId = $this->storeManager->getWebsite(\Crimson\ZipCokerWvConsolidation\Model\Config::ZIP_WEBSITE_CODE)->getId();

        $this->configWriter->save(self::AMASTY_SHOPBY_BRAND_PATH, null, 'default', 0);
        $this->configWriter->save(self::AMASTY_SHOPBY_BRAND_PATH, null, 'websites', $zipWebsiteId);
        $this->configWriter->save(self::AMASTY_SHOPBY_BRAND_PATH, 'brand_item', 'websites', $cokerWebsiteId);
        $this->configWriter->save(self::AMASTY_SHOPBY_BRAND_PATH, 'brand_item', 'websites', $WVWebsiteId);
    }


    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [
            CreateCokerWebiste::class,
            CreateWVWebiste::class
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
