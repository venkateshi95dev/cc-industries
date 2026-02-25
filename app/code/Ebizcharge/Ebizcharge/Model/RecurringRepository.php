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

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Model\RecurringFactory as ModelFactory;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring as Resource;
use Ebizcharge\Ebizcharge\Api\Data\RecurringSearchResultInterfaceFactory as SearchFactory;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\CollectionFactory as CollectionFactory;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Contains operations related to ebizcharge_recurring table
 *
 * Class RecurringRepository
 */
class RecurringRepository extends AbstractRepository implements RecurringRepositoryInterface
{
    /**
     * @var RecurringFactory
     */
    protected $modelFactory;

    /**
     * @var SearchFactory
     */
    protected $searchFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * Main Constructor
     *
     * @param RecurringFactory $modelFactory
     * @param SearchFactory $searchFactory
     * @param CollectionFactory $collectionFactory
     * @param Resource $resource
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        ModelFactory $modelFactory,
        SearchFactory $searchFactory,
        CollectionFactory $collectionFactory,
        Resource $resource,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct($ebizchargeLogger);

        /** @var  modelFactory */
        $this->modelFactory = $modelFactory;
        /** @var  searchFactory */
        $this->searchFactory = $searchFactory;
        /** @var  collectionFactory */
        $this->collectionFactory = $collectionFactory;
        /** @var  resource */
        $this->resource = $resource;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Save Method
     *
     * @param RecurringInterface $recurringRecord
     * @return RecurringInterface|null
     */
    public function save(RecurringInterface $recurringRecord): ?RecurringInterface
    {
        /** logging to the logger */
        $this->ebizchargeLogger->addInfo(__("Recurring saved record"));
        return parent::saveRecord($recurringRecord);
    }

    /**
     * Get By Id
     *
     * @param mixed $entityId
     * @param mixed $field
     * @return RecurringInterface|null
     */
    public function getById($entityId, $field = null): ?RecurringInterface
    {
        return parent::getRecordById($entityId, $field);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria): mixed
    {
        return parent::getList($searchCriteria);
    }
}
