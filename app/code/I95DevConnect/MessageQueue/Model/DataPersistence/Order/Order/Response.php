<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order;

use Exception;
use I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\ErpOrderStatus;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\SalesOrder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class responsible for saving erp responses in order
 */
class Response
{
    public const SKIPOBRVR = "i95_observer_skip";
    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    public $I95DevMagMQRepository;

    /**
     * @var I95DevMagMQInterfaceFactory
     */
    public $I95DevMagMQData;

    /**
     * @var string
     */
    public $statusCode = '5';

    /**
     * @var string
     */
    public $updatedBy = 'ERP';

    /**
     * @var int
     */
    public $orderId;

    /**
     * @var string
     */
    public $erpCode;

    /**
     * @var int
     */
    public $targetId;

    /**
     * @var string
     */
    public $entityCode;

    /**
     * @var string
     */
    public $stringData;

    /**
     * scopeConfig for system Congiguration
     *
     * @var string
     */
    public $scopeConfig;

    /**
     * @var customSalesOrder
     */
    public $customSalesOrder;

    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     * @var int
     */
    public $messageId;
    /**
     * @var ErpOrderStatus
     */
    private $orderStatusHelper;

    /**
     *
     * @param Data $dataHelper
     * @param I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepository
     * @param I95DevMagMQInterfaceFactory $I95DevMagMQData
     * @param ErpOrderStatus $orderStatusHelper
     * @param Manager $eventManager
     * @param SalesOrder $customSalesOrder
     * @param ScopeConfigInterface $scopeConfig
     * @param AbstractDataPersistence $abstractDataPersistence
     */
    public function __construct( // NOSONAR
        Data $dataHelper,
        I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepository,
        I95DevMagMQInterfaceFactory $I95DevMagMQData,
        ErpOrderStatus $orderStatusHelper,
        Manager $eventManager,
        SalesOrder $customSalesOrder,
        ScopeConfigInterface $scopeConfig,
        AbstractDataPersistence $abstractDataPersistence
    ) {
        $this->dataHelper = $dataHelper;
        $this->I95DevMagMQRepository = $I95DevMagMQRepository;
        $this->I95DevMagMQData = $I95DevMagMQData;
        $this->orderStatusHelper = $orderStatusHelper;
        $this->eventManager = $eventManager;
        $this->customSalesOrder = $customSalesOrder;
        $this->scopeConfig = $scopeConfig;
        $this->abstractDataPersistence = $abstractDataPersistence;
    }

    /**
     * Sets target order details in order
     *
     * @param array $requestData
     * @param string $entityCode
     * @param string $erpCode
     *
     * @return I95DevResponseInterface
     * @throws Exception
     * @author Divya Koona. Removed of inserting gp_orderprocess_flag column value to i95dev_sales_flat_order table
     */
    public function getResponse($requestData, $entityCode, $erpCode)
    {
        try {
            $this->stringData = $requestData;
            $this->orderId = $this->dataHelper->getValueFromArray("sourceId", $requestData);
            $this->targetId = $this->dataHelper->getValueFromArray("targetId", $requestData);
            $this->messageId = $this->dataHelper->getValueFromArray("messageId", $requestData);
            $this->entityCode = $entityCode;

            //Updated By Sravani Polu, Changed API call to Interface call to get order interface
            $order = $this->orderStatusHelper->getOrderByIncrementId($this->orderId);
            if (is_object($order) && $order->getEntityId()) {
                if (!isset($erpCode)) {
                    $this->erpCode = "ERP";
                } else {
                    $this->erpCode = $erpCode;
                }

                $this->saveDataInOutboundMQ();
                $this->dataHelper->unsetGlobalValue(self::SKIPOBRVR);
                $this->dataHelper->setGlobalValue(self::SKIPOBRVR, true);

                if ($this->targetId != "") {
                    $customOrder = $this->getCustomOrder($order->getIncrementId());
                    $customOrder->setTargetOrderId($this->targetId);
                    $customOrder->setUpdateBy($this->erpCode);
                    $origin = $customOrder->getData('origin');
                    if (empty($origin)) {
                        $customOrder->setData('origin', 'website');
                    }
                    $customOrder->save();
                }

                $this->dataHelper->unsetGlobalValue(self::SKIPOBRVR);
                $orderResponseEvent = "erpconnect_forward_orderresponse";
                $this->eventManager->dispatch($orderResponseEvent, ['currentObject' => $this]);
                return $this->abstractDataPersistence->setResponse(
                    Data::SUCCESS,
                    __("Response send successfully")
                );
            } else {
                return $this->abstractDataPersistence->setResponse(
                    Data::ERROR,
                    __("Some error occured in response sync"),
                    null,
                    105
                );
            }
        } catch (LocalizedException $ex) {
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Update outbound table once the order synced to ERP
     */
    public function saveDataInOutboundMQ()
    {
        $I95DevMagMQ = $this->I95DevMagMQData->create();
        $I95DevMagMQ->setMsgId($this->messageId);
        $I95DevMagMQ->setStatus($this->statusCode);
        $I95DevMagMQ->setUpdatedby($this->updatedBy);
        $I95DevMagMQ->setTargetId($this->targetId);

        $this->I95DevMagMQRepository->create()->saveMQData($I95DevMagMQ);
    }

    /**
     * Retrieve i95dev custom order by source order id
     *
     * @param int $sourceOrderId
     * @return SalesOrder
     */
    public function getCustomOrder($sourceOrderId)
    {
        $customOrderModel = $this->customSalesOrder;
        $customOrderData = $customOrderModel->getCollection()
            ->addFieldToSelect('id')
            ->addFieldToFilter('source_order_id', $sourceOrderId);
        $customOrderData->getSelect()->limit(1);
        $customOrderData = $customOrderData->getData();

        $customOrderId = (isset($customOrderData[0]['id']) ? $customOrderData[0]['id'] : '');

        return $customOrderModel->load($customOrderId);
    }
}
