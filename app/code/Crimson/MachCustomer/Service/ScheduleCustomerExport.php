<?php
/**
 * @namespace   Crimson
 * @module      MachCustomer
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        5/13/2019 4:59 PM
 * @brief
 */

namespace Crimson\MachCustomer\Service;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;

/**
 * Class ScheduleCustomerExport
 * @package Crimson\MachCustomer\Service
 */
class ScheduleCustomerExport
{
    const CUSTOMER_ATTR_NEEDS_EXPORT = 'needs_mach_export';

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var CustomerPreventMachDataProvider
     */
    protected $customerPreventMachDataProvider;
    /**
     * @var CustomerResource
     */
    protected $customerResource;

    public function __construct(
        CustomerResource $customerResource,
        CustomerFactory $customerFactory,
        CustomerPreventMachDataProvider $customerPreventMachDataProvider
    ) {
        $this->customerResource = $customerResource;
        $this->customerFactory = $customerFactory;
        $this->customerPreventMachDataProvider = $customerPreventMachDataProvider;
    }

    /**
     * @param int $customerId
     *
     * @throws \Exception
     */
    public function scheduleById(int $customerId): void
    {
        if (!$customerId) {
            return;
        }

        $export = $this->_getExportSetting($customerId);

        $customer = $this->customerFactory->create();
        $customer->setData($this->customerResource->getLinkField(), $customerId);
        $customer->setData(self::CUSTOMER_ATTR_NEEDS_EXPORT, $export);

        $this->customerResource->saveAttribute($customer, self::CUSTOMER_ATTR_NEEDS_EXPORT);
    }

    /**
     * @param CustomerInterface $customer
     *
     * @throws \Exception
     */
    public function scheduleByCustomerInterface(CustomerInterface $customer): void
    {
        if (!$customer->getId()) {
            return;
        }

        $export = $this->_getExportSetting($customer->getId());
        $customer->setCustomAttribute(self::CUSTOMER_ATTR_NEEDS_EXPORT, $export);

        $this->scheduleById((int) $customer->getId());
    }

    /**
     * @param int $customerId
     *
     * @return int
     */
    protected function _getExportSetting(int $customerId): int
    {;
        return $this->customerPreventMachDataProvider->isPreventMachExport($customerId) ? 0 : 1;
    }
}
