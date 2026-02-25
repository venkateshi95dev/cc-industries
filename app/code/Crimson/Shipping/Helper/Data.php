<?php
/**
 * @namespace   Crimson
 * @module      Shipping
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/21/2019
 */
namespace Crimson\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Crimson\MachBase\Model\Api\HealthCheck;

/**
 * Class Data
 * @package Crimson\Shipping\Helper
 */
class Data extends AbstractHelper
{
    const XPATH_ZIPFLATRATE_DIGITS_ENABLED = 'carriers/tablerate_zipflatrate/zipcode_valid_digits_active';

    protected $_atypicalExcludedCountryIds = array(
        'US'
    );

    protected $_atypicalRegionCodes = array(
        'AK', //alaska
        'AS', //American Samoa
        'AE', //Armed Africa, Canada, Europe, Middle East
        'AA', //Armed Americas
        'AP', //Armed Pacific
        'FM', //Federated Stats of Micronesia
        'GU', //guam
        'HI', //hawaii
        'MH', //Marshall Islands
        'MP', //Northern Mariana Islands
        'PR', //Puerto Rico
        'VI'  //Virgin Islands
    );

    /**
     * @var HealthCheck
     */
    protected $_machApiHealthCheck;

    /**
     * Data constructor.
     * @param Context $context
     * @param HealthCheck $machApiHealthCheck
     */
    public function __construct(
        Context $context,
        HealthCheck $machApiHealthCheck
    ) {
        $this->_machApiHealthCheck = $machApiHealthCheck;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getAtypicalRegionCodes(): array
    {
        return $this->_atypicalRegionCodes;
    }

    /**
     * Get country ids that would NOT typically be allowed to use atypical region codes.
     *
     * @return array
     */
    public function getExcludedCountryIds(): array
    {
        return $this->_atypicalExcludedCountryIds;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    public function isAtypicalShippingAllowed(RateRequest $request): bool
    {
        //if region code is in 'atypical region code array', we can use this method.
        if (in_array($request->getDestRegionCode(),$this->getAtypicalRegionCodes())) {
            return true;
        }
        //if country id is not in excluded we can use this method.
        if (!in_array($request->getDestCountryId(), $this->getExcludedCountryIds())) {
            return true;
        }

        return false;
    }

    /**
     * @return bool|mixed
     */
    public function getValidZipCodeDigits()
    {
        return $this->getConfigValue('carriers/tablerate_surepost/zipcode_valid_digits_active');
    }

    /**
     * Function to get Configuration value
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     * @param int $websiteId
     * @return bool
     */
    public function isZipFlatRateEnabled(int $websiteId): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_ZIPFLATRATE_DIGITS_ENABLED,scopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @return bool
     */
    public function getCrimsonMachApiIsUp(): bool
    {
        return $this->_machApiHealthCheck->isUp();
    }
}
