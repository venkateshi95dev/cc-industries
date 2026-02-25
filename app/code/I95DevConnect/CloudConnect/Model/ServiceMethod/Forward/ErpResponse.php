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
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class to sync ERP Response
 */
class ErpResponse
{
    /**
     * @var I95DevServerRepositoryInterfaceFactory
     */
    public $i95DevRepository;

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
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    public $I95DevMagMQRepository;

    /**
     * Constructor for DI
     * @param Data $cloudHelper
     * @param I95DevServerRepositoryInterfaceFactory $i95DevRepository
     * @param Logger $logger
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param RequestFactory $request
     * @param ServiceMethodFactory $serviceMethod
     * @param I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepository
     */
    public function __construct(
        Data $cloudHelper,
        I95DevServerRepositoryInterfaceFactory $i95DevRepository,
        Logger $logger,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        RequestFactory $request,
        ServiceMethodFactory $serviceMethod,
        I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepository
    ) {
        $this->cloudHelper = $cloudHelper;
        $this->i95DevRepository = $i95DevRepository;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->request = $request;
        $this->serviceMethod = $serviceMethod;
        $this->I95DevMagMQRepository = $I95DevMagMQRepository;
    }

    /**
     * Forward info sync implementation
     *
     * @param array $inputs
     * @param string $entity
     * @param string $requestType
     * @param string $action
     * @return array
     * @throws LocalizedException
     */
    public function sync($inputs, $entity, $requestType, $action = null) // NOSONAR
    {
        try {
            $destinationId = [];
            if ($this->cloudHelper->isEnabled() && (is_array($inputs) && !empty($inputs))) {
                $finalData = [];
                foreach ($inputs as $input) {
                    $data = [];
                    $destinationId[] = $input['messageId'];
                    $msg_id = $this->I95DevMagMQRepository->create()
                        ->load($input['messageId'], 'destination_msg_id')->getMsgId();
                    if (isset($msg_id) && $msg_id > 0) {
                        $data['inputData'] = $input['inputData'];
                        $data['message'] = $input['message'];
                        $data['messageId'] = $msg_id;
                        $data['targetId'] = $input['targetId'];
                        $data['sourceId'] = $input['sourceId'];
                        $finalData['requestData'][] = $data;
                    }
                }
                $apiMethodsRoutes = $this->i95DevRepository->create()->apiServiceMethodRoutes;
                $apiMethods = $this->serviceMethod->create()
                    ->getForwardApiMethods($apiMethodsRoutes, 'forward', $action);
                if (array_key_exists($entity, $apiMethods)) {
                    $this->i95DevRepository->create()->serviceMethod(
                        $apiMethods[$entity],
                        $this->jsonHelper->jsonEncode($finalData),
                        $this->cloudHelper->getErpComponent()
                    );
                }
            }
        } catch (Exception $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                Logger::EXCEPTION,
                'critical'
            );
        }
        return $destinationId;
    }
}
