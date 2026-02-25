<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/14/2019 12:18 PM
 * @brief
 */

declare(strict_types=1);

namespace Crimson\MachCatalog\Model\ResourceModel;

use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCatalog\Model\Api\Inventory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class GetStockItemData
 * @package Crimson\MachCatalog\Model\ResourceModel
 */
class GetStockItemData extends \Magento\InventoryIndexer\Model\ResourceModel\GetStockItemData
{

    public function __construct(
        ResourceConnection $resource,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        protected Inventory $inventoryApi,
        protected MachConfig $machConfig,
        protected RequestInterface $request,
        protected HealthCheck $healthCheck,
        protected StoreManagerInterface $storeManager,
        DefaultStockProviderInterface $defaultStockProvider,
        GetProductIdsBySkusInterface $getProductIdsBySkus,
    ) {
        parent::__construct($resource, $stockIndexTableNameResolver, $defaultStockProvider, $getProductIdsBySkus);
    }

    public function execute(string $sku, int $stockId, $forceLocalInventory = false): ?array
    {
        $webSite = $this->storeManager->getWebsite();
        if ($webSite->getCode() != MachConfig::ZIP_WEBSITE_CODE) {
            return parent::execute($sku, $stockId);
        }

        if ($forceLocalInventory || !$this->machConfig->isEnabled($webSite->getId()) || !$this->_shouldProcessAgainstApi() || !$this->healthCheck->isUp()) {
            return parent::execute($sku, $stockId);
        }

        $result = $this->inventoryApi->get($sku);
        if ($result->getQtyAvailable() !== false) {
            return [
                GetStockItemDataInterface::QUANTITY   => $result->getQtyAvailable(),
                GetStockItemDataInterface::IS_SALABLE => 1
            ];
        } else {
            return parent::execute($sku, $stockId);
        }
    }

    /**
     * We are now calling Mach only on checkout sections: Cart or Checkout
     *
     * @return bool
     */
    protected function _shouldProcessAgainstApi(): bool
    {
        if ($this->request->isPost()) {
            return true;
        }

        if (method_exists($this->request, 'getRouteName')
            && $this->request->getRouteName() === 'checkout'
        ) {
            return true;
        }

        return false;
    }
}
