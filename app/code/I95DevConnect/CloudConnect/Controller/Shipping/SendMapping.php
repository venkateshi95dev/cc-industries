<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\CloudConnect\Controller\Shipping;

use I95DevConnect\CloudConnect\Api\Data\RequestInterfaceFactory;
use I95DevConnect\CloudConnect\Model\Logger;
use I95DevConnect\CloudConnect\Model\Request;
use I95DevConnect\CloudConnect\Model\Service;
use I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterfaceFactory;
use I95DevConnect\ShippingMapping\Api\Data\ShippingMappingDataInterfaceFactory;
use I95DevConnect\ShippingMapping\Helper\Data;
use I95DevConnect\ShippingMapping\Model\ShippingMethodMag;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Controller for rendering data string in Message Queue
 */
class SendMapping extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     *
     * @var I95DevErpDataRepositoryInterfaceFactory
     */
    public $i95DevErpData;

    /**
     * @var Service
     */
    public $service;

    /**
     * @var ShippingMethodMag
     */
    public $shippingMethodMagento;

    /**
     * @var Request
     */
    public $request;

    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var Data
     */
    public $helper;

    /**
     * @var ShippingMappingDataInterfaceFactory
     */
    public $shippingMappingData;

    /**
     * @var JsonFactory
     */
    public $jsonResultFactory;

    /**
     * @var RequestInterfaceFactory
     */
    public $requestInterface;

    /**
     * @param Context $context
     * @param Service $service
     * @param ShippingMethodMag $shippingMethodMagento
     * @param RequestInterfaceFactory $requestInterface
     * @param Request $request
     * @param Logger $logger
     * @param JsonFactory $jsonResultFactory
     * @param Data $helper
     * @param ShippingMappingDataInterfaceFactory $shippingMappingData
     */
    public function __construct( // NOSONAR
        Context $context,
        Service $service,
        ShippingMethodMag $shippingMethodMagento,
        RequestInterfaceFactory $requestInterface,
        Request $request,
        Logger $logger,
        JsonFactory $jsonResultFactory,
        Data $helper,
        ShippingMappingDataInterfaceFactory $shippingMappingData
    ) {
        $this->service = $service;
        $this->shippingMethodMagento = $shippingMethodMagento;
        $this->requestInterface = $requestInterface;
        $this->request = $request;
        $this->logger = $logger;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->helper = $helper;
        $this->shippingMappingData = $shippingMappingData;

        parent::__construct($context);
    }

    /**
     * Render Message Queue data string
     *
     * @return Json
     * @throws LocalizedException
     */
    public function execute()
    {
        $result = $this->jsonResultFactory->create();
        $outputLiteral = "output";
        if ($this->helper->isEnabled()) {
            $mappingData = $this->shippingMappingData->create()->getCollection()->getData();

            $devResponse = $this->requestInterface->create();
            $devResponse->setContext(
                $this->request->prepareContextObject("PushData", null)
            );

            $formattedMappingData = [];
            foreach ($mappingData as $value) {
                $convertData = [];
                $convertData['ecommerceMethod'] = $value['magento_code'];
                $convertData['erpMethod'] = $value['erp_code'];
                $convertData['isEcommerceDefault'] = empty($value['is_ecommerce_default']);
                $convertData['isErpDefault'] = empty($value['is_erp_default']);

                $formattedMappingData[] = $convertData;
            }

            $devResponse->setRequestData(json_encode($formattedMappingData, JSON_UNESCAPED_UNICODE));
            $res = $this->service->makeServiceCall("PushData", null, $devResponse, null, 'ShippingDefault');

            if (!$res->Result) {
                $this->logger->createLog(
                    __METHOD__,
                    $res->Message,
                    "shipping_mapping",
                    Logger::INFO
                );

                $result->setData([$outputLiteral => false]);
                return $result;
            } else {
                $result->setData([$outputLiteral => json_encode($formattedMappingData, JSON_UNESCAPED_UNICODE)]);
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
