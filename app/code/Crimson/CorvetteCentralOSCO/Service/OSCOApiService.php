<?php

namespace Crimson\CorvetteCentralOSCO\Service;

use Crimson\CorvetteCentralOSCO\Model\CorvetteCentralOSCOConfig;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Webapi\Rest\Request;
use Psr\Http\Message\ResponseInterface;
use Crimson\CorvetteCentralOSCO\Logger\Logger;
use Magento\Framework\Serialize\SerializerInterface;

class OSCOApiService
{

    CONST OSCO_SUCCESS = 'success';
    CONST USPS_CODE    = 'usps';
    CONST CARRIER_RATE_MAP = [
        'upsrate'   => 'ups',
        'fedexrate' => 'fedex',
        'endiciarate'  => OSCOApiService::USPS_CODE,
    ];

    protected bool $logEnabled = false;
    protected Client $client;

    public function __construct(
        protected CorvetteCentralOSCOConfig $corvetteCentralOSCOConfig,
        protected Logger                    $logger,
        protected ClientFactory             $clientFactory,
        protected SerializerInterface       $serializer,
        protected ResponseFactory           $responseFactory,
    )
    {
        $this->client = $clientFactory->create();
        $this->logEnabled = $this->corvetteCentralOSCOConfig->isLogEnabled();
    }

    public function execute(array $oscoRequest): array
    {
        if (!$this->_isValidRequestData($oscoRequest)) {
            $this->logger->error("= = = Error! No valid OSCO request = = =");
            return [];
        }

        if ($this->logEnabled) {
            $this->logger->debug("= = = OSCO Request Content = = =");
            $this->logger->debug($this->serializer->serialize($oscoRequest));
        }

        $this->logger->debug("* * * * Preparing Request OSCO API * * * *");
        if (!$body = $this->fetch($oscoRequest)->getBody()) {
            $this->logger->error("= = = Error! No Body object in the Response OSCO API = = =");
            return [];
        }

        if (!$content = $body->getContents()) {
            $this->logger->debug("= = = Warning! No Content in the Response OSCO API = = =");
            return [];
        }

        if ($this->logEnabled) {
            $this->logger->debug("= = = OSCO Response Content = = =");
            $this->logger->debug($content);
        }

        $resultRaw = $this->serializer->unserialize($content);
        if (!$this->_isValidResponse($resultRaw)) {
            return [];
        }

        $result = [];
        foreach ($resultRaw as $carrierCode => $carrierRates) {
            $carrierCodeFound = $this->_getCarrierByCarrierRateCode($carrierCode);
            if (!$carrierCodeFound) {
                continue;
            }

            if (!empty($carrierRates['ErrorCode'])) {
                $this->logger->error("= = = Error Code: " . $carrierRates['ErrorCode'] . " = = =");
                if (!empty($carrierRates['ErrorMsg'])) {
                    $this->logger->error("= = = Error Message: " . $carrierRates['ErrorMsg'] . " = = =");
                }
                continue;
            }

            if (empty($carrierRates['Service'])) {
                continue;
            }

            $carrierAllRates = $this->_adjustResponseStructure($carrierRates['Service']);
            if ($carrierCodeFound === OSCOApiService::USPS_CODE) {
                $carrierAllRates = $this->_adjustUSPSFields($carrierAllRates);
            }

            $result[$carrierCodeFound] = $carrierAllRates;
        }

        return $result;
    }

    private function _adjustResponseStructure(array $carrierAllRates): array
    {
        if (isset($carrierAllRates['ServiceType']) || isset($carrierAllRates['ServiceCode']) || isset($carrierAllRates['ServiceDescription'])) {
            $result[] = $carrierAllRates;
        } else {
            $result = $carrierAllRates;
        }

        return $result;
    }

    private function _adjustUSPSFields(array $uspsRates): array
    {
        foreach ($uspsRates as $rateKey => $rateData) {
            if (!isset($rateData['ServiceCode'])) {
                unset($uspsRates[$rateKey]);
            }

            $uspsRates[$rateKey]['ServiceType'] = $rateData['ServiceCode'];
        }

        return $uspsRates;
    }

    private function fetch(array $oscoRequest): ResponseInterface
    {
        try {
            $oscoRequestJson = json_encode($oscoRequest);
            $params = [
                RequestOptions::AUTH => [
                    $this->corvetteCentralOSCOConfig->getUserName(),
                    $this->corvetteCentralOSCOConfig->getPassword(),
                    'Basic'
                ],
                RequestOptions::HEADERS => [
                    'content-type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                RequestOptions::BODY => $oscoRequestJson,
            ];

            $this->logger->debug("= = = Fetching OSCO API = = =");
            if ($this->logEnabled) {
                $this->logger->debug("= = = OSCO Request Content = = =");
                $this->logger->debug($oscoRequestJson);
            }
            $response = $this->client->post($this->corvetteCentralOSCOConfig->getUrl(), $params);
            $this->logger->debug("= = = Response Received OSCO API = = =");
        } catch (GuzzleException $exception) {
            $response = $this->responseFactory->create([
                'status' => $exception->getCode() == 0 ? 500 : $exception->getCode(),
                'reason' => $exception->getMessage(),
            ]);
            $this->logger->error('Unable to fetch from the OSCO APi', ['Exception' => $exception->getMessage()]);
        } finally {
            return $response;
        }
    }

    private function _isValidResponse(array $data): bool
    {
        if (!empty($data['ERROR_CODE']) && strtolower($data['ERROR_CODE']) === self::OSCO_SUCCESS) {
            return true;
        }

        if (!empty($data['ERROR_MESSAGE'])) {
            $this->logger->error("Response Error: " . $data['ERROR_MESSAGE']);
        }

        return false;
    }

    private function _isValidRequestData(array $data): bool
    {
        if (!$data) {
            return false;
        }

        if (empty($data['OSCO_DATA']['ORDER']['ORDER_ID'])) {
            return false;
        }

        if (empty($data['OSCO_DATA']['RateShop']['ShipFromAddress'])) {
            return false;
        }

        if (empty($data['OSCO_DATA']['RateShop']['ShipToAddress'])) {
            return false;
        }

        if (empty($data['OSCO_DATA']['SKUDATA'])) {
            return false;
        }

        return true;
    }

    private function _getCarrierByCarrierRateCode(string $rateCode): string
    {
        foreach (self::CARRIER_RATE_MAP as $carrierRateCode => $carrier) {
            if ($carrierRateCode === strtolower($rateCode)) {
                return $carrier;
            }
        }

        return '';
    }
}
