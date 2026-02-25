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

namespace Ebizcharge\Ebizcharge\Model\ResourceModel\FutureSubscription;

use Ebizcharge\Ebizcharge\Model\FutureSubscription as FutureSubscriptionModel;
use Ebizcharge\Ebizcharge\Model\ResourceModel\FutureSubscription as FutureSubscriptionResourceModel;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Collection for ebizcharge_recurring table
 *
 * Class Collection
 */
class Collection extends AbstractCollection implements SearchResultInterface
{
    /**
     * Field Id
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Event prefix var
     *
     * @var string
     */
    protected $_eventPrefix = 'ebiz_future_collection';

    /**
     * Event object var
     *
     * @var string
     */
    protected $_eventObject = 'future_collection';

    /**
     * @var AggregationInterface
     */
    protected AggregationInterface $aggregations;

    /**
     * Prepare Future Recurring Orders Collection
     *
     * @param string $fromDate
     * @param bool $isCron
     * @param string $outType
     * @param int $limit
     * @return Collection
     */
    public function prepareFutureRecurringOrdersCollection(
        $fromDate = '',
        $isCron = false,
        $outType = 'cli',
        $limit = 1000
    ) {
        /** @var $joinTables */
        $joinTables = [
            'main_table',
            'recurring'
        ];

        /** @var  $futureOrdersCollection */
        $futureOrdersCollection =
            $this->addFieldToSelect("entity_id", "future_recurring_id")
                ->addFieldToSelect("recurring_id", "recurring_id")
                ->addFieldToSelect("recurring_date", "recurring_date")
                ->addFieldToSelect("customer_id", "customer_id")
                ->addFieldToSelect("store_id", "store_id")
                ->addFieldToSelect("ordered_qty", "ordered_qty")
                ->addFieldToSelect("ordered_product_final_price", "ordered_product_final_price")
                ->addFieldToSelect("ordered_product_id", "ordered_product_id")
                ->addFieldToSelect("coupon_code", "coupon_code")
                ->addFieldToSelect("discount", "discount")
                ->addFieldToSelect("ordered_status", "ordered_status")
                ->addFieldToSelect("remarks", "remarks")
                ->addFieldToSelect("created_at", "created_at")
                ->addFieldToSelect("updated_at", "updated_at");

        $startDateFrom = $fromDate ? $fromDate : date('Y-m-d');
        $todayDate = date('Y-m-d');

        /** @var $recurringTableName */
        $recurringTableName = $this->_resource->getTable(Recurring::RECURRING_TABLE_NAME);

        $futureOrdersCollection->getSelect()
            ->joinRight(
                [
                    $joinTables[1] => $recurringTableName
                ],
                $joinTables[0] . '.' . FutureSubscriptionModel::RECURRING_ID . ' = ' . $joinTables[1] .
                '.' . FutureSubscriptionModel::ENTITY_ID
            )
            ->where(
                $joinTables[0] . '.' . FutureSubscriptionModel::RECURRING_DATE . '>? ',
                $startDateFrom
            )
            ->where(
                $joinTables[0] . '.' . FutureSubscriptionModel::RECURRING_DATE . '<=? ',
                $todayDate
            )
            ->where($joinTables[0] . '.' . FutureSubscriptionModel::ORDERED_STATUS . '= ?', 0)
            ->order($joinTables[0] . '.' . FutureSubscriptionModel::RECURRING_DATE . ' ASC ')
            ->limit($limit);

        return $futureOrdersCollection;
    }

    /**
     * Set Items
     *
     * @param array|null $items
     * @return $this|Collection
     */
    public function setItems(array $items = null)
    {
        return $this->setItems($items);
    }

    /**
     * Get aggregations
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
     * @return AggregationInterface
     */
    public function setAggregations($aggregations)
    {
        return $this->aggregations = $aggregations;
    }

    /**
     * Get Search Criteria.
     *
     * @return null
     */
    public function getSearchCriteria()
    {
        return $this;
    }

    /**
     * Set search criteria.
     *
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return SearchResultInterface
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
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
     * Set total count.
     *
     * @param int $totalCount
     * @return $this|SearchResultInterface
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Construct Method
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(FutureSubscriptionModel::class, FutureSubscriptionResourceModel::class);
    }
}
