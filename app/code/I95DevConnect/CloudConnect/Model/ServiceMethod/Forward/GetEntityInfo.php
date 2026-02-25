<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model\ServiceMethod\Forward;

use Exception;
use I95DevConnect\CloudConnect\Helper\Data;
use I95DevConnect\CloudConnect\Model\Logger;
use I95DevConnect\CloudConnect\Model\RequestFactory;
use I95DevConnect\CloudConnect\Model\ServiceMethod\ServiceMethodFactory;
use I95DevConnect\I95DevServer\Api\I95DevServerRepositoryInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class to get Entity wise Information
 */
class GetEntityInfo
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
     * @var ServiceMethodFactory
     */
    public $serviceMethod;

    /**
     *
     * @param Data $cloudHelper
     * @param I95DevServerRepositoryInterfaceFactory $i95DevRepository
     * @param Logger $logger
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param RequestFactory $request
     * @param ServiceMethodFactory $serviceMethod
     */
    public function __construct(
        Data $cloudHelper,
        I95DevServerRepositoryInterfaceFactory $i95DevRepository,
        Logger $logger,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        RequestFactory $request,
        ServiceMethodFactory $serviceMethod
    ) {
        $this->cloudHelper = $cloudHelper;
        $this->i95DevRepository = $i95DevRepository;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->request = $request;
        $this->serviceMethod = $serviceMethod;
    }

    /**
     * Sync function
     *
     * @param type $input
     * @param type $entity
     * @param string $requestType
     * @param type $action
     * @return I95DevConnect\MessageQueue\Model\I95DevResponse|null
     * @throws LocalizedException
     */
    public function sync($input, $entity, $requestType = null, $action = null) //NOSONAR
    {
        $dataList = null;
        try {
            if ($this->cloudHelper->isEnabled()) {
                $apiMethodsRoutes = $this->i95DevRepository->create()->apiServiceMethodRoutes;
                $apiMethods = $this->serviceMethod->create()
                    ->getForwardApiMethods($apiMethodsRoutes, 'forward', $action);
                if (array_key_exists($entity, $apiMethods)) {
                    $dataList = $this->i95DevRepository->create()->serviceMethod(
                        $apiMethods[$entity],
                        $input,
                        $this->cloudHelper->getErpComponent()
                    );
                }
            }
        } catch (Exception $e) {
            $this->logger->createLog(
                __METHOD__,
                $e->getMessage(),
                Logger::EXCEPTION,
                'critical'
            );
        }
        return $dataList;
    }
}
