<?php
declare(strict_types=1);

namespace Crimson\Customer\ViewModel;

use Magento\Company\Api\CompanyManagementInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Api\Data\CustomerInterface;

class CompanyViewModel implements ArgumentInterface
{
    public function __construct(
        private CompanyManagementInterface $companyManagement
    ) {
    }

    /**
     * @param CustomerInterface|null $customer
     * @return bool
     */
    public function hasCompany(?CustomerInterface $customer) : bool
    {
        if (!$customer) {
            return false;
        }
        $customerId = (int) $customer->getId();

        try {
            $company = $this->companyManagement->getByCustomerId($customerId);
        } catch (\Exception $e) {
            return false;
        }

        return (bool) $company;
    }
}
