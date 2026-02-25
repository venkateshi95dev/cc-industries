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

namespace Ebizcharge\Ebizcharge\ViewModel;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Customer Address used in subscriptions or not
 *
 * Class CustomerAddress
 */
class CustomerAddress implements ArgumentInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var RecurringRepositoryInterface
     */
    private RecurringRepositoryInterface $recurringRepository;

    /**
     * @var CustomerSessionFactory
     */
    private CustomerSessionFactory $customerSession;

    /**
     * Main Class constructor
     *
     * @param RecurringRepositoryInterface $recurringRepository
     * @param CustomerSessionFactory $customerSession
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        RecurringRepositoryInterface $recurringRepository,
        CustomerSessionFactory $customerSession,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        /** @var  recurringRepository */
        $this->recurringRepository = $recurringRepository;
        /** @var  searchCriteriaBuilder */
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        /** @var  customerSession */
        $this->customerSession = $customerSession;
    }

    /**
     * Is Shipping Address Used in Subscription
     *
     * @param mixed $shippingAddressId
     * @return string
     */
    public function isShippingAddressUsedInSubscription($shippingAddressId): string
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            RecurringInterface::MAGE_CUST_ID,
            $this->customerSession->create()->getCustomer()->getId()
        )->addFilter(RecurringInterface::SHIPPING_ADDRESS_ID, $shippingAddressId);

        $records = $this->recurringRepository->getList($searchCriteria->create());

        return count($records->getItems()) > 0 ? 'Yes' : 'No';
    }
}
