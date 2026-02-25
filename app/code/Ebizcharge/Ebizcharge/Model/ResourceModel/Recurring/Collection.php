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

namespace Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring;

use Exception;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring as RecurringResourceModel;
use Ebizcharge\Ebizcharge\Model\Recurring as RecurringModel;
use Magento\Framework\Api\Search\SearchResultInterface;

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
    protected $_idFieldName = 'entity_id';

    /**
     * Event prefix var
     *
     * @var string
     */
    protected $_eventPrefix = 'ebiz_recurring_collection';

    /**
     * Event object var
     *
     * @var string
     */
    protected $_eventObject = 'recurring_collection';

    /**
     * @var AggregationInterface
     */
    private $aggregations;

    /**
     * Construct Method
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(RecurringModel::class, RecurringResourceModel::class);
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
     * @return SearchResultInterface
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
     * @return $this|SearchResultInterface
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set Table Records
     *
     * Update Data for given condition for collection
     *
     * @param mixed $columnData
     * @param mixed $condition
     * @return bool|string
     */
    public function setTableRecords($columnData, $condition)
    {
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            $connection->update(
                $this->getMainTable(),
                $columnData,
                $where = $condition
            );
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
            return $e->getMessage();
        }
        return true;
    }
}
