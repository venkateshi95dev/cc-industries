<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Helper;

use Exception;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class to send service request
 */
class ServiceRequest extends AbstractHelper
{
    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EncryptorInterface
     */
    protected $enc;

    /**
     * ServiceRequest constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Curl $curl
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $enc
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Curl $curl,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $enc
    ) {
        $this->curl = $curl;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->enc = $enc;
        parent::__construct($context);
    }

    /**
     * Curl request
     *
     * @param string $methodtype
     * @param string $curlurl
     * @param string $string
     * @param string $url_param
     * @return string
     * @throws LocalizedException
     */
    public function curlRequest($methodtype, $curlurl, $string = null, $url_param = null)
    {
        try {
            $isI95DevRestReq = (isset($url_param['isI95DevRestReq'])) ? $url_param['isI95DevRestReq'] : 0;

            $putData = (isset($url_param['putData'])) ? $url_param['putData'] . "/" : null;
            $this->logger->createLog(
                __METHOD__,
                "===Magento API End Point===" . $curlurl,
                LoggerInterface::MSGLOGNAME,
                'info'
            );
            if ($string != null) {
                $this->logger->createLog(
                    __METHOD__,
                    "===Request String to Magento API===" . json_encode($string, JSON_UNESCAPED_UNICODE),
                    LoggerInterface::MSGLOGNAME,
                    'info'
                );
            }
            $url = $this->storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_DIRECT_LINK,
                true
            );
            $url .= 'index.php/rest/';
            $token = $this->getToken();
            $this->curl->setHeaders(["Content-Type" => "application/json",
                "Authorization" => "Bearer $token"]);
            if ($methodtype == 'GET') {
                $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'GET');
                $serviceUrl = $url . $curlurl . '/?isI95DevRestReq=' . $isI95DevRestReq;
                $this->curl->get($serviceUrl);
            } elseif ($methodtype == 'POST') {
                $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'POST');
                $this->curl->setOption(CURLOPT_POSTFIELDS, json_encode($string, JSON_UNESCAPED_UNICODE));
                $serviceUrl = $url . $curlurl . '/?isI95DevRestReq=' . $isI95DevRestReq;
                $this->curl->post($serviceUrl, $string);
            } elseif ($methodtype == 'PUT') {
                $serviceUrl = $url . $curlurl  . $putData;
                if (!empty($isI95DevRestReq)) {
                    $serviceUrl .= '?isI95DevRestReq=' . $isI95DevRestReq;
                }

                $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
                $this->curl->setOption(CURLOPT_POSTFIELDS, json_encode($string, JSON_UNESCAPED_UNICODE));
                $this->curl->post($serviceUrl, $string);
            }
        } catch (Exception $ex) {
            $this->logger->createLog(
                '__METHOD__',
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
            throw new LocalizedException(__($ex->getMessage()));
        }
        $result = json_decode($this->curl->getBody(), 1);
        $this->checkServiceResponse($result);
        return $result;
    }

    /**
     * To get token
     *
     * @return boolean
     * @throws LocalizedException
     */
    public function getToken()
    {
        try {
            $storeScope = ScopeInterface::SCOPE_WEBSITE;
            $token = $this->scopeConfig->getValue(
                'i95dev_messagequeue/I95DevConnect_credentials/token',
                $storeScope,
                $this->storeManager->getDefaultStoreView()->getWebsiteId()
            );
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
            throw new LocalizedException(__($ex->getMessage()));
        }
        if (!empty($token) && !is_object($token)) {
            return $token;
        } else {
            return false;
        }
    }

    /**
     * Will check service response
     *
     * @param array $result
     * @throws LocalizedException
     */
    public function checkServiceResponse($result)
    {
        if (isset($result['message'])) {
            throw new LocalizedException(__($result['message']));
        }
    }
}
