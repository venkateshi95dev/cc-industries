<?php

namespace Crimson\MachShipping\Model\Carrier;

use Crimson\MachAddressVerification\Model\Service\VerifyAddress;
use Crimson\MachShipping\Model\Api\Shipping;
use Crimson\MachShipping\Model\Shipping\Carrier\Source\Method;
use Crimson\MachShipping\Model\Shipping\Method\FilterFactory;
use Crimson\MachShipping\Service\DeliveryDates;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Crimson\PoBoxRestriction\Service\IsShipMethodAllowedForPoBox;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use Crimson\MachShipping\Model\Config;

/**
 * Class Mach
 * @package Crimson\MachShipping\Model\Carrier
 */
class Mach extends AbstractCarrier implements CarrierInterface
{
    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'mach';

    /**
     * Format to display the delivery date
     *
     * @var int
     */
    const FORMAT_DATE = 'M d';
    const DELIVERY_DATE_HEAD_TEXT = 'Estimated Delivery ';

    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;

    protected $_result = null;
    protected ?RateRequest $_request = null;
    protected bool $_isEtaPossible = false;
    protected bool $_isEnabledEta = false;


    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        protected Method $_machShippingMethods,
        protected Shipping $_shippingApi,
        protected ResultFactory $_rateResultFactory,
        protected FilterFactory $_methodFilterFactory,
        protected MethodFactory $_rateResultMethodFactory,
        protected IsShipMethodAllowedForPoBox $isShipMethodAllowedForPoBox,
        protected TimezoneInterface $timezone,
        protected DeliveryDates $deliveryDates,
        protected VerifyAddress $addressVerify,
        protected Config $config,
        array $data = []
    ) {
        $this->_isEnabledEta = $this->config->isEnabledETA();
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param DataObject $request
     * @return $this|bool|DataObject|AbstractCarrier
     */
    public function processAdditionalValidation(DataObject $request)
    {
        if (!empty($request[IsShipMethodAllowedForPoBox::SHIP_DEST_STREET_FIELD])) {
            return $this->isShipMethodAllowedForPoBox->is(
                (string) $request[IsShipMethodAllowedForPoBox::SHIP_DEST_STREET_FIELD],
                $this->_getCode()
            );
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function _getCode(): string
    {
        return $this->_code;
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active') || !$this->_getApi()->isMachEnabled() || !$this->_getApi()->isUp()) {
            return false;
        }

        //Don't continue if postcode and state don't exist
        if (is_null($request->getDestPostcode()) || is_null($request->getDestRegionCode()) || strlen($request->getDestPostcode() ?? '') < 5) {
            return false;
        }

        //first steps
        $this->setRequest($request);

        //getting MACH Rates(filtering process happens here as well)
        $this->_result = $this->_getMachRates();
        if (empty($this->_result)) {
            return false;
        }

        //from here we have Mach rates
        $result = $this->_rateResultFactory->create();
        $methods = $this->_machShippingMethods->getAvailableMethods();
        $this->_updateFreeMethodQuote($request);

        //packageWeight
        $packageWeight = $request->getPackageWeight();
        $packageValue = $request->getPackageValue();

        $allowedFreeMethods = explode(',', $this->getConfigData('allow_if_zero'));
        $accountForHolidays = false;
        $accountForWeekends = false;
        if(count($this->_result) && $this->_isEnabledEta && $this->_isEtaPossible) {
            $accountForHolidays = $this->config->accountForHolidays();
            $accountForWeekends = $this->config->accountForWeekends();
        }

        foreach ($this->_result as $key => $item) {
            if ($this->_isRateAllowed((string)$key, $item, $allowedFreeMethods)) {

                if (!$this->_doesRateMeetExtraRules($key, $packageWeight, $packageValue)) {
                    continue;
                }

                $method = $this->_rateResultMethodFactory->create();

                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod($key);
                $method->setMethodTitle($methods[$key]);

                $method->setPrice($item['fob_ship_amount']);
                $method->setCost($item['fob_ship_amount']);

                //In transit days logic
                $deliveryDateFinal = '';
                if($this->_isEnabledEta && $this->_isEtaPossible && (int) $item['days_in_transit'] > 0) {
                    $currentDate = $this->timezone->date();
                    $startDate = clone $currentDate;

                    //first checks
                    $todayCounts = $this->deliveryDates->checkIfCurrentDayCounts(
                        (int) $item['days_in_transit'],
                        $currentDate,
                        $accountForHolidays,
                        $accountForWeekends
                    );

                    //if today is not considered then the start day is +1
                    if (!$todayCounts) {
                        $startDate->modify('+1 day');
                    }

                    //Delivery day is 1 day after in transit days
                    $dayInTransit = (int) $item['days_in_transit'] + 1;

                    //Adding in transit days from Mach AND validation for business days
                    $deliveryDate = $this->deliveryDates->getDeliveryDay(
                        $startDate,
                        $dayInTransit,
                        $accountForHolidays,
                        $accountForWeekends
                    );
                    $deliveryDateFinal = self::DELIVERY_DATE_HEAD_TEXT . ' ' . $deliveryDate->format(self::FORMAT_DATE);
                }

                $method->setMethodDescription($deliveryDateFinal);
                $result->append($method);
            }
        }

        return $result;
    }

    protected function _doesRateMeetExtraRules($rateKey, $packageWeight, $packageValue): bool
    {
        if (!$rateKey) {
            return false;
        }

        if (($rateKey === '508C' || $rateKey === '209') && $packageWeight <= 1 && $packageValue <= 200) {
            return true;
        } elseif (($rateKey === '508' || $rateKey === '209') && $packageWeight > 1 && $packageWeight <= 6 && $packageValue <= 200) {
            return true;
        } elseif ($rateKey != '508' && $rateKey != '508C' && $rateKey != '209') {
            return true;
        }

        return false;
    }

    protected function _getMachRates(): array
    {
        $request = $this->getRequest();
        $allowedMethods = $this->getAllowedMethods();
        if (!$allowedMethods) {
            return [];
        }

        //Restricting shipping options based on ships_from_manufacturer, inventory and hazardous_material
        $allowedMethods = $this->_filterShippingMethods($this->getRequest()->getAllItems(), $allowedMethods);

        //getting Residential or Business
        if ($allowedMethods) {
            $resBusFlagFromMach = $this->addressVerify->verifyAddressToGetResBusIndicatorValue($request);
            $request->setResBusFlag($resBusFlagFromMach);

            $resBusFlag = $this->_shippingApi->getResidentialCommercialValue($resBusFlagFromMach);
            if ($resBusFlag === Shipping::RESIDENTIAL_FLAG) {
                unset($allowedMethods[Method::MACH_COMMERCIAL_RATE_CODE]);
            }

            if ($resBusFlag === Shipping::COMMERCIAL_FLAG) {
                unset($allowedMethods[Method::MACH_RESIDENTIAL_RATE_CODE]);
            }
        }

        if (!$allowedMethods) {
            return [];
        }

        //calling MACH for Multi GET_FREIGHT
        $request->setMachMethods($allowedMethods);
        $rates = $this->_getApi()->getShippingRates($request);

        return $rates ?: [];
    }

    public function getAllowedMethods(): array
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr     = [];
        foreach ($allowed as $k){
            $label   = $this->_machShippingMethods->getMethod($k);
            $arr[$k] = sprintf('%s (%s)', $label, $k);
        }

        return $arr;
    }

    //filtering methods based on: dropship, inventory and hazardous_material
    protected function _filterShippingMethods(array $items, array $allowedMethods)
    {
        $_methodFilter = $this->_methodFilterFactory->create();
        $_filteredMethods = $_methodFilter->filterShippingMethods($items, $allowedMethods);
        $this->_isEtaPossible = $_filteredMethods['is_eta_possible'];

        return !empty($_filteredMethods['filtered_methods']) ? $_filteredMethods['filtered_methods'] : [];
    }

    protected function _getApi(): Shipping
    {
        return $this->_shippingApi;
    }

    private function setRequest(RateRequest $request): void
    {
        $this->_request = $request;
    }

    private function getRequest(): ?RateRequest
    {
        return $this->_request;
    }

    //validation per shipping method
    private function _isRateAllowed(string $key, array $item, array $allowedFreeMethods): bool
    {
        if (in_array($key, Method::MACH_RESCOM_RATES_CODE) && $key === Method::MACH_COMMERCIAL_RATE_CODE) {
            return $item['res_com_flag'] === Shipping::COMMERCIAL_FLAG &&
                (($allowedFreeMethods && in_array($key, $allowedFreeMethods)) || $item['fob_ship_amount'] >= 0.01);
        } elseif (in_array($key, Method::MACH_RESCOM_RATES_CODE) && $key === Method::MACH_RESIDENTIAL_RATE_CODE) {
            return $item['res_com_flag'] === Shipping::RESIDENTIAL_FLAG &&
                (($allowedFreeMethods && in_array($key, $allowedFreeMethods)) || $item['fob_ship_amount'] >= 0.01);
        }

        return ($allowedFreeMethods && in_array($key, $allowedFreeMethods)) || $item['fob_ship_amount'] >= 0.01;
    }
}
