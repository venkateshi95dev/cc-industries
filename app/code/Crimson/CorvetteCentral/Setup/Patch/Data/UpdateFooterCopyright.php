<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Psr\Log\LoggerInterface;

class UpdateFooterCopyright implements DataPatchInterface
{
    private const XML_PATH_COPYRIGHT = 'design/footer/copyright';

    private const COPYRIGHT_TEXT = '© Copyright 1975-{YYYY} Corvette Central. All rights reserved.';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly WriterInterface $configWriter,
        private readonly StoreRepositoryInterface $storeRepository,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @inheritDoc
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();

        try {
            $store = $this->storeRepository->get(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE);
            $storeId = (int)$store->getId();

            $this->configWriter->save(
                self::XML_PATH_COPYRIGHT,
                self::COPYRIGHT_TEXT,
                'stores',
                $storeId
            );
        } catch (\Exception $e) {
            $this->logger->error('Error updating footer copyright: ' . $e->getMessage());
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [
            CreateCorvetteCentralWebiste::class
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
