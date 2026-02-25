<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Controller\Payment;

use I95DevConnect\CloudConnect\Api\Data\RequestInterfaceFactory;
use I95DevConnect\CloudConnect\Model\Logger;
use I95DevConnect\CloudConnect\Model\Request;
use I95DevConnect\CloudConnect\Model\Service;
use I95DevConnect\PaymentMapping\Api\Data\PaymentMappingDataInterfaceFactory;
use I95DevConnect\PaymentMapping\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Controller for rendering data string in Message Queue
 */
class SendMapping extends Action
{
    /**
     * @var Service
     */
    public $service;
    /**
     * @var RequestInterfaceFactory
     */
    public $requestInterface;
    /**
     * @var Request
     */
    public $request;
    /**
     * @var Logger
     */
    public $logger;
    /**
     * @var JsonFactory
     */
    public $jsonResultFactory;
    /**
     * @var Data
     */
    public $helper;
    /**
     * @var PaymentMappingDataInterfaceFactory
     */
    public $paymentMappingData;

    /**
     * @param Context $context
     * @param Service $service
     * @param RequestInterfaceFactory $requestInterface
     * @param Request $request
     * @param Logger $logger
     * @param JsonFactory $jsonResultFactory
     * @param Data $helper
     * @param PaymentMappingDataInterfaceFactory $paymentMappingData
     */
    public function __construct( // NOSONAR
        Context $context,
        Service $service,
        RequestInterfaceFactory $requestInterface,
        Request $request,
        Logger $logger,
        JsonFactory $jsonResultFactory,
        Data $helper,
        PaymentMappingDataInterfaceFactory $paymentMappingData
    ) {
        $this->service = $service;
        $this->requestInterface = $requestInterface;
        $this->request = $request;
        $this->logger = $logger;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->helper = $helper;
        $this->paymentMappingData = $paymentMappingData;

        parent::__construct($context);
    }

    /**
     * Push the default mapping data
     *
     * @return Json
     * @throws LocalizedException
     */
    public function execute()
    {
        $result = $this->jsonResultFactory->create();
        $outputLiteral = "output";
        if ($this->helper->isEnabled()) {
            $mappingDataCollection = $this->paymentMappingData->create()->getCollection();
            $mappingDataCollection->getSelect()->order('created_at', 'DESC');
            $mappingDataCollection->getSelect()->order('id', 'DESC');
            $mappingDataCollection->getSelect()->limit(1);
            $mappingData = [];
            if ($mappingDataCollection->getSize() > 0) {
                foreach ($mappingDataCollection as $recordMappingData) {
                    $mappingData = json_decode($recordMappingData->getData('mapped_data'), true);

                    $revisedMappingDataArray = [];
                    foreach ($mappingData as $data) {
                        $data['isEcommerceDefault'] = empty($data['isEcommerceDefault']);
                        $data['isErpDefault'] = empty($data['isErpDefault']);
                        $revisedMappingDataArray[] = $data;
                    }
                    $mappingData = $revisedMappingDataArray;
                }
            }

            $devResponse = $this->requestInterface->create();
            $devResponse->setContext(
                $this->request->prepareContextObject("PushData", null)
            );
            $devResponse->setRequestData(json_encode($mappingData, JSON_UNESCAPED_UNICODE));
            $res = $this->service->makeServiceCall("PushData", null, $devResponse, null, 'PaymentDefault');

            if (!$res->Result) {
                $this->logger->createLog(
                    __METHOD__,
                    $res->Message,
                    "PaymentMapping",
                    Logger::INFO
                );
                $result->setData([$outputLiteral => false]);
                return $result;
            } else {
                $result->setData([$outputLiteral => $devResponse]);
                return $result;
            }
        }

        $result->setData([$outputLiteral => false]);
        return $result;
    }

    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
