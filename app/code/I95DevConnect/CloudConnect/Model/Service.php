<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model;

use I95DevConnect\CloudConnect\Api\Data\ResponseInterfaceFactory;
use I95DevConnect\CloudConnect\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigCollection;

/**
 * Class for service request
 */
class Service extends AbstractModel
{
    public const CLOUD_ACCESS_TOKEN = 'cloud_access_token';
    public const CLOUD_ACCESS_TOKEN_EXPIRY = 'cloud_access_token_expiry';
    public const TOKEN = 'token';
    public const EXPIRY = 'expiry';
    /**
     * scopeConfig for system Congiguration
     *
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     *
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var Data
     */
    public $cloudHelper;
    /**
     * @var Curl
     */
    public $curl;
    /**
     * @var ResponseInterfaceFactory
     */
    public $responseInterface;
    /**
     * @var LoggerFactory
     */
    public $logger;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;
    /**
     * @var $devResponse
     */
    public $devResponse;
    /**
     * @var Request
     */
    public $request;
    /**
     * @var string
     */
    public $serviceEntityCode;

    /**
     * @var DateTimeFactory
     */
    public $dateTime;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var ConfigCollection
     */
    protected $configCollection;

    /**
     * @var \Magento\Framework\Module\ResourceInterface
     */
    protected $moduleResource;
    /**
     * Constructor for DI
     * @param Data $cloudHelper
     * @param Curl $curl
     * @param ResponseInterfaceFactory $responseInterface
     * @param LoggerFactory $logger
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Request $request
     * @param string $serviceEntityCode
     * @param DateTimeFactory $dateTime
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ConfigCollection $configCollection
     * @param \Magento\Framework\Module\ResourceInterface $moduleResource
     */
    public function __construct( // NOSONAR
        Data $cloudHelper,
        Curl $curl,
        ResponseInterfaceFactory $responseInterface,
        LoggerFactory $logger,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Request $request,
        $serviceEntityCode,
        DateTimeFactory $dateTime,
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ConfigCollection $configCollection,
        \Magento\Framework\Module\ResourceInterface $moduleResource
    ) {
        $this->cloudHelper = $cloudHelper;
        $this->curl = $curl;
        $this->responseInterface = $responseInterface;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->request = $request;
        $this->serviceEntityCode = $serviceEntityCode;
        $this->dateTime = $dateTime;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->configCollection = $configCollection;
        $this->moduleResource = $moduleResource;
    }

    /**
     * Make call to get the schedular id
     *
     * @param string $schedulerType
     * @param string $entity
     * @param Object $requestData
     * @param int $schedulerId
     * @param string $otherUrl
     * @return Object
     */
    public function makeServiceCall(
        $schedulerType,
        $entity = null,
        $requestData = null,
        $schedulerId = 0,
        $otherUrl = ''
    ) {
        try {
            if (empty($requestData)) {
                $requestParam = $this->request->cloudRequest($schedulerType, $entity, $schedulerId);
            } else {
                $requestParam = $requestData;
            }

            return $this->_doRestCall($requestParam, $schedulerType, $entity, $otherUrl, $schedulerId);
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                'PullDataCron',
                $ex->getMessage(),
                Logger::EXCEPTION,
                Logger::CRITICAL
            );

            return null;
        }
    }

    /**
     * Soap call
     *
     * @param Object $transport
     * @param string $schedulerType
     * @param string $entity
     * @param string $otherUrl
     * @param int $schedulerId
     * @return Object
     */
    // @codingStandardsIgnoreStart
    public function _doRestCall($transport, $schedulerType, $entity = null, $otherUrl = '', $schedulerId = 0)
    {
        $finalResponse = $this->jsonHelper->jsonEncode(json_decode("{}"));

        try {
            $targetUrl = $this->cloudHelper->getTargetUrl();
            if (!$this->cloudHelper->isEnabled()) {
                $this->setDevResponse(
                    "",
                    "Please Enable i95Dev Generic Connector",
                    $finalResponse,
                    true,
                    false,
                    $schedulerId
                );
                return $this->devResponse;
            }
            $logAs = 'info';

            if (!empty($entity)) {
                if (isset($this->serviceEntityCode[$entity])) {
                    $entity = $this->serviceEntityCode[$entity];
                }
                $entity = $this->getEntityCodeWithoutSplChars($entity);
                $finalUrl = $targetUrl . '/' . $entity . '/' . $schedulerType;
            } else {
                $finalUrl = $targetUrl . '/Index';
                $logAs = 'debug';
            }

            if ($otherUrl != '') {
                $finalUrl = $targetUrl . '/Mapping/' . $otherUrl;
            }

            try {
                //To encrypt transport data
                $encryptObj = $this->jsonHelper->jsonEncode($transport);
                $logType = $this->logger->create()->getEntityLogType($schedulerType, $entity);

                $refreshToken = $this->cloudHelper->getApiAuthenticationToken();
                $accessToken = '';
                $accessTokenData = $this->configCollection->create()
                    ->addFieldToFilter('path', self::CLOUD_ACCESS_TOKEN);
                if ($accessTokenData->getSize()) {
                    $accessToken = $accessTokenData->getLastItem()->getData('value');
                }
                $accessToken = $this->getAccessTokenDetails($accessToken, $refreshToken);
                $response = $this->curlCall($accessToken, $encryptObj, $finalUrl);
                $this->logger->create()->createLog(
                    "Request to: " . $finalUrl . PHP_EOL .
                    "Request data to cloud: " . $encryptObj,
                    "Response data from cloud: " . $response,
                    $logType,
                    $logAs
                );

                $responseObject = !empty($response) ? json_decode($response) : "";
                if (is_object($responseObject)) {
                    $this->processResponseObject($responseObject);
                } else {
                    $this->setDevResponse("", "empty response received", false, true, false, $schedulerId);
                }
            } catch (LocalizedException $e) {
                $msg = $e->getMessage();
                $errorMessage = str_replace("Server was unable to process request. --->", "", $msg);
                $this->logger->create()->createLog(
                    __METHOD__,
                    $errorMessage,
                    Logger::EXCEPTION,
                    'critical'
                );
                $this->setDevResponse("", $errorMessage, $finalResponse, true, false, $schedulerId);
            }
        } catch (LocalizedException $ex) {
            $msg = $ex->getMessage();
            $this->logger->create()->createLog(
                __METHOD__,
                $msg,
                Logger::EXCEPTION,
                'critical'
            );
            $this->setDevResponse("", $msg, $finalResponse, true, false, $schedulerId);
        }
        return $this->devResponse;
    }
    // @codingStandardsIgnoreEnd

    /**
     * Method to set response
     *
     * @param bool $isConfiguration
     * @param string $msg
     * @param string $resultData
     * @param bool $isActive
     * @param bool $result
     * @param string $schedularId
     */
    public function setDevResponse($isConfiguration, $msg, $resultData, $isActive, $result, $schedularId)
    {
        $this->devResponse = $this->responseInterface->create();
        if (is_array($isConfiguration)) {
            $this->devResponse->setIsConfigurationUpdated($isConfiguration['entity']);
            $this->devResponse->setIsShippingMappingUpdated($isConfiguration['shipping']);
            $this->devResponse->setIsPaymentMappingUpdated($isConfiguration['payment']);
            $this->devResponse->setIsProductAttributeMappingUpdated($isConfiguration['productattr']);
        }

        $this->devResponse->setMessage($msg);
        $this->devResponse->setResultData($resultData);
        $this->devResponse->setIsSubscriptionActive($isActive);
        $this->devResponse->setResult($result);
        $this->devResponse->setSchedulerId($schedularId);
    }

    /**
     * Get access token details
     *
     * @param string $accessToken
     * @param string $refreshToken
     * @return false|mixed|string
     */
    public function getAccessTokenDetails($accessToken, $refreshToken)
    {
        $curDate = strtotime($this->dateTime->create()->gmtDate());
        $accessTokenDetails = [];
        if ($accessToken) {
            $expiry = '';
            $expiryData = $this->configCollection->create()
                ->addFieldToFilter('path', self::CLOUD_ACCESS_TOKEN_EXPIRY);
            if ($expiryData->getSize()) {
                $expiry = strtotime((string)$expiryData->getLastItem()->getData('value'));
            }

            if ($expiry < $curDate) {
                $accessTokenDetails = $this->getAccessToken($refreshToken);
                $this->setConfigData(self::CLOUD_ACCESS_TOKEN, $accessTokenDetails[self::TOKEN]);
                $this->setConfigData(self::CLOUD_ACCESS_TOKEN_EXPIRY, $accessTokenDetails[self::EXPIRY]);
                $accessToken = isset($accessTokenDetails['token']) ? $accessTokenDetails['token'] : false;
            }
        } else {
            $accessTokenDetails = $this->getAccessToken($refreshToken);
            $this->setConfigData(self::CLOUD_ACCESS_TOKEN, $accessTokenDetails[self::TOKEN]);
            $this->setConfigData(self::CLOUD_ACCESS_TOKEN_EXPIRY, $accessTokenDetails[self::EXPIRY]);
            $accessToken = isset($accessTokenDetails['token']) ? $accessTokenDetails['token'] : false;
        }

        return $accessToken;
    }

    /**
     * Get access token
     *
     * @param string $refreshToken
     * @return array|bool|string[]
     */
    public function getAccessToken($refreshToken)
    {
        try {
            $finalUrl = $this->cloudHelper->getTargetUrl() . "/Client/Token";
            $encryptObj = $this->jsonHelper->jsonEncode(["refreshToken" => $refreshToken]);
            $this->curl->setHeaders([
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($encryptObj)
            ]);
            $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'POST');
            $this->curl->setOption(CURLOPT_POSTFIELDS, $encryptObj);
            $this->curl->post($finalUrl, $encryptObj);
            $response = $this->curl->getBody();

            $this->logger->create()->createLog(
                "Request to: " . $finalUrl . PHP_EOL .
                "Request data to cloud: " . $encryptObj,
                "Response data from cloud: " . $response,
                "AccessToken",
                'info'
            );
            $responseObject = !empty($response) ? $this->jsonHelper->jsonDecode($response) : "";
            if (is_array($responseObject) && isset($responseObject['accessToken'])) {
                return [
                    'token' => $responseObject['accessToken']['token'],
                    'expiry' => $responseObject['accessToken']['expiryTime']
                ];
            }
        } catch (LocalizedException $e) {
            $this->logger->create()->createLog(
                __METHOD__,
                $e->getMessage(),
                Logger::EXCEPTION,
                'critical'
            );
        }

        return [];
    }

    /**
     * Get Configuration Data
     *
     * @param string $path
     * @param string $value
     */
    public function setConfigData(string $path, string $value)
    {
        $this->configWriter->save(
            $path,
            $value
        );
    }

    /**
     * Curl call
     *
     * @param string $token
     * @param object $encryptObj
     * @param string $finalUrl
     * @return mixed
     */
    public function curlCall($token, $encryptObj, $finalUrl)
    {
        $authorization = "Bearer $token";
        $version = $this->moduleResource->getDbVersion('I95DevConnect_CloudConnect');
        $userAgent = 'i95DevMagentoConnector-'.$version;
        $this->curl->setHeaders([
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($encryptObj),
            'Authorization' => $authorization,
            'User-Agent' => $userAgent
        ]);
        $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'POST');
        $this->curl->setOption(CURLOPT_POSTFIELDS, $encryptObj);
        $this->curl->post($finalUrl, $encryptObj);
        return $this->curl->getBody();
    }

    /**
     * Process response object
     *
     * @param object $responseObject
     */
    public function processResponseObject($responseObject)
    {
        $isConfiguration = [
            'entity' => (isset($responseObject->isConfigurationUpdated)) ?
                $responseObject->isConfigurationUpdated : false,
            'payment' => (isset($responseObject->isPaymentMappingUpdated)) ?
                $responseObject->isPaymentMappingUpdated : false,
            'shipping' => (isset($responseObject->isShippingMappingUpdated)) ?
                $responseObject->isShippingMappingUpdated : false,
            'productattr' => (isset($responseObject->isProductAttributeMappingUpdated)) ?
                $responseObject->isProductAttributeMappingUpdated : false // Updated by Subhan
        ];

        $resultant = isset($responseObject->resultData) ? $responseObject->resultData : null;
        $subscriptionActive = $responseObject->isSubscriptionActive ?? null;
        $this->setDevResponse(
            $isConfiguration,
            (isset($responseObject->message)) ? $responseObject->message : null,
            $resultant,
            $subscriptionActive,
            (isset($responseObject->result)) ? $responseObject->result : null,
            (isset($responseObject->schedulerId)) ? $responseObject->schedulerId : false
        );
    }

    /**
     * Get entity code without special characters
     *
     * @param string $entityCode
     * @return string
     */
    public function getEntityCodeWithoutSplChars($entityCode)
    {
        switch ($entityCode) {
            case 'item_discount_group':
                $revisedEntityCode = 'ItemDiscountGroup';
                break;
            case 'customer_discount_group':
                $revisedEntityCode = 'CustomerDiscountGroup';
                break;
            case 'discount_calculation':
                $revisedEntityCode = 'DiscountPrice';
                break;
            default:
                $revisedEntityCode = $entityCode;
        }
        return $revisedEntityCode;
    }
}
