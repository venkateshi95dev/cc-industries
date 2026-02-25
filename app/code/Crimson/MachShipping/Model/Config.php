<?php

namespace Crimson\MachShipping\Model;

use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\MachConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 * @package Crimson\MachShipping\Model
 */
class Config
{
    CONST XPATH_MACH_SHIPPING_ENABLED        = "carriers/mach/active";
    CONST XPATH_MACH_DAYS_IN_TRANSIT_MESSAGE = "carriers/mach/time_in_transit_message";
    CONST XPATH_MACH_DAYS_IN_TRANSIT_ENABLED = "carriers/mach/eta_enabled";
    CONST XPATH_MACH_CARRIER_IN_USE          = "carriers/mach/carrier_in_use";
    CONST XPATH_MACH_NON_DELIVERY_HOLIDAYS   = "carriers/mach/non_delivery_holidays";
    CONST XPATH_MACH_ADDRESS_HOLIDAYS        = "carriers/mach/holidays_enabled";
    CONST XPATH_MACH_ADDRESS_WEEKENDS        = "carriers/mach/weekend_enabled";

    /** @var HealthCheck */
    protected $healthCheck;

    /** @var MachConfig */
    protected $machconfig;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    public function __construct(
        HealthCheck $healthCheck,
        MachConfig $machconfig,
        ScopeConfigInterface $scopeConfig,
        protected StoreManagerInterface $storeManager
    ) {
        $this->healthCheck = $healthCheck;
        $this->machconfig = $machconfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getCanCallMach(): bool
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        if (!$this->machconfig->isEnabled($websiteId) ||
            !$this->healthCheck->isUp() ||
            !$this->scopeConfig->isSetFlag(self::XPATH_MACH_SHIPPING_ENABLED, ScopeInterface::SCOPE_WEBSITE, $websiteId)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getTransitTimeMessage(): string
    {
        return $this->isEnabledETA()
            ? (string) $this->scopeConfig->getValue(self::XPATH_MACH_DAYS_IN_TRANSIT_MESSAGE, ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getWebsiteId())
            : '';
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getNonDeliveryHolidays(): string
    {
        return (string) $this->scopeConfig->getValue(self::XPATH_MACH_NON_DELIVERY_HOLIDAYS, ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getWebsiteId());
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function accountForHolidays(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_MACH_ADDRESS_HOLIDAYS, ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getWebsiteId());
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function accountForWeekends(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_MACH_ADDRESS_WEEKENDS, ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getWebsiteId());
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isEnabledETA(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_MACH_DAYS_IN_TRANSIT_ENABLED, ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getWebsiteId());
    }

    public function getCarrierInUse(): string
    {
        return (string) $this->scopeConfig->getValue(self::XPATH_MACH_CARRIER_IN_USE, ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getWebsiteId());
    }
}
