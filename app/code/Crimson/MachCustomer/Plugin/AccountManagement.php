<?php

namespace Crimson\MachCustomer\Plugin;

use Crimson\MachCustomer\Helper\Data;
use Crimson\MachCustomer\Model\Api\CustomerUpdate;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\AccountManagementInterface;

/**
 * Class AccountManagement
 * @package Crimson\MachCustomer\Plugin
 */
class AccountManagement
{
    /** @var CustomerUpdate $_customerApi */
    protected $_customerApi;

    /** @var CustomerRepositoryInterface $_customerRepository */
    protected $_customerRepository;

    /** @var Data $_customerMachHelper */
    protected $_customerMachHelper;

    /**
     * AccountManagement constructor.
     *
     * @param CustomerUpdate $customerApi
     * @param CustomerRepositoryInterface                       $customerRepository
     */
    public function __construct(
        CustomerUpdate $customerApi,
        CustomerRepositoryInterface $customerRepository,
        Data $customerMachHelper
    ) {
        $this->_customerApi = $customerApi;
        $this->_customerRepository = $customerRepository;
        $this->_customerMachHelper = $customerMachHelper;
    }

    /**
     * @param AccountManagementInterface $accountManagement
     * @param CustomerInterface $customer
     * @return CustomerInterface
     */
    public function afterAuthenticate(AccountManagementInterface $accountManagement, CustomerInterface $customer): CustomerInterface
    {
        try {
            if($this->_customerMachHelper->isMachExtEnable()){
                $this->_customerApi->customerInfo($customer, true);
            }
        } catch (\Exception $exception) {
            $this->_customerApi->debugLogMessage('Unable to Update Customer from MACH: '.$exception->getMessage());
        } finally {
            return $customer;
        }
    }
}
