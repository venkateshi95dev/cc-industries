<?php
namespace Crimson\CorvetteCentral\Plugin\InventorySales\Model\IsProductSalableCondition;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\InventorySales\Model\IsProductSalableCondition\BackOrderNotifyCustomerCondition;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\ScopeInterface;

class BackOrderNotifyCustomerConditionPlugin
{

    public function __construct(
        private StoreManager $storeManager,
        private ProductRepositoryInterface $productRepository,
        private ProductSalableResultInterfaceFactory $productSalableResultFactory,
        private ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        private ScopeConfigInterface $scopeConfig
    )
    {
    }

    /**
     * After plugin for execute()
     *
     * @param BackOrderNotifyCustomerCondition $subject
     * @param ProductSalableResultInterface $result
     * @param string $sku
     * @param int $stockId
     * @return ProductSalableResultInterface
     */
    public function afterExecute(
        BackOrderNotifyCustomerCondition $subject,
        ProductSalableResultInterface $result,
                                         $sku,
                                         $stockId
    ) {

        $CCStoreId = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();
        if ($this->storeManager->getStore()->getId()!==$CCStoreId) {
            return $result;
        }

        $customBackorderMessage = (string)$this->scopeConfig->getValue(
            'catalog/crimson_cart_item/backorder',
            ScopeInterface::SCOPE_STORE
        );
        $customInstockMessage = (string)$this->scopeConfig->getValue(
            'catalog/crimson_cart_item/in_stock',
            ScopeInterface::SCOPE_STORE
        );
        $customDropshipMessage = (string)$this->scopeConfig->getValue(
            'catalog/crimson_cart_item/dropship',
            ScopeInterface::SCOPE_STORE
        );
        // Check if the result has errors
        if (count($result->getErrors())>0) {
            try {
                $product = $this->productRepository->get($sku);
                $dropShipOnly = $product->getData('cc_dropship_only');
                $mayDropShip = $product->getData('cc_may_ship_from_manufacturer');
                if ($dropShipOnly || $mayDropShip) {
                    if(!$customDropshipMessage)
                        return $this->productSalableResultFactory->create(['errors' => []]);
                    else{
                        $errors = [
                            $this->productSalabilityErrorFactory->create([
                                'code' => 'back_order-dropship',
                                'message' => __($customDropshipMessage)])
                        ];
                        return $this->productSalableResultFactory->create(['errors' => $errors]);
                    }
                }
                else{
                    if(!$customBackorderMessage)
                        return $result;
                    else{
                        $errors = [
                            $this->productSalabilityErrorFactory->create([
                                'code' => 'back_order-not-enough',
                                'message' => __($customBackorderMessage)])
                        ];
                        return $this->productSalableResultFactory->create(['errors' => $errors]);
                    }
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            }
        }elseif ($customInstockMessage){
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'custom_in_stock',
                    'message' => __($customInstockMessage)])
            ];
            return $this->productSalableResultFactory->create(['errors' => $errors]);
        }

        return $result;
    }
}
