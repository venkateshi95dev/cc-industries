<?php

namespace I95DevConnect\CloudConnect\Cron;

use I95DevConnect\CloudConnect\Api\Data\RequestInterfaceFactory;
use I95DevConnect\CloudConnect\Model\Logger;
use I95DevConnect\CloudConnect\Model\Request;
use I95DevConnect\CloudConnect\Model\Service;
use I95DevConnect\ShippingMapping\Helper\Data;
use I95DevConnect\ShippingMapping\Model\ShippingMethodMag;
use Magento\Shipping\Model\Config;

/**
 * Class for Sending Shipping mapping data
 */
class SendShippingMapping
{
    public const SCHEDULER_TYPE = 'PushData';

    /**
     * @var string
     */
    private $logFilename = 'shippingMapping';

    /**
     * @var Service
     */
    public $service;

    /**
     * @var Config
     */
    public $shipconfig;

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
     * @var ShippingMethodMag
     */
    public $shippingMethodMagento;

    /**
     * @var Data
     */
    public $helper;

    /**
     * SendShippingMapping constructor.
     *
     * @param Service $service
     * @param Config $shipconfig
     * @param RequestInterfaceFactory $requestInterface
     * @param Request $request
     * @param Logger $logger
     * @param ShippingMethodMag $shippingMethodMagento
     * @param Data $helper
     */
    public function __construct(
        Service $service,
        Config $shipconfig,
        RequestInterfaceFactory $requestInterface,
        Request $request,
        Logger $logger,
        ShippingMethodMag $shippingMethodMagento,
        Data $helper
    ) {
        $this->service = $service;
        $this->shipconfig = $shipconfig;
        $this->requestInterface = $requestInterface;
        $this->request = $request;
        $this->logger = $logger;
        $this->shippingMethodMagento = $shippingMethodMagento;
        $this->helper = $helper;
    }

    /**
     * Class execute method.
     *
     * @return void
     */
    public function execute()
    {
        if ($this->helper->isEnabled()) {
            $devResponse = $this->requestInterface->create();
            $devResponse->setContext(
                $this->request->prepareContextObject(self::SCHEDULER_TYPE, null)
            );
            $devResponse->setRequestData(json_encode(
                $this->shippingMethodMagento->availableShippingMethod(),
                JSON_UNESCAPED_UNICODE
            ));

            $res = $this->service->makeServiceCall(self::SCHEDULER_TYPE, null, $devResponse, null, 'Shipping');

            if (!$res->Result) {
                $this->logger->createLog(
                    __METHOD__,
                    $res->Message,
                    $this->logFilename,
                    Logger::INFO
                );
            }
        }
    }
}
