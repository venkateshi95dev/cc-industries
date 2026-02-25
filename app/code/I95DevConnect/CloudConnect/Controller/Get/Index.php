<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\CloudConnect\Controller\Get;

use I95DevConnect\CloudConnect\Api\Data\RequestInterfaceFactory;
use I95DevConnect\CloudConnect\Model\Logger;
use I95DevConnect\CloudConnect\Model\Request;
use I95DevConnect\CloudConnect\Model\Service;
use I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterfaceFactory;
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
class Index extends Action
{
    public const OUTPUT = "output";
    public const MESSAGE = "message";

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
     * @param Context $context
     * @param Service $service
     * @param ShippingMethodMag $shippingMethodMagento
     * @param RequestInterfaceFactory $requestInterface
     * @param Request $request
     * @param Logger $logger
     * @param JsonFactory $jsonResultFactory
     * @param Data $helper
     */
    public function __construct( // NOSONAR
        Context $context,
        Service $service,
        ShippingMethodMag $shippingMethodMagento,
        RequestInterfaceFactory $requestInterface,
        Request $request,
        Logger $logger,
        JsonFactory $jsonResultFactory,
        Data $helper
    ) {
        $this->service = $service;
        $this->shippingMethodMagento = $shippingMethodMagento;
        $this->requestInterface = $requestInterface;
        $this->request = $request;
        $this->logger = $logger;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->helper = $helper;

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
        if ($this->helper->isEnabled()) {
            $devResponse = $this->requestInterface->create();
            $devResponse->setContext(
                $this->request->prepareContextObject("PushData", null)
            );
            $devResponse->setRequestData(json_encode(
                $this->shippingMethodMagento->availableShippingMethod(),
                JSON_UNESCAPED_UNICODE
            ));
            $res = $this->service->makeServiceCall("PushData", null, $devResponse, null, 'Shipping');
            if (!$res->Result) {
                $this->logger->createLog(
                    __METHOD__,
                    $res->Message,
                    "shipping_mapping",
                    Logger::INFO
                );

                $result->setData([self::OUTPUT => false, self::MESSAGE => $res->Message]);
                return $result;
            } else {
                $data = $this->shippingMethodMagento->availableShippingMethod();
                $result->setData([self::OUTPUT => $data, self::MESSAGE => ""]);
                return $result;
            }
        }

        $result->setData([self::OUTPUT => false]);
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
