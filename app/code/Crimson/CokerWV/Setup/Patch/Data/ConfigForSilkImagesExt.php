<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Crimson\CokerWV\Api\WVStoreInterface;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ConfigForSilkImagesExt implements DataPatchInterface
{
    const SILK_IMAGE_ENABLE_PATH = 'silk_image/general/enable';

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

        $this->configWriter->save(self::SILK_IMAGE_ENABLE_PATH, 0, 'default', 0);
        $this->configWriter->save(self::SILK_IMAGE_ENABLE_PATH, 1, 'websites', $cokerWebsiteId);
        $this->configWriter->save(self::SILK_IMAGE_ENABLE_PATH, 1, 'websites', $WVWebsiteId);
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
