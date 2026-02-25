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

use Ebizcharge\Ebizcharge\Api\Data\OrderSubscriptionInterface;
use Ebizcharge\Ebizcharge\Api\OrderSubscriptionRepositoryInterface;
use Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription as Resource;
use Ebizcharge\Ebizcharge\Api\Data\FutureSubscriptionSearchInterfaceFactory as SearchFactory;
use Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription\CollectionFactory as CollectionFactory;
use Ebizcharge\Ebizcharge\Model\OrderSubscriptionFactory as ModelFactory;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;

/**
 * This repository contains operations related to ebizcharge_recurring_order table
 *
 * Class OrderSubscriptionRepository
 */
class OrderSubscriptionRepository extends AbstractRepository implements OrderSubscriptionRepositoryInterface
{
    /**
     * @var OrderSubscriptionFactory
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
     * Model Constructor
     *
     * @param OrderSubscriptionFactory $modelFactory
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
     * Save Function
     *
     * @param OrderSubscriptionInterface $subscription
     * @return OrderSubscriptionInterface|null
     */
    public function save(OrderSubscriptionInterface $subscription): ?OrderSubscriptionInterface
    {
        /** logging to the logger file */
        $this->ebizchargeLogger->addInfo(__("order Subscribed and saved "));

        return parent::saveRecord($subscription);
    }

    /**
     * Get By Id
     *
     * @param mixed $entityId
     * @param mixed $field
     * @return OrderSubscriptionInterface|null
     */
    public function getById($entityId, $field = null): ?OrderSubscriptionInterface
    {
        return parent::getRecordById($entityId, $field);
    }
}
