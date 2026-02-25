<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model\ServiceMethod\Reverse;

use Exception;
use I95DevConnect\CloudConnect\Api\Data\RequestInterfaceFactory;
use I95DevConnect\CloudConnect\Helper\Data;
use I95DevConnect\CloudConnect\Model\Logger;
use I95DevConnect\CloudConnect\Model\RequestFactory;
use I95DevConnect\CloudConnect\Model\ServiceMethod\ServiceMethod;
use I95DevConnect\I95DevServer\Api\I95DevServerRepositoryInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Model class to get data from cloud
 */
class Reverse extends ServiceMethod
{
    /**
     * @var I95DevServerRepositoryInterfaceFactory
     */
    private $i95DevRepository;
    /**
     * @var Data
     */
    public $cloudHelper;
    /**
     * @var RequestInterfaceFactory
     */
    public $requestInterface;
    /**
     * @var $magentoMessageQueue
     */
    public $magentoMessageQueue;

    /**
     * scopeConfig for system Configuration
     *
     * @var string
     */
    public $scopeConfig;
    /**
     * @var string
     */
    public $erpName = 'ERP';
    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;
    /**
     * @var RequestFactory
     */
    public $request;
    /**
     * @var $data
     */
    public $data;

    /**
     * Constructor for DI
     * @param Data $cloudHelper
     * @param I95DevServerRepositoryInterfaceFactory $i95DevRepository
     * @param RequestInterfaceFactory $requestInterface
     * @param RequestFactory $request
     * @param Logger $logger
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Data $cloudHelper,
        I95DevServerRepositoryInterfaceFactory $i95DevRepository,
        RequestInterfaceFactory $requestInterface,
        RequestFactory $request,
        Logger $logger,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->cloudHelper = $cloudHelper;
        $this->i95DevRepository = $i95DevRepository;
        $this->requestInterface = $requestInterface;
        $this->request = $request;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Sync function
     *
     * @param array $request
     * @param string $entity
     * @param string $requestType
     * @param string $action
     * @return object|bool
     * @throws LocalizedException
     */
    public function sync($request, $entity, $requestType, $action = null)
    {
        $devResponse = $this->requestInterface->create();
        try {
            if ($this->cloudHelper->isEnabled()) {
                $requestList = (array)$request;
                $resultData = isset($requestList['ResultData']) ? $requestList['ResultData'] : [];
                if (!empty($resultData)) {
                    $requestList['RequestData'] = (array)$resultData;
                    unset($resultData);

                    $devResponse->setContext(
                        $this->request->create()->prepareContextObject($requestType, $requestList['SchedulerId'])
                    );
                    $apiMethodsRoutes = $this->i95DevRepository->create()->apiServiceMethodRoutes;
                    $apiMethods = $this->getReverseApiMethods($apiMethodsRoutes, 'reverse');

                    if (array_key_exists($entity, $apiMethods)) {
                        return $this->i95DevRepository->create()->serviceMethod(
                            $apiMethods[$entity],
                            $this->jsonHelper->jsonEncode($requestList),
                            $this->cloudHelper->getErpComponent()
                        );
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->createLog(
                __METHOD__,
                $action . " :: " . $e->getMessage(),
                Logger::EXCEPTION,
                'critical'
            );
        }

        return false;
    }
}
