<?php

namespace Crimson\CorvetteCentralOSCO\Model\Carrier;

use Crimson\CorvetteCentralOSCO\Model\CorvetteCentralOSCOConfig;
use Crimson\CorvetteCentralOSCO\Service\AirShipmentOk;
use Crimson\CorvetteCentralOSCO\Service\BestWay;
use Crimson\CorvetteCentralOSCO\Service\OSCOApiService;
use Crimson\CorvetteCentralOSCO\Service\RequestService;
use Crimson\CorvetteCentralShipping\Model\CorvetteCentralShippingConfig;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Osco extends AbstractCarrier implements CarrierInterface
{

    CONST CODE = 'osco';
    CONST CACHE_PREFIX = 'osco_';
    CONST CACHE_ACTION = 'shipping_request_';
    const FORMAT_DATE  = 'M d';
    const DELIVERY_DATE_HEAD_TEXT = 'Estimated Delivery ';
    CONST CARRIER_METHOD_TITLE    = 'Shipping Options';
    CONST METHOD_RELABEL         = '01';
    CONST CARRIER_METHOD_RELABEL = 'ups';
    CONST METHOD_NEW_LABEL       = 'Next Day';

    protected $_code   = self::CODE;
    protected $_result = null;

    protected ?RateRequest $_request        = null;
    protected array $_countriesETA          = [];
    protected array $_noAirShipmentOKMethod = [];
    protected array $_usAtypicalRegions     = [];
    protected int $_countriesETAAddDays     = 0;
    protected bool $_bestWayEnabled        = false;
    protected bool $_isAirShipmentPossible = false;

    public function __construct(
        protected CorvetteCentralOSCOConfig $corvetteCentralOSCOConfig,
        protected ScopeConfigInterface $scopeConfig,
        protected ErrorFactory $rateErrorFactory,
        protected LoggerInterface $logger,
        protected ResultFactory $rateResultFactory,
        protected MethodFactory $rateResultMethodFactory,
        protected OSCOApiService $oscoService,
        protected CacheInterface $cacheManager,
        protected TimezoneInterface $timezone,
        protected BestWay $bestWayService,
        protected AirShipmentOk $airShipmentService,
        protected Json $json,
        protected RequestService $requestService,
        protected CorvetteCentralShippingConfig $shippingConfig,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->_countriesETA           = $this->corvetteCentralOSCOConfig->getCountriesForETA();
        $this->_noAirShipmentOKMethod  = $this->corvetteCentralOSCOConfig->getNoAirShippingAllowedMethod();
        $this->_countriesETAAddDays    = $this->corvetteCentralOSCOConfig->getETAExtraDays();
        $this->_bestWayEnabled         = $this->bestWayService->isEnabled();
        $this->_usAtypicalRegions      = $this->shippingConfig->getUSAtypicalRegions($this->_code);
    }


    public function processAdditionalValidation(DataObject $request)
    {
        return $this;
    }

    protected function _getCode(): string
    {
        return $this->_code;
    }

    public function collectRates(RateRequest $request)
    {
        //shipping method enabled
        if (!$this->corvetteCentralOSCOConfig->isEnabled()) {
            return false;
        }

        //check if request contains all required fields
        if (!$this->_doesRequestHaveRequiredFields($request)) {
            return false;
        }

        //check if OSCO call is needed
        if (!$this->requestService->isOSCOQuote($request)) {
            return false;
        }

        //check if air shipment ius possible
        if ($this->requestService->isAirShipmentOkQuote($request)) {
            $this->_isAirShipmentPossible = true;
        }

        //first steps
        $this->setRequest($request);
        $this->_result = $this->_getOSCORates();
        $this->_updateFreeMethodQuote($request);
        if (empty($this->_result)) {
            return false;
        }

        //processing OSCO carriers/rates
        $result = $this->rateResultFactory->create();
        $oscoCarrierCode  = $this->_code;
        foreach ($this->_result as $carrierCode => $carrierRates) {
            //these carrier rates already match the allowed methods for the carrier
            foreach ($carrierRates as $carrierRate) {
                $method = $this->rateResultMethodFactory->create();

                $method->setCarrier($oscoCarrierCode);
                $method->setCarrierTitle(self::CARRIER_METHOD_TITLE);
                $method->setMethod($carrierCode . '_' . $carrierRate['ServiceType']);
                $method->setMethodTitle($carrierRate['ServiceDescription']);
                $method->setPrice($carrierRate['TotalCharges']);
                $method->setCost($carrierRate['TotalCharges']);

                $deliveryDate = $this->_calculateETA($carrierRate['ArrivalDate'] ?? '');
                $method->setMethodDescription($deliveryDate);

                $result->append($method);
            }
        }

        return $result;
    }

    protected function _getOSCORates(): array
    {
        //preparing OSCO request, checking cache and getting rates from OSCO
        $oscoRequestData = $this->_prepareOSCORequest();
        if (!$oscoRequestData) {
            return [];
        }

        //at this point we have items to send to OSCO
        $oscoRates = $this->_getCachedResult($oscoRequestData);
        $cacheUsed = true;
        if ($oscoRates === false) {
            $oscoRates = $this->oscoService->execute($oscoRequestData);
            $cacheUsed = false;
        }

        if (!$cacheUsed && $oscoRates) {
            $this->_setCachedResult($oscoRequestData, $oscoRates);
        } else if (!$oscoRates) {
            return [];
        }

        $allowedMethods = $this->getAllowedMethods();
        if (!$allowedMethods) {
            return [];
        }

        $rates = [];
        //checking AirShipmentOk
        if (!$this->_isAirShipmentPossible && $this->getRequest()->getDestCountryId() === self::USA_COUNTRY_ID) {
            $rates = $this->airShipmentService->check($oscoRates, $this->_noAirShipmentOKMethod);
        } else {
            foreach ($allowedMethods as $methodCode => $methodRates) {
                if (!isset($oscoRates[$methodCode])) {
                    continue;
                }

                $rates[$methodCode] = $this->_processOSCOResponseAllowedMethods($methodCode, $methodRates, $oscoRates[$methodCode]);
            }
        }

        //BestWay checking
        if ($rates && $this->_bestWayEnabled) {
            $rates = $this->bestWayService->calculateBestWay($this->getRequest(), $rates);
        }

        return !empty($rates) ? $rates : [];
    }

    public function getAllowedMethods(): array
    {
        try {
            if (is_null($this->_request)) {
                return [];
            }

            if ($this->_request->getDestCountryId() === self::USA_COUNTRY_ID) {
                return $this->corvetteCentralOSCOConfig->getAllCarriersAllowedMethods();
            }

            if ($this->_request->getDestCountryId() === self::CANADA_COUNTRY_ID) {
                return $this->corvetteCentralOSCOConfig->getUpsCAAllowedMethods();
            }

            return $this->corvetteCentralOSCOConfig->getUpsForeignAllowedMethods();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function _doesRequestHaveRequiredFields(RateRequest $request): bool
    {
        if (empty($request->getDestCountryId())) {
            return false;
        }

        //US atypical regions check first
        if ($this->isUSAtypicalRegion($request)) {
            return false;
        }

        //US and CA
        if (in_array($request->getDestCountryId(), [self::USA_COUNTRY_ID, self::CANADA_COUNTRY_ID])) {
            return !(empty($request->getDestPostcode()) || empty($request->getDestRegionCode()) || strlen($request->getDestPostcode()) < 5);
        }

        //Rest of the world
        return !(empty($request->getDestPostcode()) || empty($request->getDestRegionCode()));
    }

    public function isUSAtypicalRegion(RateRequest $request): bool
    {
        return $request->getDestCountryId() === AbstractCarrier::USA_COUNTRY_ID &&
            !empty($request->getDestRegionCode()) &&
            !empty($this->_usAtypicalRegions) &&
            in_array($request->getDestRegionCode(), $this->_usAtypicalRegions);
    }

    protected function _processOSCOResponseAllowedMethods($methodCode, $allowedMethods, $carrierRates): array
    {
        $result = [];
        foreach ($allowedMethods as $allowedMethod) {
            if (($key = array_search((string)$allowedMethod, array_column($carrierRates, 'ServiceType'))) !== false) {
                if ($methodCode === self::CARRIER_METHOD_RELABEL && $allowedMethod == self::METHOD_RELABEL) {
                    $carrierRates[$key]['ServiceDescription'] = self::METHOD_NEW_LABEL;
                }

                $result[] = $carrierRates[$key];
            }
        }

        //Rest of the world shipping address extra processing
        if (!empty($result) && $this->_isCountryRestOfTheWorld()) {
            usort($result, fn($a, $b) => $a['TotalCharges'] <=> $b['TotalCharges']);
            $result = array_slice($result, 0, 1, true);
        }

        return $result;
    }

    protected function _prepareOSCORequest(): array
    {
        $request = $this->getRequest();
        $streetFull = explode("\n", $request->getDestStreet() ?? '');
        $items = $this->requestService->createOSCOSKUDataPayload($request);
        if (!$items) {
            return [];
        }

        //preparing OSCO request
        return [
            'OSCO_DATA' => [
                'ORDER' => [
                    'ORDER_ID' => $this->corvetteCentralOSCOConfig->getOrderId()
                ],
                'SKUDATA' => [
                    'SKU' => $items
                ],
                'RateShop' => [
                    'ShipFromAddress' => $this->corvetteCentralOSCOConfig->getOriginAddress($request->getStoreId()),
                    'ShipToAddress'   => [
                        'AddressLine1' => ucwords(strtolower($streetFull[0] ?? '')),
                        'AddressLine2' => ucwords(strtolower($streetFull[1] ?? '')),
                        'City'         => ucwords(strtolower($request->getDestCity() ?? '')),
                        'State'        => $request->getDestRegionCode(),
                        'Zip'          => $request->getDestPostcode(),
                        'Country'      => $request->getDestCountryId(),
                    ]
                ],
            ]
        ];
    }

    private function setRequest(RateRequest $request): void
    {
        $this->_request = $request;
    }

    private function getRequest(): ?RateRequest
    {
        return $this->_request;
    }

    protected function _setCachedResult(array $arguments, $result)
    {
        $cacheKey        = $this->_getCacheKeyFromArgumentArray($arguments);
        $result          = $this->_removeUnserializableElements($result);
        $maxAgeInSeconds = $this->_getMaxRequestAge() * 60; //minutes to be in cache

        $this->cacheManager->save($this->json->serialize($result), $cacheKey, [], $maxAgeInSeconds);

        return $this;
    }

    protected function _getCachedResult(array $arguments)
    {
        $cacheKey = $this->_getCacheKeyFromArgumentArray($arguments);

        $result = $this->cacheManager->load($cacheKey) ?: false;
        if ($result && !is_array($result)) {
            $result = $this->json->unserialize($result);
        }

        return $result;
    }

    private function _removeUnserializableElements(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->_removeUnserializableElements($value);
            } elseif (!$this->_isSerializable($value)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected function _getMaxRequestAge(): int
    {
        return 60;
    }

    private function _isSerializable($value): bool
    {
        try {
            $value = $this->json->serialize($value);
            return $value === "0" ? true : ($value ? true : false);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function _getCacheKeyFromArgumentArray(array $arguments): string
    {
        return sprintf('%s%s_%s', self::CACHE_PREFIX, self::CACHE_ACTION, $this->_generateRequestHash($arguments));
    }

    protected function _generateRequestHash($request): string
    {
        return sha1($this->json->serialize($request));
    }

    private function _calculateETA(string  $oscoETA): string
    {
        //Only ETA for US
        if (!$oscoETA || !in_array($this->_request->getDestCountryId(), $this->_countriesETA)) {
            return '';
        }

        try {
            return self::DELIVERY_DATE_HEAD_TEXT . ' ' . $this->timezone->date($oscoETA)->modify(sprintf('+%s day', $this->_countriesETAAddDays))->format(self::FORMAT_DATE);
        } catch (\Exception $e) {
            return '';
        }
    }

    private function _isCountryRestOfTheWorld(): bool
    {
        return !empty($this->_request->getDestCountryId()) &&
            !in_array($this->_request->getDestCountryId(), [self::USA_COUNTRY_ID, self::CANADA_COUNTRY_ID]);
    }
}
