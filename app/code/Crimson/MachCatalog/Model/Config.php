<?php

namespace Crimson\MachCatalog\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 * @package Crimson\MachCatalog\Model
 */
class Config
{
    const DEFAULT_NUMBER_OF_PRODUCTS = 500;
    const XPATH_NUMBER_OF_PRODUCTS = 'mach/update_products/qty_products';

    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected StoreManagerInterface $storeManager
    ) {}

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getNumberOfProducts(): int
    {
        $qty = (int) $this->scopeConfig->getValue(
            self::XPATH_NUMBER_OF_PRODUCTS,
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getStore()->getWebsiteId());
        if ($qty > 0) {
            return $qty;
        }

        return self::DEFAULT_NUMBER_OF_PRODUCTS;
    }
}
