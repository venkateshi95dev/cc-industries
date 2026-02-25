<?php
/**
 * @namespace   Crimson
 * @module      MachBase
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/12/2019 11:37 AM
 * @brief
 */

namespace Crimson\MachBase\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class MachConfig
 * @package Crimson\MachBase\Model
 */
class MachConfig
{
    CONST ZIP_WEBSITE_CODE = 'base';
    CONST ZIP_STORE_CODE   = 'default';

    const XPATH_ENABLED               = 'mach/settings/enabled';
    const XPATH_WSDL_URL              = 'mach/settings/wsdl_url';
    const XPATH_ADDRESS_WSDL_URL       = 'mach/settings/wsdl_address_url';
    const XPATH_SOAP_TIMEOUT          = 'mach/settings/soap_timeout';
    const XPATH_LOG_LEVEL             = 'mach/settings/log_level';
    const XPATH_INTEGRATION_TIMEZONE  = 'mach/settings/integration_timezone';
    const XPATH_SECURITY_CODE         = 'mach/settings/security_code';
    const XPATH_MACH_CACHE_WSDL = 'mach/settings/cache_wsdl';

    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected StoreManagerInterface $storeManager
    ) {}

    /**
     * @param null $websiteId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isEnabled($websiteId = null): bool
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return $this->scopeConfig->isSetFlag(self::XPATH_ENABLED, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getCacheWsdl(): int
    {
        return (int) $this->scopeConfig->isSetFlag(self::XPATH_MACH_CACHE_WSDL, ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getWebsiteId());
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getWsdlUrl()
    {
        return $this->scopeConfig->getValue(self::XPATH_WSDL_URL, ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getWebsiteId());
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getWsdlAddressUrl()
    {
        return $this->scopeConfig->getValue(self::XPATH_ADDRESS_WSDL_URL, ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getWebsiteId());
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getSoapTimeout(): int
    {
        return (int) ($this->scopeConfig->getValue(self::XPATH_SOAP_TIMEOUT, ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getWebsiteId()) ?: 10);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getLogLevel()
    {
        return $this->scopeConfig->getValue(self::XPATH_LOG_LEVEL, ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getWebsiteId());
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getIntegrationTimezone()
    {
        return $this->scopeConfig->getValue(self::XPATH_INTEGRATION_TIMEZONE, ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getWebsiteId());
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getSecurityCode()
    {
        return $this->scopeConfig->getValue(self::XPATH_SECURITY_CODE, ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getWebsiteId());
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getZIPWebsiteId(): int
    {
        return $this->storeManager->getWebsite(self::ZIP_WEBSITE_CODE)->getId();
    }
}
