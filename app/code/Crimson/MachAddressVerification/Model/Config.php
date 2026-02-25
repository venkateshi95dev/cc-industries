<?php

namespace Crimson\MachAddressVerification\Model;

use Crimson\MachBase\Model\MachConfig;
use IWD\AddressValidation\Helper\Data;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Directory\Api\Data\RegionInformationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 * @package Crimson\MachAddressVerification\Model
 */
class Config
{
    const XPATH_MACH_ADDRESS_VERIFICATION_ENABLED = 'mach/address_verification/enabled';
    CONST US_COUNTRY_ID                           = 'US';
    CONST MACH_ADDRESS_VALIDATION_CODE            = 'mach';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CountryInformationAcquirerInterface $countryInformationAcquirer
     */
    protected $countryInformationAcquirer;

    protected $_usRegions = null;

    /** @var MachConfig $machConfig */
    protected $machConfig;

    /** @var Data $helper */
    protected $helper;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        MachConfig $machConfig,
        Data $helper
    ) {
        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->machConfig = $machConfig;
        $this->helper = $helper;
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isMachAddrValidationEnabled(): bool
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        return $this->machConfig->isEnabled($websiteId)
            && $this->scopeConfig->isSetFlag(
                self::XPATH_MACH_ADDRESS_VERIFICATION_ENABLED,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getUSAvailableRegion(): ?array
    {
        if ($this->_usRegions == null) {
            $country          = $this->countryInformationAcquirer->getCountryInfo(self::US_COUNTRY_ID);
            $this->_usRegions = $country->getAvailableRegions();
        }

        return $this->_usRegions;
    }

    /**
     * @param int $regionId
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getRegionCodeById(int $regionId): ?string
    {
        $result = null;
        if ($regionId > 0) {
            foreach ($this->getUSAvailableRegion() as $region) {
                /** @var RegionInformationInterface $region */
                if ((int)$region->getId() == $regionId) {
                    $result = (string)$region->getCode();
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @param string $regionCode
     *
     * @return int|null
     * @throws NoSuchEntityException
     */
    public function getRegionIdByCode(string $regionCode): ?int
    {
        $result = null;
        if ($regionCode) {
            foreach ($this->getUSAvailableRegion() as $region) {
                /** @var RegionInformationInterface $region */
                if ($regionCode == $region->getCode()) {
                    $result = (int)$region->getId();
                    break;
                }
            }
        }

        return $result;
    }
}
