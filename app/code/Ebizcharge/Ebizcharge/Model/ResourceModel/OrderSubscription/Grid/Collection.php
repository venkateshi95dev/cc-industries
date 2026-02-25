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

namespace Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription\Grid;

use Ebizcharge\Ebizcharge\Helper\Data as EbizHelper;
use Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription\Collection as OrderSubscriptionCollection;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Helper\Mysql\Fulltext as FullTextHelper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Psr\Log\LoggerInterface;

/**
 * This is grid collection for ebizcharge_recurring table
 *
 * Class Collection
 */
class Collection extends OrderSubscriptionCollection implements SearchResultInterface
{
    /**
     * Special Characters
     *
     * @const: SPECIAL_CHARACTERS
     */
    public const SPECIAL_CHARACTERS = '-+~/\\<>\'":*$#@()!,.?`=%&^';

    /**
     * Minimal Chracter Length
     *
     * @const MINIMAL_CHARACTER_LENGTH
     */
    public const MINIMAL_CHARACTER_LENGTH = 3;

    /**
     * Ebizcharge Recurring Tablename
     *
     * @const: EBIZCHARGE_RECURRING_TABLENAME
     */
    public const EBIZCHARGE_RECURRING_TABLENAME = 'ebizcharge_recurring';

    /**
     *
     * Customer Entity Tablename
     *
     * @const: CUSTOMER_ENTITY_TABLENAME
     */
    public const CUSTOMER_ENTITY_TABLENAME = 'customer_entity';

    /**
     * @var EbizHelper
     */
    protected EbizHelper $_httpRequest;

    /**
     * @var AggregationInterface
     */
    protected AggregationInterface $aggregations;

    /**
     * @var FullTextHelper
     */
    protected FullTextHelper $_fullTextHelper;

    /**
     * Collection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param EbizHelper $httpRequest
     * @param FullTextHelper $fullTextHelper
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
        EbizHelper $httpRequest,
        FullTextHelper $fullTextHelper,
        ManagerInterface $eventManager,
        $mainTable,
        $resourceModel,
        $model = Document::class,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        /** parent construct */
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        /** @var _httpRequest */
        $this->_httpRequest = $httpRequest;
        /** @var _fulltextHelper */
        $this->_fullTextHelper = $fullTextHelper;

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
     * Get Search Criteria
     *
     * @return null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set Search Criteria
     *
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return $this|Collection|SearchResultInterface
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get Total Count
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
     * @return $this|Collection|SearchResultInterface
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Render Filter Before
     *
     * @return void
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
            'ebizcharge_recurring.qty_ordered',
            'ebizcharge_recurring.eb_rec_frequency',
            'ebizcharge_recurring.eb_rec_start_date',
            'CONCAT_WS(" ", customer_entity.firstname, customer_entity.lastname)'
        ];
        $matchQuery = "";

        if ($isTwoColumns) {
            // phpcs:ignore
            $matchQuery .= "CONCAT_WS(' ', customer_entity.firstname, customer_entity.lastname) like ('%" . $searchKeywords . "%')";
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

        if ($searchKeywords !== "") {
            /** implement query again */
            $this->getSelect()->where($matchQuery);
        }

        parent::_renderFiltersBefore();
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
     * Init Select
     *
     * @return $this|Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('order_date', 'main_table.order_date');

        /**
         * Join Left
         */
        $this->getSelect()->join(
            [
                'ebizcharge_recurring' => $this->getTable(self::EBIZCHARGE_RECURRING_TABLENAME)
            ],
            'main_table.recurring_id = ebizcharge_recurring.entity_id',
            [
                'main_table.order_date',
                'mage_item_name' => 'ebizcharge_recurring.mage_item_name',
                'eb_rec_frequency' => 'ebizcharge_recurring.eb_rec_frequency'
            ]
        );

        /**
         * Join Left
         */
        $this->getSelect()->join(
            [
                'customer_entity' => $this->getTable(self::CUSTOMER_ENTITY_TABLENAME)
            ],
            "ebizcharge_recurring.mage_cust_id = customer_entity.entity_id",
            [
                'email' => 'customer_entity.email',
                'customer_name' => "CONCAT(customer_entity.firstname, ' ', customer_entity.lastname)"
            ]
        );

        /**
         * Add Filter To Map
         */
        $this->addFilterToMap(
            'customer_name',
            'main_table.order_date',
            "CONCAT(customer_entity.firstname, ' ', customer_entity.lastname)"
        );

        /** sorting by the latest order logs */
        //$this->getSelect()->order("main_table.entity_id DESC");
       //  dump($this->getSelect()->__toString());exit;

        return $this;
    }
}
