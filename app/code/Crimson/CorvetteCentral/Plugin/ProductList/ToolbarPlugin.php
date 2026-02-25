<?php
namespace Crimson\CorvetteCentral\Plugin\ProductList;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Store\Model\StoreManager;

class ToolbarPlugin
{
    public function __construct(
        private StoreManager $storeManager)
    {
    }
    /**
     * Remove “dropshipped” and “primarybin” from the array of sort orders.
     *
     * @param Toolbar $subject
     * @param array   $availableOrders
     * @return array
     */
    public function afterGetAvailableOrders(
        Toolbar $subject,
        array $availableOrders
    ): array {
        $CCStoreId = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();
        if ($this->storeManager->getStore()->getId()!==$CCStoreId) {
            return $availableOrders;
        }
        unset(
            $availableOrders['dropshipped'],
            $availableOrders['primarybin']
        );
        return $availableOrders;
    }
}
