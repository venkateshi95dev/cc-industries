<?php
/**
 * @namespace   Crimson
 * @module      Shipping
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/21/2019
 */
namespace Crimson\Shipping\Plugin;

use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\MachConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Shipping;
use Magento\Framework\App\State;
use Magento\Framework\App\Request\Http;
use Magento\Framework\UrlInterface;
use Magento\Framework\Message\ManagerInterface;
use Crimson\Shipping\Helper\Data;

/**
 * Class ShippingPlugin
 * @package Crimson\Shipping\Plugin
 */
class ShippingPlugin
{
    protected $_atypicalCarrierCode = 'atypicalregions';
    protected $_atypicalUSCarrierCode = 'atypicalusregions';

    protected Data $_helper;
    protected State $_appState;
    protected Http $_request;
    protected UrlInterface $_urlInterface;
    protected ManagerInterface $_messageManagerInterface;
    protected MachConfig $machConfig;
    protected HealthCheck $_healthCheck;

    public function __construct(
        Data $helper,
        State $appState,
        Http $request,
        UrlInterface $urlInterface,
        ManagerInterface $messageManagerInterface,
        MachConfig $machConfig,
        HealthCheck $healthCheck
    ) {
        $this->_helper = $helper;
        $this->_appState = $appState;
        $this->_request = $request;
        $this->_urlInterface = $urlInterface;
        $this->_messageManagerInterface = $messageManagerInterface;
        $this->machConfig = $machConfig;
        $this->_healthCheck = $healthCheck;
    }

    /**
     * @param Shipping $subject
     * @param callable $proceed
     * @param RateRequest $request
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function aroundCollectRates(Shipping $subject, callable $proceed, RateRequest $request)
    {
        $websiteId = !empty($request->getWebsiteId()) ? $request->getWebsiteId() : null;
        $isMachEnabled = $this->machConfig->isEnabled($websiteId);
        if (!$isMachEnabled) {
            return $proceed($request);
        }

        // Mach is not enabled or Mach is down
        if (!$this->_healthCheck->isUp()) {
            if ($this->_helper->isAtypicalShippingAllowed($request)) {
                $this->limitCarriers($request);
            }
            return $proceed($request);
        }

        // Mach is up
        if ($this->_isAtypicalShippingAllowed($request)) {
            //if atypical shipping is allowed, simply return payment which will handle it.
            $this->limitCarriers($request);
        } else {
            //if atypical is not allowed, we must limit this to mach only.
            $request->setLimitCarrier([
                'freeshipping',
                'flatrate',
                \Crimson\MachShipping\Model\Carrier\Mach::CODE,
                'instore',
                'm2eproshipping',
                'tablerate_surepost',
                'tablerate_zipflatrate',
                'tablerate_twoday'
            ]);
        }
        return $proceed($request);
    }

    /**
     * With MACH, all contiguous US regions are supported.
     * @param RateRequest $request
     * @return bool
     * @throws NoSuchEntityException
     */
    protected function _isAtypicalShippingAllowed(RateRequest $request): bool
    {
        $isMachEnabled = $this->machConfig->isEnabled();

        if (!$isMachEnabled) {
            return $this->_helper->isAtypicalShippingAllowed($request);
        }

        //if we already don't want to use, return.
        $result = $this->_helper->isAtypicalShippingAllowed($request);
        if (!$result) {
            return false;
        }

        $destRegionCode = $request->getDestRegionCode();
        //if we are here the default atypical functionality has determined we are in HI, AK, Guam, or outside hte US.
        // if we are in HI or AK we do not want to use Atypical shipping.
        if ($request->getDestCountryId() == 'US' && in_array($destRegionCode, ['HI', 'AK'])) {
            return false;
        }

        return true;
    }

    /**
     * @param RateRequest $request
     */
    public function limitCarriers(RateRequest $request)
    {
        $limitCarrier = ['freeshipping'];
        $atypicalUsRegion = $request->getDestRegionCode();
        if ($request->getDestCountryId() == 'US' && in_array($atypicalUsRegion, $this->getAtypicalRegionCodes())) {
            $limitCarrier[] = $this->_atypicalUSCarrierCode;
        } else {
            $limitCarrier[] = $this->_atypicalCarrierCode;
        }
        $request->setLimitCarrier($limitCarrier);
    }

    /**
     * @return array
     */
    public function getAtypicalRegionCodes(): array
    {
        return $this->_helper->getAtypicalRegionCodes();
    }
}
