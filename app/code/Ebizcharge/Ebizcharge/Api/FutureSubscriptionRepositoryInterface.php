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

use Ebizcharge\Ebizcharge\Api\Data\FutureSubscriptionInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface FutureSubscriptionRepositoryInterface
 *
 * Future Subscription Repository Interface
 */
interface FutureSubscriptionRepositoryInterface
{
    /**
     * Save subscription records
     *
     * @param FutureSubscriptionInterface $subscription
     * @return FutureSubscriptionInterface|null
     */
    public function save(FutureSubscriptionInterface $subscription): ?FutureSubscriptionInterface;

    /**
     * Retrieve a specific subscription record
     *
     * @param mixed $entityId
     * @param mixed $field
     * @return FutureSubscriptionInterface|null
     */
    public function getById($entityId, $field = null): ?FutureSubscriptionInterface;

    /**
     * Retrieve records matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
