<?php

namespace Crimson\CorvetteCentralRushService\ViewModel;

use Crimson\CorvetteCentralRushService\Service\RushService;
use Magento\Checkout\ViewModel\Cart;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Context;

class RushServiceViewModel extends Cart
{

    public function __construct(
        protected Context $context,
        protected RushService $rushService,
        protected Session $customerSession,
        protected GroupFactory $groupFactory
    ) {
        parent::__construct($context);
    }

    public function showGroupDiscountForCustomer(): bool
    {
        if (!$this->customerSession->isLoggedIn()) {
            return false;
        }

        if ($this->customerSession->getCustomerGroupId() == $this->getGroupIdByName('CC-RETAIL')) {
            return false;
        }

        return true;
    }

    public function getGroupIdByName(string $groupName): ?int
    {
        $collection = $this->groupFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('customer_group_code', $groupName)
            ->setPageSize(1);

        $group = $collection->getFirstItem();

        return $group->getId() ?: null;
    }
    public function isItemRushService($item): bool
    {
        return $this->rushService->isItemProductRushService($item);
    }

    public function isItemEligibleForRush($item): bool
    {
        return $this->rushService->isItemEligibleForRush($item);
    }

    public function isRushServiceEnabled(): bool
    {
        return $this->rushService->isEnabled();
    }
}
