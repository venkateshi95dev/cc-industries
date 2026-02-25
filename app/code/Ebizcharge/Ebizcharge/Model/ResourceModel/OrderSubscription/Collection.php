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

namespace Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription as OrderSubscriptionResourceModel;
use Ebizcharge\Ebizcharge\Model\OrderSubscription as OrderSubscriptionModel;
use Magento\Framework\Api\Search\SearchResultInterface;
use Psr\Log\LoggerInterface;

/**
 * Collection for ebizcharge_recurring table
 *
 * Class Collection
 */
class Collection extends AbstractCollection implements SearchResultInterface
{
    /**
     * Field ID
     *
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Event prefix var
     *
     * @var string
     */
    protected $_eventPrefix = 'ebiz_Order_collection';

    /**
     * Event object var
     *
     * @var string
     */
    protected $_eventObject = 'Order_collection';

    /**
     * @var AggregationInterface
     */
    protected AggregationInterface $aggregations;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_init(OrderSubscriptionModel::class, OrderSubscriptionResourceModel::class);

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * Set Items
     *
     * @param array|null $items
     * @return $this|Collection
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Get Aggregations
     *
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * Get Aggregation
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
        return $this;
    }

    /**
     * Set search criteria
     *
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return SearchCriteriaInterface|null
     */
    public function setSearchCriteria(
        SearchCriteriaInterface $searchCriteria = null
    ) {
        return $searchCriteria;
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
     * Set total count
     *
     * @param int $totalCount
     * @return Collection|int
     */
    public function setTotalCount($totalCount)
    {
        return $totalCount;
    }
}
