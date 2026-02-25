<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Crimson\CokerWV\Api\WVStoreInterface;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ConfigForMagestoreMageplazaExt implements DataPatchInterface
{
    const BANNERSLIDER_PATH = 'bannerslider/general/enable_frontend';
    const BETTERPOPUP_PATH = 'betterpopup/general/enabled';
    const AJAXCART_PATH = 'ajaxcart/general/active';

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

        $this->configWriter->save(self::BANNERSLIDER_PATH, 0, 'default', 0);
        $this->configWriter->save(self::BANNERSLIDER_PATH, 1, 'websites', $cokerWebsiteId);
        $this->configWriter->save(self::BANNERSLIDER_PATH, 1, 'websites', $WVWebsiteId);

        $this->configWriter->save(self::BETTERPOPUP_PATH, 0, 'default', 0);
        $this->configWriter->save(self::AJAXCART_PATH, 0, 'default', 0);
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
