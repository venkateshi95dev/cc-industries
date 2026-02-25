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

namespace Ebizcharge\Ebizcharge\Api;

use Ebizcharge\Ebizcharge\Api\Data\OrderSubscriptionInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface OrderSubscriptionRepositoryInterface
 *
 * Order Subscription Repository Interface
 */
interface OrderSubscriptionRepositoryInterface
{
    /**
     * Save order subscription records
     *
     * @param OrderSubscriptionInterface $subscription
     * @return OrderSubscriptionInterface|false
     */
    public function save(OrderSubscriptionInterface $subscription): ?OrderSubscriptionInterface;

    /**
     * Retrieve a specific order subscription record
     *
     * @param mixed $entityId
     * @param null|mixed $field
     * @return OrderSubscriptionInterface|false
     */
    public function getById($entityId, $field = null): ?OrderSubscriptionInterface;

    /**
     * Retrieve records matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
