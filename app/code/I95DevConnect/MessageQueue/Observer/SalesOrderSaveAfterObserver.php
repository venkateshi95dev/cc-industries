<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Observer;

use Exception;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\ChequeNumberFactory;
use I95DevConnect\MessageQueue\Model\SalesOrderFactory;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

/**
 * Observer class to sales order before save
 */
class SalesOrderSaveAfterObserver implements ObserverInterface
{
    public const MAGLOGNAME = 'MagentoToERP';
    public const ERPLOGNAME = 'ERPToMagento';
    public const I95EXC = 'i95devApiException';
    public const PAYMENTMETHOD = 'checkmo';
    public const ORDER_OBSERVER_SKIP = 'order_observer_skip';
    public const CRITICAL = 'critical';

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var Order
     */
    public $salesOrderModel;

    /**
     * @var customSalesOrder
     */
    public $customSalesOrder;

    /**
     * @var I95DevConnect\MessageQueue\Model\ChequeNumberFactory
     */
    public $chequeNumberFactory;

    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var Http
     */
    public $request;

    /**
     *
     * @param Order $salesOrderModel
     * @param SalesOrderFactory $customSalesOrder
     * @param ChequeNumberFactory $chequeNumberFactory
     * @param Manager $eventManager
     * @param Data $dataHelper
     * @param Registry $coreRegistry
     * @param Http $request
     */
    public function __construct(
        Order $salesOrderModel,
        SalesOrderFactory $customSalesOrder,
        ChequeNumberFactory $chequeNumberFactory,
        Manager $eventManager,
        Data $dataHelper,
        Registry $coreRegistry,
        Http $request
    ) {
        $this->dataHelper = $dataHelper;
        $this->coreRegistry = $coreRegistry;
        $this->salesOrderModel = $salesOrderModel;
        $this->customSalesOrder = $customSalesOrder;
        $this->chequeNumberFactory = $chequeNumberFactory;
        $this->eventManager = $eventManager;
        $this->request = $request;
    }

    /**
     * Save i95Dev Custom attributes
     *
     * @param Observer $observer
     *
     * @throws Exception
     * @author i95Dev Team
     * @updatedBy Divya Koona. Removed of inserting gp_orderprocess_flag column value to i95dev_sales_flat_order table
     */
    public function execute(Observer $observer)
    {
        try {
            if ($this->skipObserver()) {
                return;
            }

            $orderObserverData = $observer->getEvent()->getDataObject();
            $orderId = $orderObserverData->getIncrementId();
            if (!empty($orderId) && $orderId != "") {
                $order = $this->salesOrderModel->loadByIncrementId($orderId);
                if ($order->getIsGuest() && $order->getCustomerId() !== null) {
                    $this->dataHelper->logger->createLog(__METHOD__, "Duplicate order to Outbound MQ on guest customer"
                        . " conversion to registered from Order sucess page.", self::I95EXC, self::CRITICAL);
                } else {
                    $realOrderId = $order->getIncrementId();
                    $orderData = $order->getData();
                    $orderStatus = $orderData['status'];
                    if ($orderStatus == "canceled" || $orderStatus == "closed") {
                        $this->dataHelper->logger->createLog(
                            __METHOD__,
                            "Order is Canceled in Magento",
                            self::I95EXC,
                            self::CRITICAL
                        );
                        return ;
                    }
                    $paymentMethod = "";
                    $paymentMethodData = $orderObserverData->getPayment();
                    $paymentMethod = $this->getPaymentMethod($paymentMethodData);

                    if ($paymentMethod == self::PAYMENTMETHOD) {
                        $requestParameters = $paymentMethodData['additional_information'];

                        $magChequeNumber = $this->getMagChequeNumber($requestParameters, $paymentMethodData);
                        $orderId = $this->getOrderId($paymentMethodData, $order);
                        $chequeNumber = $this->getChequeNumber($magChequeNumber, $paymentMethodData);
                        $this->saveChequeNumber($chequeNumber, $orderId);
                    }
                    $this->setCustomOrder($realOrderId, $orderData);
                    $this->eventManager->dispatch('creditlimit_event_before', ['myEventData' => $order]);
                }
            }
        } catch (LocalizedException $ex) {
            $this->dataHelper->logger->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, self::CRITICAL);
        }
    }

    /**
     * Set Custom Order
     *
     * @param int $realOrderId
     * @param object $orderData
     */
    private function setCustomOrder($realOrderId, $orderData)
    {
        $customOrder = $this->customSalesOrder->create();
        $loadCustomOrder = $this->customSalesOrder->create()->load($realOrderId, 'source_order_id');
        if ($loadCustomOrder->getId()) {
            $customOrder->setId($loadCustomOrder->getId());
        }
        $customOrder->setSourceOrderId($realOrderId);
        $customOrder->setTargetOrderStatus('New');
        $customOrder->setOrigin('website');
        $customOrder->setCreatedAt($orderData['created_at']);
        $customOrder->setUpdatedAt($orderData['updated_at']);
        $customOrder->setUpdateBy('Magento');
        $this->eventManager->dispatch(
            'i95dev_custom_sales_order_out_save_before',
            ['customOrder' => $customOrder, 'realOrder' => $orderData]
        );
        $customOrder->save();
    }

    /**
     * Skip Observer
     */
    private function skipObserver()
    {
        $is_enabled = $this->dataHelper->isEnabled();
        if (!$is_enabled) {
            return true;
        }

        if ($this->dataHelper->getGlobalValue('i95_observer_skip') ||
            $this->request->getParam('isI95DevRestReq') == 'true' ||
            ($this->dataHelper->getGlobalValue(self::ORDER_OBSERVER_SKIP) == 'invoice' ||
                $this->dataHelper->getGlobalValue(self::ORDER_OBSERVER_SKIP) == 'shipment')
        ) {
            $this->dataHelper->unsetGlobalValue(self::ORDER_OBSERVER_SKIP);
            return true;
        }

        return false;
    }

    /**
     * Get cheque number
     *
     * @param int $magChequeNumber
     * @param array $paymentMethodData
     * @return mixed
     */
    private function getChequeNumber($magChequeNumber, $paymentMethodData)
    {
        return (isset($magChequeNumber) ?
            $magChequeNumber : $paymentMethodData['target_cheque_number']);
    }

    /**
     * Get Magento cheque number
     *
     * @param array $requestParameters
     * @param object $paymentMethodData
     * @return mixed
     */
    private function getMagChequeNumber($requestParameters, $paymentMethodData)
    {
        return (isset($requestParameters['Checknumber']) ?
            $requestParameters['Checknumber'] : $paymentMethodData->getCheckNumber());
    }

    /**
     * Get Order id
     *
     * @param array $paymentMethodData
     * @param object $order
     * @return mixed
     */
    private function getOrderId($paymentMethodData, $order)
    {
        $orderId = (isset($paymentMethodData['parent_id']) ?
            $paymentMethodData['parent_id'] : $order->getId());
        if ($orderId == '') {
            $orderId = $order->getId();
        }

        return $orderId;
    }

    /**
     * Saves cheque number
     *
     * @param type $chequeNumber
     * @param type $orderId
     * @author i95Dev Team
     */
    private function saveChequeNumber($chequeNumber, $orderId)
    {
        if ($chequeNumber) {
            $checkNumber = $this->chequeNumberFactory->create();
            $checkNumber->setTargetChequeNumber($chequeNumber)
                ->setSourceOrderId($orderId)
                ->save();
        }
    }

    /**
     * Get payment method
     *
     * @param object $paymentMethodData
     * @return string
     */
    private function getPaymentMethod($paymentMethodData)
    {
        $paymentMethod = "";
        if ($paymentMethodData) {
            $paymentMethod = $paymentMethodData->getMethod();
        }

        return $paymentMethod;
    }
}
