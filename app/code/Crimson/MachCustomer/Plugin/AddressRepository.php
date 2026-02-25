<?php

namespace Crimson\MachCustomer\Plugin;

use Crimson\MachCustomer\Service\CustomerSaveDataProvider;
use Crimson\MachCustomer\Service\ScheduleCustomerExport;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;

/**
 * Class AddressRepository
 * @package Crimson\MachCustomer\Plugin
 */
class AddressRepository
{
    /** @var CustomerSaveDataProvider $_customerRepository */
    protected $_customerSaveDataProvider;
    /**
     * @var ScheduleCustomerExport
     */
    protected $scheduleCustomerExport;

    /**
     * AddressRepository constructor.
     *
     * @param CustomerSaveDataProvider $customerSaveDataProvider
     * @param ScheduleCustomerExport   $scheduleCustomerExport
     */
    public function __construct(
        CustomerSaveDataProvider $customerSaveDataProvider,
        ScheduleCustomerExport $scheduleCustomerExport
    ) {
        $this->_customerSaveDataProvider = $customerSaveDataProvider;
        $this->scheduleCustomerExport    = $scheduleCustomerExport;
    }

    /**
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterface           $address
     *
     * @return AddressInterface
     * @throws \Exception
     */
    public function afterSave(AddressRepositoryInterface $addressRepository, AddressInterface $address): AddressInterface
    {
        //if customer save is in progress, we don't need to process this.
        if ($this->_customerSaveDataProvider->isSaveInProgress($address->getCustomerId())
            && $address->getCustomerId()
        ) {
            $this->scheduleCustomerExport->scheduleById((int)$address->getCustomerId());
        }

        return $address;
    }
}
