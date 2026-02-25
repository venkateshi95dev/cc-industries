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

namespace Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\Grid;

use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\Collection as RecurringCollection;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Helper\Mysql\Fulltext as FullTextHelper;
use Magento\Framework\DB\Select;
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
class Collection extends RecurringCollection implements SearchResultInterface
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
     * Customer Entity Tablename
     *
     * @const: CUSTOMER_ENTITY_TABLENAME
     */
    public const CUSTOMER_ENTITY_TABLENAME = 'customer_entity';

    /**
     *
     * Customer Address Entity
     *
     * @const: CUSTOMER_ADDRESS_ENTITY_TABLENAME
     */
    public const CUSTOMER_ADDRESS_ENTITY_TABLENAME = 'customer_address_entity';

    /**
     * Catalog Product Entity Tablename
     *
     *
     * @const: CATALOG_PRODUCT_ENTITY_TABLENAME
     */
    public const CATALOG_PRODUCT_ENTITY_TABLENAME = 'catalog_product_entity';

    /**
     * @var Http
     */
    protected Http $_httpRequest;
    /**
     * @var FullTextHelper
     */
    protected FullTextHelper $_fullTextHelper;
    /**
     * @var AggregationInterface
     */
    private AggregationInterface $aggregations;

    /**
     * Collection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param Http $httpRequest
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
        LoggerInterface        $logger,
        FetchStrategyInterface $fetchStrategy,
        Http                   $httpRequest,
        FullTextHelper         $fullTextHelper,
        ManagerInterface       $eventManager,
                               $mainTable,
                               $resourceModel,
                               $model = Document::class,
        AdapterInterface       $connection = null,
        AbstractDb             $resource = null
    )
    {
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
        parent::getSearchCriteria();
        return $this;
    }

    /**
     * Set Search Cretria
     *
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return $this|Collection|SearchResultInterface
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        parent::setSearchCriteria($searchCriteria);
        return $this;
    }

    /**
     * Get total count
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count
     *
     * @param int $totalCount
     * @return $this|Collection|SearchResultInterface
     */
    public function setTotalCount($totalCount)
    {
        parent::setTotalCount($totalCount);
        return $this;
    }

    /**
     * Set items list
     *
     * @param array|null $items
     * @return $this|Collection|SearchResultInterface
     */
    public function setItems(array $items = null)
    {
        parent::setItems($items);
        return $this;
    }

    /**
     * Init Select
     *
     * @return Collection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        // $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('eb_rec_next_formatted', 'main_table.eb_rec_next');
        $this->addFilterToMap('start_date_formatted', 'main_table.eb_rec_start_date');
        $this->addFilterToMap('end_date_formatted', 'main_table.eb_rec_end_date');
        $this->addFilterToMap('customer_email', 'customer_entity.email');
        $this->addFilterToMap('product_sku', 'catalog_product_entity.sku');
        $this->addFilterToMap(
            'customer_name',
            new Zend_Db_Expr(
                'CONCAT(customer_entity.firstname, " ", customer_entity.lastname)'
            )
        );

        //  parent::_initSelect();
    }


    /**
     * Render Filters Before
     *
     * @return void
     * @phpcs:disable
     */
    protected function _renderFiltersBefore()
    {
        /** @var $keywords */
        $searchKeywords = $this->getSearchKeywords();

        /** @var $customerEntityTable */
        $customerEntityTable = $this->getTable(self::CUSTOMER_ENTITY_TABLENAME);
        /** @var $customerAddressEntityTable */
        $customerAddressEntityTable = $this->getTable(self::CUSTOMER_ADDRESS_ENTITY_TABLENAME);
        /** @var $catalogProductEntityTable */
        $catalogProductEntityTable = $this->getTable(self::CATALOG_PRODUCT_ENTITY_TABLENAME);

        /** @var $tableNames */
        $columnNames = [
            'main_table.mage_item_name',
            'customer_entity.email',
            'customer_entity.created_in',
            'customer_entity.firstname',
            'customer_entity.middlename',
            'customer_entity.lastname',
            'customer_address_entity.company',
            'customer_address_entity.street',
            'catalog_product_entity.sku',
            'main_table.qty_ordered',
            'main_table.eb_rec_frequency',
            'main_table.eb_rec_start_date',

        ];

        /** Building Query for customers */
        $this->getSelect()
            ->joinLeft(
                [
                    'customer_entity' => $customerEntityTable
                ],
                "main_table.mage_cust_id = customer_entity.entity_id",
                [
                    'eb_rec_next_formatted' => "date(main_table.eb_rec_next)",
                    'customer_id' => "customer_entity.entity_id",
                    'start_date_formatted' => "date(main_table.eb_rec_start_date)",
                    'end_date_formatted' => "date(main_table.eb_rec_end_date)",
                    'customer_email' => "customer_entity.email",
                    'customer_name' => "CONCAT(customer_entity.firstname, ' ', customer_entity.lastname)"
                ]
            )
            ->joinLeft(
                [
                    'customer_address_entity' => $customerAddressEntityTable
                ],
                "main_table.shipping_address_id = customer_address_entity.entity_id",
                [
                    'customer_shipping_address' => "CONCAT(customer_address_entity.street, ' ', customer_address_entity.city, ' ', customer_address_entity.region, ' ', customer_address_entity.postcode)"
                ]
            )->joinLeft(
                [
                    'catalog_product_entity' => $catalogProductEntityTable
                ],
                "main_table.mage_item_id = catalog_product_entity.entity_id",
                [
                    'product_sku' => "catalog_product_entity.sku"
                ]
            )
            //->order("main_table.eb_rec_start_date desc")
            //    ->order("main_table.entity_id desc")

        ;


        $matchQuery = "";
        $counter = 0;

        foreach ($columnNames as $columnName) {
            $matchQuery .= " $columnName like ('%" . $searchKeywords . "%') ";
            $counter++;
            if ($counter < count($columnNames)) {
                $matchQuery .= " OR ";
            }
        }
        $wherePart = $this->getSelect()->getPart(Select::WHERE);
        foreach ($wherePart as $colKey => $colCond) {
            $wherePart[$colKey] = str_replace('`rec_status`', '`main_table`.`rec_status`', $colCond);
        }
        if (!empty($searchKeywords)) {
            $wherePart[] = "OR (((".$matchQuery.")))";
        }

        $this->getSelect()->setPart(Select::WHERE, $wherePart);
        parent::_renderFiltersBefore();

        //  echo $this->getSelect()->__toString();exit;


    }
    // phpcs:enable

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
        return $this->_httpRequest->getParams();
    }
}
