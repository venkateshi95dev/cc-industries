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

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Data\Collection;

/**
 * This class provides basic repository functionality to other classes
 *
 * Class AbstractRepository
 */
abstract class AbstractRepository
{
    /**
     * Class to get single record
     *
     * @var $modelFactory
     */
    protected $modelFactory;

    /**
     * Search factory obj
     *
     * @var $searchFactory
     */
    protected $searchFactory;

    /**
     * Collection factory for get list method
     *
     * @var $collectionFactory
     */
    protected $collectionFactory;

    /**
     * Resource model class to save records
     *
     * @var $resource
     */
    protected $resource;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * Abstract Class constructor
     * only used for Dependency Injection
     *
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        EbizchargeLogger $ebizchargeLogger
    ) {
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Get List by Search Criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /**
         * @var SearchResultsInterface $searchResults
         */
        $searchResults = $this->searchFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /**
         * @var Collection $collection
         */
        $collection = self::searchList($searchCriteria, $this->collectionFactory->create());
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    /**
     * Search List
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param mixed $collection
     * @return mixed
     */
    // phpcs:ignore
    public static function searchList(SearchCriteriaInterface $searchCriteria, $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if (is_array($filter->getValue())) {
                    $collection->addFieldToFilter($filter->getField(), $filter->getValue());
                } else {
                    $condition = $filter->getConditionType() ?: 'eq';
                    $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
                }
            }
        }

        /**
         * Pagination for collection
         */
        if ($pageSize = $searchCriteria->getPageSize()) {
            $collection->setPageSize($pageSize);
        }
        if ($currentPage = $searchCriteria->getCurrentPage()) {
            $collection->setCurPage($currentPage);
        }

        return $collection;
    }

    /**
     * Save Record
     *
     * @param mixed $record
     * @return mixed|null
     */
    protected function saveRecord($record)
    {
        try {

            $this->resource->save($record);
            return $record;

        } catch (Exception $exception) {

            $this->ebizchargeLogger->addError(__("Exception occured " . $exception->getMessage()));
        }
        return null;
    }

    /**
     * Get Record By Id
     *
     * @param mixed $entityId
     * @param mixed $field
     * @return null
     */
    protected function getRecordById($entityId, $field = null)
    {
        try {
            $record = $this->modelFactory->create();
            $this->resource->load($record, $entityId, $field);

            return $record;

        } catch (Exception $e) {

            $this->ebizchargeLogger->addError(__("Exception occured " . $e->getMessage()));

        }
        return null;
    }
}
