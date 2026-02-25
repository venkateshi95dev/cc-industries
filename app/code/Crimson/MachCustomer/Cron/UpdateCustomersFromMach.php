<?php

namespace Crimson\MachCustomer\Cron;

use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCustomer\Helper\Data;
use Crimson\MachCustomer\Service\UpdateCustomer;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class UpdateCustomersFromMach
 * @package Crimson\MachCustomer\Cron
 */
class UpdateCustomersFromMach
{

    public function __construct(
        protected UpdateCustomer $updateCustomer,
        protected Data $customerMachHelper,
        protected StoreManagerInterface $storeManager
    ) {}

    public function execute(): void
    {
        $zipWebsiteId = $this->storeManager->getWebsite(MachConfig::ZIP_WEBSITE_CODE)->getId();
        if ($this->customerMachHelper->isMachExtEnable($zipWebsiteId) &&
            $this->customerMachHelper->isMachCustomerCronEnable($zipWebsiteId)) {
            $this->updateCustomer->execute();
        }
    }
}
