<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model;

use I95DevConnect\CloudConnect\Api\Data\ContextInterfaceFactory;
use I95DevConnect\CloudConnect\Api\Data\RequestInterfaceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Model\AbstractModel;
use I95DevConnect\CloudConnect\Model\Logger;

/**
 * class to get request from cloud to magento
 */
class Request extends AbstractModel
{
    /**
     * @var int
     */
    public $PacketSize = 0;

    /**
     * @var array
     */
    public $RequestData = [];

    /**
     * @var \95DevConnect\CloudConnect\Model\Data\Context
     */
    public $Context;

    /**
     * @var string
     */
    public $type;

    /**
     * @var \I95DevConnect\CloudConnect\Model\Logger
     */
    public $logger;

    /**
     * @var RequestInterfaceFactory
     */
    public $requestInterface;

    /**
     * @var ContextInterfaceFactory
     */
    public $contextInterface;

    /**
     * scopeConfig for system Configuration
     *
     * @var string
     */
    public $scopeConfig;

    /**
     * @var Data
     */
    public $jsonHelper;

    /**
     * Constructor for DI
     * @param Logger $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterfaceFactory $requestInterface
     * @param ContextInterfaceFactory $contextInterface
     * @param Data $jsonHelper
     * @param \I95DevConnect\CloudConnect\Helper\Data $cloudHelper
     */
    public function __construct(
        Logger $logger,
        ScopeConfigInterface $scopeConfig,
        RequestInterfaceFactory $requestInterface,
        ContextInterfaceFactory $contextInterface,
        Data $jsonHelper,
        \I95DevConnect\CloudConnect\Helper\Data $cloudHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->requestInterface = $requestInterface;
        $this->contextInterface = $contextInterface;
        $this->jsonHelper = $jsonHelper;
        $this->cloudHelper = $cloudHelper;
    }

    /**
     * Method to create object for cloud request
     *
     * @param string $schedulerType
     * @param string $entity
     * @param string $schedulerId
     * @return object
     * @throws LocalizedException
     */
    public function cloudRequest($schedulerType, $entity, $schedulerId)
    {
        $request = $this->requestInterface->create();
        try {
            if ($entity == null) {
                $request->setContext($this->prepareContextObject($schedulerType, $schedulerId));
                $request->setPacketSize($this->PacketSize);
            }
        } catch (LocalizedException $e) {
            $this->logger->createLog(
                __METHOD__,
                $e->getMessage(),
                Logger::EXCEPTION,
                'critical'
            );
        }

        return $request;
    }

    /**
     * Method to prepare Context object
     *
     * @param string $schedulerType
     * @param string $schedulerId
     * @return object
     * @throws LocalizedException
     */
    public function prepareContextObject($schedulerType, $schedulerId)
    {
        $context = $this->contextInterface->create();
        try {
            $clientId = $this->cloudHelper->getConfigClientId();
            $subscriptionKey = $this->cloudHelper->getConfigSubscriptionKey();
            $endpointCode = $this->cloudHelper->getConfigEndpointCode();
            $instanceType = $this->cloudHelper->getInstanceType();
            if ($schedulerId && !empty($schedulerId)) {
                $context->setSchedulerId($schedulerId);
            }

            $context->setRequestType("Source");
            $context->setSchedulerType($schedulerType);
            $context->setClientId($clientId);
            $context->setSubscriptionKey($subscriptionKey);
            $context->setEndpointCode($endpointCode);
            $context->setInstanceType($instanceType);
        } catch (LocalizedException $e) {
            $this->logger->createLog(
                __METHOD__,
                $e->getMessage(),
                Logger::EXCEPTION,
                'critical'
            );
        }
        return $context;
    }
}
