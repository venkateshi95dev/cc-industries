<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\ResourceModel\FutureSubscription\Grid;

use Ebizcharge\Ebizcharge\Api\Data\FutureSubscriptionInterface;
use Ebizcharge\Ebizcharge\Model\ResourceModel\FutureSubscription\Collection as FutureSubscriptionCollection;
use Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\StatusRenderer;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

/**
 * This is grid collection for ebizcharge_recurring table
 *
 * Class Collection
 */
class Collection extends FutureSubscriptionCollection implements SearchResultInterface
{
    /**
     * Ebizcharge Recurring Tablename
     *
     * @const: EBIZCHARGE_RECURRING_TABLENAME
     */
    public const EBIZCHARGE_RECURRING_TABLENAME = 'ebizcharge_recurring';

    /**
     * Customer Entity Tablname
     *
     * @const: CUSTOMER_ENTITY_TABLENAME
     */
    public const CUSTOMER_ENTITY_TABLENAME = 'customer_entity';

    /**
     * Customer Address Entity Tablename
     *
     * @const: CUSTOMER_ADDRESS_ENTITY_TABLENAME
     */
    public const CUSTOMER_ADDRESS_ENTITY_TABLENAME = 'customer_address_entity';

    /**
     * Catalog product Entity Tablename
     *
     * @const: CATALOG_PRODUCT_ENTITY_TABLENAME
     */
    public const CATALOG_PRODUCT_ENTITY_TABLENAME = 'catalog_product_entity';

    /**
     * @var AggregationInterface
     */
    protected AggregationInterface $aggregations;

    /**
     * Class Main Constructor
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @param string $model
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable,
        $resourceModel,
        $model = Document::class,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * Aggregation Interface
     *
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * Set Aggregation
     *
     * @param AggregationInterface $aggregations
     * @return void
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     *
     * @return null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set Total Count
     *
     * @param int $totalCount
     * @return $this|Collection|SearchResultInterface
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set Items List
     *
     * @param array|null $items
     * @return object
     * @phpcs:disable
     */
    final public function setItems(array $items = null): object
    {
        return $this;
    }

    /**
     * Init Select
     *
     * @return object
     */
    final protected function _initSelect(): object
    {
        // phpcs:enable
        parent::_initSelect();
        $this->addFilterToMap('entity_id', 'main_table.entity_id');

        /** @var $searchKeywords */
        $searchKeywords = $this->getSearchKeywords();

        /** creating Collection and joining other columns to the Collection */
        $this->addFieldToFilter(
            'rec_status',
            [
                'lt' => StatusRenderer::STATUS_DELETED]
        );
        $this->getSelect()
            ->joinLeft(
                [
                    'ebizcharge_recurring' => $this->getTable(self::EBIZCHARGE_RECURRING_TABLENAME)
                ],
                'main_table.recurring_id = ebizcharge_recurring.entity_id',
                [
                    'ebizcharge_recurring.mage_item_name',
                    'ebizcharge_recurring.rec_status',
                    'ebizcharge_recurring.qty_ordered',
                    'ebizcharge_recurring.mage_item_id',
                    'ebizcharge_recurring.coupon_code',
                    'ebizcharge_recurring.discount',
                    'ebizcharge_recurring.eb_rec_frequency',
                    'recurring_date_formatted' => 'STR_TO_DATE(main_table.recurring_date, "%Y-%m-%d")'
                ]
            );

        $this->getSelect()->joinLeft(
            [
                'customer_entity' => $this->getTable(self::CUSTOMER_ENTITY_TABLENAME)
            ],
            "ebizcharge_recurring.mage_cust_id = customer_entity.entity_id",
            [
                'customer_name' => "CONCAT(customer_entity.firstname, ' ', customer_entity.lastname)",
                'customer_email' => "customer_entity.email"
            ]
        );
        $this->getSelect()->joinLeft(
            [
                'shipping_address_table' => $this->getTable(self::CUSTOMER_ADDRESS_ENTITY_TABLENAME)
            ],
            "ebizcharge_recurring.shipping_address_id = shipping_address_table.entity_id",
            [
                'shipping_address_street' => "shipping_address_table.street",
                'shipping_address_city' => "shipping_address_table.city",
                'shipping_address_region' => "shipping_address_table.region",
                'shipping_address_postcode' => "shipping_address_table.postcode"
            ]
        );

        $this->getSelect()->joinLeft(
            [
                'billing_address_table' => $this->getTable(self::CUSTOMER_ADDRESS_ENTITY_TABLENAME)
            ],
            "ebizcharge_recurring.billing_address_id = billing_address_table.entity_id",
            [
                'billing_address_street' => "billing_address_table.street",
                'billing_address_city' => "billing_address_table.city",
                'billing_address_region' => "billing_address_table.region",
                'billing_address_postcode' => "billing_address_table.postcode"
            ]
        );

        $this->getSelect()->joinLeft(
            [
                'catalog_product_entity' => $this->getTable(self::CATALOG_PRODUCT_ENTITY_TABLENAME)
            ],
            "ebizcharge_recurring.mage_item_id = catalog_product_entity.entity_id",
            [
                'catalog_product_entity.sku'
            ]
        );

        $this->addFilterToMap('customer_email', 'customer_entity.email');
        $this->addFilterToMap(
            'customer_name',
            new Zend_Db_Expr(
                'CONCAT(customer_entity.firstname, " ", customer_entity.lastname)'
            )
        );
        $this->addFilterToMap(
            'recurring_date_formatted',
            new Zend_Db_Expr(
                'STR_TO_DATE(main_table.recurring_date, "%Y-%m-%d")'
            )
        );

        return $this;
    }

    /**
     * Get Search Keywords
     *
     * @return mixed|string
     */
    public function getSearchKeywords()
    {
        $params = $this->getSearchParams();
        return isset($params['search']) ? $params['search'] : '';
    }

    /**
     * Get Search Params
     *
     * @return array
     */
    public function getSearchParams()
    {
        // phpcs:ignore
        return $_REQUEST;
    }

    /**
     * Render Filter Before
     */
    protected function _renderFiltersBefore()
    {

        /** @var $keywords */
        $searchKeywords = $this->getSearchKeywords();
        $keywords = $searchKeywords;
        $isTwoColumns = strpos($keywords, ' ') !== false ? true : false;

        /** @var $tableNames */
        $columnNames = [
            'ebizcharge_recurring.mage_item_name',
            'customer_entity.email',
            'customer_entity.created_in',
            'customer_entity.firstname',
            'customer_entity.middlename',
            'customer_entity.lastname',
            'catalog_product_entity.sku',
            'ebizcharge_recurring.eb_rec_frequency',
            'ebizcharge_recurring.eb_rec_start_date'

        ];
        $matchQuery = "";

        if ($isTwoColumns) {
            $matchQuery .= "CONCAT_WS(' ', customer_entity.firstname, customer_entity.lastname) like ('%" .
                $searchKeywords . "%')";
        } else {
            $counter = 0;
            foreach ($columnNames as $columnName) {
                $matchQuery .= " $columnName like ('%" . $searchKeywords . "%') ";
                $counter++;

                if ($counter < count($columnNames)) {
                    $matchQuery .= " OR ";
                }
            }
        }
        $this->addFieldToFilter(
            'rec_status',
            [
                'lt' => StatusRenderer::STATUS_DELETED]
        );
        $this->addFieldToFilter(
            'rec_status',
            [
                'eq' => FutureSubscriptionInterface::EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_PENDING]
        )
        ;
        /** implement query again */
        $this->getSelect()->where($matchQuery);
       //  dump($this->getSelect()->__toString());exit;

        parent::_renderFiltersBefore();
    }
}
