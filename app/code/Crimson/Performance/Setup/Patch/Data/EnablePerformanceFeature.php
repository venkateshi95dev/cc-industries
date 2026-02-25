<?php

declare(strict_types=1);

namespace Crimson\Performance\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;

class EnablePerformanceFeature implements DataPatchInterface
{
    public function __construct(
        private WriterInterface $configWriter,
        private StoreManagerInterface $storeManager
    ) {}

    public function apply(): void
    {
        try {
            $website = $this->storeManager->getWebsite(
                CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_CODE
            );
        } catch (NoSuchEntityException $e) {
            return;
        }

        $websiteId = (int) $website->getId();

        $this->configWriter->save(
            'dev/js/enable_magepack_js_bundling',
            1,
            'websites',
            $websiteId
        );

        $this->configWriter->save(
            'performance/server_push/enabled',
            1,
            'websites',
            $websiteId
        );
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
