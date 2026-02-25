<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class StandardizeProductListingOptions implements DataPatchInterface
{
    private const CONFIG_GRID_VALUES_PATH = 'catalog/frontend/grid_per_page_values';
    private const CONFIG_GRID_DEFAULT_PATH = 'catalog/frontend/grid_per_page';
    private const CONFIG_LIST_VALUES_PATH = 'catalog/frontend/list_per_page_values';
    private const CONFIG_LIST_DEFAULT_PATH = 'catalog/frontend/list_per_page';

    private const PRODUCT_PER_PAGE_VALUES = '12,24,36';
    private const PRODUCT_PER_PAGE_DEFAULT = '12';

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface $configWriter
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly WriterInterface $configWriter,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();

        // Get store ID from the store code
        $storeId = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();

        // Update grid and list per page values for Corvette Central store view
        $this->configWriter->save(
            self::CONFIG_GRID_VALUES_PATH,
            self::PRODUCT_PER_PAGE_VALUES,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );

        $this->configWriter->save(
            self::CONFIG_GRID_DEFAULT_PATH,
            self::PRODUCT_PER_PAGE_DEFAULT,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );

        $this->configWriter->save(
            self::CONFIG_LIST_VALUES_PATH,
            self::PRODUCT_PER_PAGE_VALUES,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );

        $this->configWriter->save(
            self::CONFIG_LIST_DEFAULT_PATH,
            self::PRODUCT_PER_PAGE_DEFAULT,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
