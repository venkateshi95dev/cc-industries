<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Model;

use Exception;
use I95DevConnect\I95DevServer\Api\I95DevServerRepositoryInterface;
use I95DevConnect\I95DevServer\Model\ServiceMethod\ForwardSync;
use I95DevConnect\I95DevServer\Model\ServiceMethod\ReverseSync;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\I95DevResponse;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Model class for implementing I95DevServerRepositoryInterface
 */
class I95DevServerRepository implements I95DevServerRepositoryInterface
{
    public const RESPONSE = "===Response===";
    public const ERROR_STATUS = "Error";

    /**
     * @var string
     */
    public $erpName = "ERP";
    /**
     * @var LoggerInterface
     */
    public $logger;
    /**
     * @var Data
     */
    public $helperData;
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     * @var I95DevResponse
     */
    public $i95DevResponse;
    /**
     * @var ServiceMethod\ReverseSync
     */
    public $reverseSync;
    /**
     * @var $currentMethodProperties
     */
    public $currentMethodProperties;
    /**
     * @var ServiceMethod\ForwardSync
     */
    public $forwardSync;
    /**
     * @var mixed
     */
    public $apiServiceMethodRoutes;
    /**
     * @var $currententitiyCode
     */
    public $currententitiyCode;

    /**
     * Constructor for DI
     *
     * @param LoggerInterface      $logger
     * @param Data                 $helperData
     * @param ScopeConfigInterface $scopeConfig
     * @param I95DevResponse       $i95DevResponse
     * @param ReverseSync          $reverseSync
     * @param ForwardSync          $forwardSync
     * @param mixed                $apiServiceMethodRoutes
     */
    public function __construct(
        LoggerInterface $logger,
        Data $helperData,
        ScopeConfigInterface $scopeConfig,
        I95DevResponse $i95DevResponse,
        ReverseSync $reverseSync,
        ForwardSync $forwardSync,
        $apiServiceMethodRoutes
    ) {

        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->scopeConfig = $scopeConfig;
        $this->i95DevResponse = $i95DevResponse;
        $this->reverseSync = $reverseSync;
        $this->apiServiceMethodRoutes = $apiServiceMethodRoutes;
        $this->forwardSync = $forwardSync;
    }

    /**
     * Method to process inbound records to Magento
     */
    public function syncMQtoMagento()
    {
        try {
            $this->reverseSync->syncMQtoMagento();
        } catch (Exception $e) {
            $this->logger->createLog(
                __METHOD__,
                $e->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function serviceMethod($methodName, $inputString = null, $erpName = null)
    {
        if ($this->helperData->isEnabled()) {
            $this->erpName = ($erpName) ? $erpName : __($this->helperData->getComponent());

            $this->logger->createLog(__METHOD__, "===Request===" . $inputString, $methodName, 'info');
            try {
                $response = [];

                if ($this->checkMethodExistsInService($methodName)) {
                    $methodType = $this->currentMethodProperties['methodType'];
                    $this->currententitiyCode = $this->currentMethodProperties['entityCode'];
                    if ($methodType == 'reverse') {
                        $response = $this->reverseSync->syncDataToMQ(
                            $methodName,
                            $inputString,
                            $this->currentMethodProperties,
                            $this->erpName
                        );
                    } else {
                        $response = $this->routeForwardEntity($inputString, $methodName);
                    }
                } else {
                    $this->logger->createLog(
                        __METHOD__,
                        self::RESPONSE . json_encode($this->i95DevResponse),
                        $methodName,
                        'info'
                    );
                    $response = $this->i95DevResponse;
                }
            } catch (LocalizedException | Exception $e) {
                $this->logger->createLog(
                    __METHOD__,
                    $e->getMessage(),
                    LoggerInterface::I95EXC,
                    'critical'
                );
            }
            $this->logger->createLog(__METHOD__, self::RESPONSE . json_encode($response), $methodName, 'info');
            return $response;
        } else {
            $this->i95DevResponse->setStatus(self::ERROR_STATUS);
            $this->i95DevResponse->setMessage("I95Dev Connector Extension is disabled");
            $this->logger->createLog(
                __METHOD__,
                self::RESPONSE . json_encode($this->i95DevResponse),
                $methodName,
                'info'
            );
            return $this->i95DevResponse;
        }
    }

    /**
     * Route forward entity
     *
     * @param  string $inputString
     * @param  string $methodName
     * @return I95DevResponse|Object
     * @throws Exception
     */
    public function routeForwardEntity($inputString, $methodName)
    {
        if (isset($this->currentMethodProperties['sendInfo'])) {
            $response = $this->forwardSync->sendEntityData(
                $this->currententitiyCode,
                $inputString,
                $this->erpName
            );
        } elseif (isset($this->currentMethodProperties['setResponse'])) {
            $response = $this->forwardSync->sendEntityResponse(
                $this->currentMethodProperties['entityCode'],
                $inputString,
                $this->erpName
            );
        } elseif (isset($this->currentMethodProperties['setMQResponse'])) {
            $response = $this->reverseSync->getMessageQueueStatus($inputString);
        } elseif (isset($this->currentMethodProperties['setMQResponseAck'])) {
            $response = $this->reverseSync->setMessageQueueAck($inputString);
        } else {
            $this->i95DevResponse->setStatus(self::ERROR_STATUS);
            $this->i95DevResponse->setMessage('Method Not exists');
            $this->logger->createLog(
                __METHOD__,
                self::RESPONSE . json_encode($this->i95DevResponse),
                $methodName,
                'info'
            );
            $response = $this->i95DevResponse;
        }

        return $response;
    }
    /**
     * Check method exist in service
     *
     * @param  string $methodName
     * @return bool
     */
    private function checkMethodExistsInService($methodName)
    {
        if (isset($this->apiServiceMethodRoutes[$methodName])) {
            $this->currentMethodProperties = $this->apiServiceMethodRoutes[$methodName];
            return true;
        }

        $this->i95DevResponse->setStatus(self::ERROR_STATUS);
        $this->i95DevResponse->setMessage('Method Not exists');
        return false;
    }
}
