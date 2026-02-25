<?php

namespace Crimson\ProductAlert\Model\ResourceModel\Stock\Grid;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

/**
 * Adds more data to the collection.
 */
class Collection extends SearchResult
{
    /** @var ProductResource */
    protected $productResource;

    /** @var CustomerResource */
    protected $customerResource;

    /** @var array */
    protected $statuses;


    public function __construct(
        EntityFactory    $entityFactory,
        Logger           $logger,
        FetchStrategy    $fetchStrategy,
        EventManager     $eventManager,
                         $mainTable,
        ProductResource  $productResource,
        CustomerResource $customerResource,
                         $resourceModel = null,
                         $identifierName = null,
                         $connectionName = null
    )
    {
        $mainTable = 'product_alert_stock';
        $resourceModel = 'Magento\ProductAlert\Model\ResourceModel\Stock';
        $this->productResource = $productResource;
        $this->customerResource = $customerResource;
        $this->statuses = [
            0 => __('Not Sent'),
            1 => __('Sent'),
        ];
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel, $identifierName, $connectionName);
    }

    /**
     * Override default, in order to support 'sku' and 'customer_email' as fields.
     *
     * @param $field
     * @param $condition
     * @return Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'sku' && isset($condition['like'])) {
            $field = 'product_id';
            $condition = ['in' => $this->findProducts($condition['like'])];
        }
        if ($field == 'customer_email' && isset($condition['like'])) {
            $field = 'customer_id';
            $condition = ['in' => $this->findCustomers($condition['like'])];
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        $items = parent::getItems();
        /** @var Document $item */
        foreach ($items as $item) {
            $this->prepareItem($item);
        }
        return $items;
    }

    /**
     * Double purpose function.
     * Prepare data for both Grid-Render, and the Export.
     *
     * @param Document $item
     * @return void
     */
    protected function prepareItem(Document $item): void
    {
        $item->setData(
            'sku',
            $this->getProductSku($item->getData('product_id'))
        );
        $item->setData(
            'customer_email',
            $this->getCustomerEmail($item->getData('customer_id'))
        );
    }

    protected function getProductSku(int|string $productId): string
    {
        $connection = $this->productResource->getConnection();
        $select = $connection
            ->select()
            ->from($this->productResource->getEntityTable(), ['sku'])
            ->where('entity_id = ?', $productId);
        return (string)$connection->fetchOne($select);
    }

    protected function findProducts(string $like): array
    {
        $connection = $this->productResource->getConnection();
        $select = $connection
            ->select()
            ->from($this->productResource->getEntityTable(), ['entity_id'])
            ->where($connection->quoteInto('sku LIKE ?', $like));
        return $connection->fetchCol($select);
    }

    protected function getCustomerEmail(int|string $customerId): string
    {
        $connection = $this->customerResource->getConnection();
        $select = $connection
            ->select()
            ->from($this->customerResource->getEntityTable(), ['email'])
            ->where('entity_id = ?', $customerId);
        return (string)$connection->fetchOne($select);
    }

    protected function findCustomers(string $like): array
    {
        $connection = $this->customerResource->getConnection();
        $select = $connection
            ->select()
            ->from($this->customerResource->getEntityTable(), ['entity_id'])
            ->where($connection->quoteInto('email LIKE ?', $like));
        return $connection->fetchCol($select);
    }
}
