<?php

namespace Crimson\Customer\Observer;

use Crimson\Customer\Service\CatalogRequestRedirect;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CatalogRequestCheck implements ObserverInterface
{

    public function __construct(
        protected CatalogRequestRedirect $catalogRequestRedirect
    ) {}

    public function execute(Observer $observer)
    {
        try {
            $this->catalogRequestRedirect->unsetComingFromCatalogRequest();
        } catch (\Exception $e) {

        }
    }
}
