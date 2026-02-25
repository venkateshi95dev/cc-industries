<?php

namespace Crimson\Customer\Observer;

use Crimson\Customer\Service\CatalogRequestRedirect;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AccountIndexCheck implements ObserverInterface
{

    public function __construct(
        protected CatalogRequestRedirect $catalogRequestRedirect
    ) {}

    public function execute(Observer $observer)
    {
        try {
            $this->catalogRequestRedirect->checkCatalogRequest($observer->getRequest());
        } catch (\Exception $e) {

        }
    }
}
