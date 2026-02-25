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

use Ebizcharge\Ebizcharge\Api\FutureSubscriptionRepositoryInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ResourceModel\FutureSubscription as Resource;
use Ebizcharge\Ebizcharge\Api\Data\FutureSubscriptionSearchInterfaceFactory as SearchFactory;
use Ebizcharge\Ebizcharge\Model\ResourceModel\FutureSubscription\CollectionFactory as CollectionFactory;
use Ebizcharge\Ebizcharge\Model\FutureSubscriptionFactory as ModelFactory;
use Ebizcharge\Ebizcharge\Api\Data\FutureSubscriptionInterface;

/**
 * This repository contains operations related to ebizcharge_recurring_dates table
 *
 * Class FutureSubscriptionRepository
 */
class FutureSubscriptionRepository extends AbstractRepository implements FutureSubscriptionRepositoryInterface
{
    /**
     * @var FutureSubscriptionFactory
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
     * Main Constructor of the Model
     *
     * @param FutureSubscriptionFactory $modelFactory
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
     * Save
     *
     * @param FutureSubscriptionInterface $subscription
     * @return FutureSubscriptionInterface|null
     */
    public function save(FutureSubscriptionInterface $subscription): ?FutureSubscriptionInterface
    {
        $this->ebizchargeLogger->addInfo(__("Future subscription record has been saved"));
        return parent::saveRecord($subscription);
    }

    /**
     * Get By Id
     *
     * @param mixed $entityId
     * @param mixed $field
     * @return FutureSubscriptionInterface|null
     */
    public function getById($entityId, $field = null): ?FutureSubscriptionInterface
    {
        return parent::getRecordById($entityId, $field = null);
    }
}
