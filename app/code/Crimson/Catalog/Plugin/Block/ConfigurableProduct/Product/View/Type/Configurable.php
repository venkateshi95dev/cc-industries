<?php

namespace Crimson\Catalog\Plugin\Block\ConfigurableProduct\Product\View\Type;

use Crimson\Catalog\Model\Config;
use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachCatalog\Model\Api\Inventory as InventoryApi;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as PluginConfigurable;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Crimson\MachCatalog\Model\Service\GetMultiItemInventory;
use Crimson\Catalog\Model\Service\SortSuperAttributes as SortSuperAttributesService;
use Crimson\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class Configurable
 * @package Crimson\Catalog\Plugin\Block\ConfigurableProduct\Product\View\Type
 */
class Configurable
{

    public function __construct(
        protected Json $json,
        protected HealthCheck $healthCheck,
        protected InventoryApi $inventoryApi,
        protected Config $catalogConfig,
        protected GetMultiItemInventory $getMultiItemInventory,
        protected SortSuperAttributesService $sortSuperAttributesService,
        protected CatalogHelper $catalogHelper,
        protected ProductRepositoryInterface $productRepository,
        protected SearchCriteriaBuilder $searchCriteriaBuilder,
        protected FilterBuilder $filterBuilder
    ) {}

    public function aroundGetJsonConfig(
        PluginConfigurable $subject,
        \Closure $proceed
    ) {
        $config = $proceed();
        $config = $this->json->unserialize($config);
        $productsCollection = $this->_getProducts($subject);

        $timeShippingText = $this->catalogHelper->getShippingTimeInfo();
        $msgStock = (string)$this->catalogConfig->getChildSelectedStockMsg();
        $msgNoStockDropship = (string)$this->catalogConfig->getChildSelectedNoStockDropshipMsg();
        $msgETAConfig = $this->catalogConfig->getETAAllMessages();

        $config['stockInfo'] = $this->getMultiItemInventory
            ->getMultiInventoryPdpConfigurable($productsCollection, $timeShippingText, $msgStock, $msgNoStockDropship, $msgETAConfig);
        if ($this->catalogConfig->isDisableDiscontinuedProduct()) {
            $arrDisc = [];
            foreach ($productsCollection as $product) {
                $arrDisc[$product->getId()] = $product->getData('item_discount_group');
            }
            $config['itemDiscountGroup'] = $arrDisc;
            foreach ($this->catalogConfig->getAvailabilityMessages() as $message) {
                if ($messageLabel = $message['message'] ?: $this->catalogConfig->getDefaultAvailabilityMessage()) {
                    $config['availabilityMessages'][$message['item_discount_group']] = $messageLabel;
                }
            }
        }

        return $this->json->serialize($config);
    }

    public function afterGetAllowAttributes(PluginConfigurable $subject, $result): array
    {
        $attributeSetId = $subject->getProduct()->getAttributeSetId();

        return $this->sortSuperAttributesService->sort($attributeSetId, $result);
    }

    /**
     * @param PluginConfigurable $subject
     * @return ProductInterface[]
     */
    protected function _getProducts(PluginConfigurable $subject): array
    {
        $ids = $this->_getAllowProducts($subject);

        $filters[] = $this->filterBuilder
            ->setField('entity_id')
            ->setConditionType('in')
            ->setValue($ids)
            ->create();
        $this->searchCriteriaBuilder->addFilters($filters);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->productRepository->getList($searchCriteria);

        return $searchResults->getItems();
    }

    /**
     * @param PluginConfigurable $subject
     * @return array
     */
    protected function _getAllowProducts(PluginConfigurable $subject): array
    {
        $products = [];
        $allProducts = $subject->getProduct()->getTypeInstance()->getUsedProducts($subject->getProduct(), null);
        /** @var $product Product */
        foreach ($allProducts as $product) {
            if ((int) $product->getStatus() === Status::STATUS_ENABLED) {
                $products[] = $product->getId();
            }
        }

        return $products;
    }
}
