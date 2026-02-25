<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Psr\Log\LoggerInterface;

/**
 * Helper Class for Module
 */
class Data extends AbstractHelper
{
    /**
     * scopeConfig for system Configuration
     *
     * @var string
     */
    public $scopeConfig;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var CurrencyFactory
     */
    public $priceCurrencyFactory;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var Configurable
     */
    public $configurable;

    /**
     * @var ProductRepositoryInterface
     */
    public $productRepository;

    /**
     * Class constructor to include all the dependencies
     *
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param CurrencyFactory $priceCurrencyFactory
     * @param StoreManagerInterface $storeManager
     * @param Configurable $configurable
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        CurrencyFactory $priceCurrencyFactory,
        StoreManagerInterface $storeManager,
        Configurable $configurable,
        ProductRepositoryInterface $productRepository
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->priceCurrencyFactory = $priceCurrencyFactory;
        $this->storeManager = $storeManager;
        $this->configurable = $configurable;
        $this->productRepository = $productRepository;
    }

    /**
     * Check Price Level module enable/disable
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            'i95dev_pricelevel/active_display/enabled',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getStore()->getWebsiteId()
        );
    }

    /**
     * Get enabled customer website IDs
     *
     * @return array
     */
    public function getEnabledCustomerWebsiteIds()
    {
       $enabledWebsiteIds = [];
       foreach ($this->storeManager->getWebsites() as $website) {
            $isEnabled = $this->scopeConfig->getValue(
                'i95dev_pricelevel/active_display/enabled',
                ScopeInterface::SCOPE_WEBSITE,
                $website->getId()
            );
            if ($isEnabled) {
               $enabledWebsiteIds[] = $website->getId();
            }
        }
        return $enabledWebsiteIds;
    }

    /**
     * Check if Price Groups are enabled in any website
     *
     * @return bool
     */
    public function isPriceGroupsEnabledInAnyWebsite()
    {
        foreach ($this->storeManager->getWebsites() as $website) {
            $isEnabled = $this->scopeConfig->isSetFlag(
                'i95dev_pricelevel/active_display/enabled',
                ScopeInterface::SCOPE_WEBSITE,
                $website->getId()
            );
    
            if ($isEnabled) {
                return true; // enabled in at least one website
            }
        }
        return false; // disabled in all websites
    }

    /**
     * Convert value from base currency to user currency
     *
     * @param  float|int $value
     * @return float|int
     */
    public function convertPrice($value)
    {
        $currencyCodeTo = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $currencyCodeFrom = $this->storeManager->getStore()->getBaseCurrency()->getCode();
        $rate = $this->priceCurrencyFactory->create()->load($currencyCodeFrom)
            ->getAnyRate($currencyCodeTo);
        return $value * $rate;
    }

    /**
     * Get parent id by child sku
     *
     * @param string $childSku
     * @return array
     */
    public function getParentIdsByChildSku($childSku)
    {
        $child = $this->productRepository->get($childSku);
        return $this->getParentIdsByChild($child->getId());
    }

    /**
     * Get parent id by child id
     *
     * @param int $childId
     * @return array
     */
    public function getParentIdsByChild($childId)
    {
        $parentIds = $this->configurable->getParentIdsByChild($childId);

        $parentList = [];
        if (!empty($parentIds)) {
            foreach ($parentIds as $parentId) {
                $parentList[] = $this->productRepository->getById($parentId)->getSku();
            }
        }

        return $parentList;
    }
}
