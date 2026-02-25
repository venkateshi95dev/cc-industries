<?php
declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\State;

class UpdateGiftCardChildStock implements DataPatchInterface
{

    public function __construct(
        private State                       $appState,
        private ModuleDataSetupInterface    $moduleDataSetup,
        private ProductRepositoryInterface  $productRepository,
        private Configurable                $configurable,
        private StockRegistryInterface      $stockRegistry
    )
    {
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        try {
            $this->appState->setAreaCode('adminhtml');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
        }
        try {
            // Load configurable product by SKU
            $configurableProduct = $this->productRepository->get('100000');

            // Get child product IDs
            $childIds = $this->configurable->getChildrenIds($configurableProduct->getId())[0] ?? [];

            foreach ($childIds as $childId) {
                $child = $this->productRepository->getById($childId);

                // Update stock item
                $stockItem = $this->stockRegistry->getStockItem($child->getId());
                $stockItem->setUseConfigManageStock(false);
                $stockItem->setManageStock(false); // manage_stock = No
                $stockItem->setIsInStock(true);    // stock status = In Stock
                $this->stockRegistry->updateStockItemBySku($child->getSku(), $stockItem);
                $this->productRepository->save($child);
            }
        } catch (NoSuchEntityException $e) {
        } finally {
            $this->moduleDataSetup->getConnection()->endSetup();
        }

        return $this;
    }

    public
    static function getDependencies(): array
    {
        return [];
    }

    public
    function getAliases(): array
    {
        return [];
    }
}
