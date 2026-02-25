<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order;

use Exception;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Create;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Info;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Response;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for creating Order, getting Order info and setting Order response
 */
class Order
{
    /**
     * @var Order\Info
     */
    public $orderInfo;

    /**
     * @var Order\Response
     */
    public $orderResponse;

    /**
     * @var Order\Create
     */
    public $orderCreate;

    /**
     *
     * @param Response $orderResponse
     * @param Info $orderInfo
     * @param Create $orderCreate
     */
    public function __construct(
        Response $orderResponse,
        Info $orderInfo,
        Create $orderCreate
    ) {
        $this->orderResponse = $orderResponse;
        $this->orderInfo = $orderInfo;
        $this->orderCreate = $orderCreate;
    }

    /**
     * Create Order.
     *
     * @param string $stringData
     * @param string $entityCode
     * @param string $erp
     *
     * @return I95DevResponseInterface
     * @throws Exception
     */
    public function create($stringData, $entityCode, $erp = null)
    {
        return $this->orderCreate->createOrder($stringData, $entityCode, $erp);
    }

    /**
     * Get Order information
     *
     * @param int $orderId
     * @param string $entityCode
     * @param string $erpCode
     * @return array
     * @throws LocalizedException
     * @throws Exception
     */
    public function getInfo($orderId, $entityCode, $erpCode = null) //NOSONAR
    {
        return  $this->orderInfo->getInfo($orderId);
    }

    /**
     * Sets target Order information
     *
     * @param array $requestData
     * @param string $entityCode
     * @param string $erpCode
     * @return I95DevResponseInterface
     * @throws Exception
     */
    public function getResponse($requestData, $entityCode, $erpCode = null)
    {
        return $this->orderResponse->getResponse($requestData, $entityCode, $erpCode);
    }
}
