<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/15/2019 1:18 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 * @package Crimson\MachCatalogRequest\Model
 */
class Config
{
    const XPATH_CATALOG_REQUEST_ENABLED = 'mach/catalog_request/enabled';
    const XPATH_AVAILABLE_CATALOGS = 'mach/catalog_request/available_catalogs';
    const XPATH_FOOTER_REQUEST_CATALOG_IMAGE_URL = 'mach/catalog_request/footer_request_catalog_image_url';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * AvailableCatalogs constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface         $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $websiteId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isEnabled($websiteId = null): bool
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return $this->scopeConfig->isSetFlag(self::XPATH_AVAILABLE_CATALOGS, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @param $websiteId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAvailableCatalogs($websiteId = null): array
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        $result = $this->scopeConfig->getValue(self::XPATH_AVAILABLE_CATALOGS, ScopeInterface::SCOPE_WEBSITE, $websiteId);
        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * @param $websiteId
     * @return string|null
     */
    public function getFooterRequestCatalogImageUrl($websiteId = null): ?string
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return $this->scopeConfig->getValue(
            self::XPATH_FOOTER_REQUEST_CATALOG_IMAGE_URL,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }
}
