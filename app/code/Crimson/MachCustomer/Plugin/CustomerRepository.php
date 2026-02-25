<?php

namespace Crimson\MachCustomer\Plugin;

use Crimson\MachCustomer\Service\CustomerSaveDataProvider;
use Crimson\MachCustomer\Service\ScheduleCustomerExport;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Class CustomerRepository
 * @package Crimson\MachCustomer\Plugin
 */
class CustomerRepository
{
    /** @var CustomerSaveDataProvider $_customerRepository */
    protected $_customerSaveDataProvider;

    /**
     * @var ScheduleCustomerExport
     */
    protected $scheduleCustomerExport;

    /**
     * CustomerRepository constructor.
     *
     * @param CustomerSaveDataProvider $customerSaveDataProvider
     * @param ScheduleCustomerExport                                 $scheduleCustomerExport
     */
    public function __construct(
        CustomerSaveDataProvider $customerSaveDataProvider,
        ScheduleCustomerExport $scheduleCustomerExport
    ) {
        $this->_customerSaveDataProvider = $customerSaveDataProvider;
        $this->scheduleCustomerExport = $scheduleCustomerExport;
    }

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Closure                    $proceed
     *
     * @param CustomerInterface           $customer
     * @param null                        $passwordHash
     *
     * @return mixed
     * @throws \Exception
     */
    public function aroundSave(
        CustomerRepositoryInterface $customerRepository,
        \Closure $proceed,
        CustomerInterface $customer,
        $passwordHash = null
    )
    {
        if ($customer->getId()) {
            $this->_customerSaveDataProvider->setSaveInProgress($customer->getId());
        }

        $this->scheduleCustomerExport->scheduleByCustomerInterface($customer);

        $returnValue = $proceed($customer, $passwordHash);

        $this->_customerSaveDataProvider->setSaveProcessFinished($returnValue->getId());

        return $returnValue;
    }
}
