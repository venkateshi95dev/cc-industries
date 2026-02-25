<?php
/**
 * Copyright © Crimson. All rights reserved.
 */
declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Updates the transactional email logo for Corvette Central store view
 */
class UpdateTransactionalEmailLogo implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly StoreManagerInterface    $storeManager,
        private readonly WriterInterface $configWriter
    ) {}

    /**
     * {@inheritdoc}
     * @throws NoSuchEntityException
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();

        $storeId = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();

        if ($storeId) {
            $logoFilename = 'corvette_central_logo.png';
            $logoPath = "stores/{$storeId}/{$logoFilename}";

            $this->configWriter->save(
                'design/email/logo',
                $logoPath,
                ScopeInterface::SCOPE_STORES,
                $storeId
            );
            
            $this->configWriter->save(
                'design/email/logo_alt',
                'Corvette Central',
                ScopeInterface::SCOPE_STORES,
                $storeId
            );
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
