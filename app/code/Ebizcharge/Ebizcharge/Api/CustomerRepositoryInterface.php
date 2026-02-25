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

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;

/**
 * Interface CustomerRepositoryInterface
 *
 * Customer Repository Interface
 */
interface CustomerRepositoryInterface
{
    /**
     * Create or update a customer.
     *
     * @param CustomerInterface $customer
     * @param string|null $passwordHash
     * @return CustomerInterface
     * @throws InputException If bad input is provided
     * @throws InputMismatchException If the provided email is already used
     * @throws LocalizedException
     */
    public function save(CustomerInterface $customer, $passwordHash = null);

    /**
     * Retrieve customer.
     *
     * @param string $email
     * @param int|null $websiteId
     * @return CustomerInterface
     * @throws NoSuchEntityException If customer with the specified email does not exist.
     * @throws LocalizedException
     */
    public function get($email, $websiteId = null);

    /**
     * Get customer by Customer ID.
     *
     * @param int $customerId
     * @return CustomerInterface
     * @throws NoSuchEntityException If customer with the specified ID does not exist.
     * @throws LocalizedException
     */
    public function getById($customerId);

    /**
     * Retrieve customers which match a specified criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See https://devdocs.magento.com/codelinks/attributes.html#CustomerRepositoryInterface to determine
     * which call to use to get detailed information about all attributes for an object.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return CustomerSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete customer.
     *
     * @param CustomerInterface $customer
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(CustomerInterface $customer);

    /**
     * Delete customer by Customer ID.
     *
     * @param int $customerId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($customerId);
}
