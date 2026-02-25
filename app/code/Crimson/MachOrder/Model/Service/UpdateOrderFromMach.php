<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 2:54 PM
 * @brief
 */

namespace Crimson\MachOrder\Model\Service;

use Crimson\MachBase\Model\Api\AbstractApi;
use Crimson\MachBase\Model\Api\Logger;
use Crimson\MachOrder\Api\Data\MachOrderInterface;
use Crimson\MachOrder\Api\Data\MachOrderItemInterface;
use Crimson\MachOrder\Model\Api\OrderDetail;
use Crimson\MachOrder\Model\ResourceModel\ProcessedMachShipments;
use Crimson\MachBase\Model\Api\ApiContext;
use Crimson\MachOrder\Model\Api\AddOrder;
use Magento\Backend\Model\Auth\Session;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\Data\ShipmentTrackSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment\OrderRegistrarInterface;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Shipping\Model\CarrierFactoryInterface;

/**
 * Class UpdateOrderFromMach
 *
 * @package Crimson\MachOrder\Model\Service
 */
class UpdateOrderFromMach extends DataObject
{
    protected $_objectUsed = false;
    protected $_closedStatus
        = array(
            Order::STATE_COMPLETE,
            Order::STATE_CLOSED,
            Order::STATE_CANCELED,
        );

    /**
     * @var AddOrder
     */
    protected $addOrder;
    /**
     * @var Session
     */
    protected $adminSession;
    /**
     * @var ApiContext
     */
    protected $apiContext;
    /**
     * @var CarrierFactoryInterface
     */
    protected $carrierFactory;
    /**
     * @var Logger|null
     */
    protected $criticalLog;
    /**
     * @var Logger|null
     */
    protected $debugLog;
    /**
     * @var GetMachOrderNumber
     */
    protected $getMachOrderNumber;
    /**
     * @var Logger|null
     */
    protected $infoLog;
    /**
     * @var InvoiceService
     */
    protected $invoiceService;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var OrderDetail
     */
    protected $orderDetail;
    /**
     * @var OrderRegistrarInterface
     */
    protected $orderRegistrar;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var ProcessedMachShipments
     */
    protected $processedMachShipmentsResource;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var ShipmentFactory
     */
    protected $shipmentFactory;
    /**
     * @var ShipmentTrackRepositoryInterface
     */
    protected $shipmentTrackRepository;
    /**
     * @var TrackFactory
     */
    protected $trackFactory;
    /**
     * @var UpdateOrderFromMachFactory
     */
    protected $updateOrderFromMachFactory;

    /**
     * UpdateOrderFromMach constructor.
     *
     * @param ApiContext                                      $apiContext
     * @param AddOrder                                        $addOrder
     * @param GetMachOrderNumber                              $getMachOrderNumber
     * @param UpdateOrderFromMachFactory                      $updateOrderFromMachFactory
     * @param ProcessedMachShipments                          $processedMachShipmentsResource
     * @param OrderDetail                                     $orderDetail
     * @param Session             $adminSession
     * @param ManagerInterface     $messageManager
     * @param OrderRepositoryInterface     $orderRepository
     * @param InvoiceService     $invoiceService
     * @param ShipmentFactory                                 $shipmentFactory
     * @param TrackFactory                                    $trackFactory
     * @param OrderRegistrarInterface                         $orderRegistrar
     * @param ShipmentTrackRepositoryInterface                $shipmentTrackRepository
     * @param SearchCriteriaBuilder                           $searchCriteriaBuilder
     * @param CarrierFactoryInterface $carrierFactory
     * @param array                                           $data
     */
    public function __construct(
        ApiContext $apiContext,
        AddOrder $addOrder,
        GetMachOrderNumber $getMachOrderNumber,
        UpdateOrderFromMachFactory $updateOrderFromMachFactory,
        ProcessedMachShipments $processedMachShipmentsResource,
        OrderDetail $orderDetail,
        Session $adminSession,
        ManagerInterface $messageManager,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        ShipmentFactory $shipmentFactory,
        TrackFactory $trackFactory,
        OrderRegistrarInterface $orderRegistrar,
        ShipmentTrackRepositoryInterface $shipmentTrackRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CarrierFactoryInterface $carrierFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->apiContext                     = $apiContext;
        $this->criticalLog                    = $apiContext->getLog()->getCriticalLog();
        $this->debugLog                       = $apiContext->getLog()->getDebugLog();
        $this->infoLog                        = $apiContext->getLog()->getInfoLog();
        $this->getMachOrderNumber             = $getMachOrderNumber;
        $this->addOrder                       = $addOrder;
        $this->updateOrderFromMachFactory     = $updateOrderFromMachFactory;
        $this->adminSession                   = $adminSession;
        $this->messageManager                 = $messageManager;
        $this->processedMachShipmentsResource = $processedMachShipmentsResource;
        $this->orderDetail                    = $orderDetail;
        $this->orderRepository                = $orderRepository;
        $this->invoiceService                 = $invoiceService;
        $this->shipmentFactory                = $shipmentFactory;
        $this->trackFactory                   = $trackFactory;
        $this->orderRegistrar                 = $orderRegistrar;
        $this->shipmentTrackRepository        = $shipmentTrackRepository;
        $this->searchCriteriaBuilder          = $searchCriteriaBuilder;
        $this->carrierFactory                 = $carrierFactory;
    }

    /**
     * @param Order|OrderInterface $order
     * @param string|null          $alternativeMachOrderNumber
     *
     * @return void
     * @throws \Exception
     */
    public function execute(Order $order, ?string $alternativeMachOrderNumber = null): void
    {
        //we cannot re-use the same object twice.
        if ($this->_objectUsed) {
            //don't use new self() for DI reasons.
            $this->updateOrderFromMachFactory->create()->execute($order, $alternativeMachOrderNumber);

            return;
        } else {
            $this->_objectUsed = true;
        }

        $this->debugLog->debug(__('Beginning UpdateOrder service call for order "%1"', $order->getIncrementId()));
        $this->_reset();
        $this->_setOrder($order);

        if (!$this->_canUpdateOrder()) {
            $this->debugLog->debug(
                __('Beginning UpdateOrder exiting for order "%1".  Order is closed.', $order->getIncrementId())
            );

            return;
        } elseif (!$order->canShip() && !$order->canInvoice()) {
            $this->debugLog->debug(
                __(
                    'Beginning UpdateOrder exiting for order "%1".  Order cannot be shipped/invoiced.  Marking complete.',
                    $order->getIncrementId()
                )
            );

            return;
        }

        try {
            $machOrderNumber = $alternativeMachOrderNumber;
            if (!$machOrderNumber) {
                $machOrderNumber = $this->getMachOrderNumber->get($order);
            }

            if (!$machOrderNumber) {
                $this->debugLog->debug(
                    __('Order "%1" has not yet been processed through MACH, attempting.', $order->getIncrementId())
                );
                try {
                    $result = $this->addOrder->addOrder($order);

                    /* If $result is strictly false means that the Payment is not ready to send the Order to Mach,
                     * but we display the Order's details in the Admin, this is to avoid an endless loop
                     */
                    if ($result === false) {
                        return;
                    }
                } catch (\Exception $e) {
                    $this->criticalLog->critical(
                        __('Unable to add order, exiting UpdateOrder for order "%1".', $order->getIncrementId())
                    );
                    $this->criticalLog->critical($e);

                    return;
                }

                $this->execute($order);

                return;
                //this is causing a loop when no order is assigned repeatedly by mach
                //it should find itself again during the cron or when admin opens order in back end
            }

            if ($this->processedMachShipmentsResource->hasProcessedMachOrderNumberShipment($machOrderNumber)) {
                //if we're here, this order number has already been processed...but we don't have a completed order...it's possibel we haev a backorder.
                //let's increment it and try again.
                $this->debugLog->debug(
                    __(
                        'MACH Order "%1"/Magento Order "%2" has already been processed.  Attempting next....',
                        $machOrderNumber, $order->getIncrementId()
                    )
                );

                //we increment order number by 1.  If an item on an order is backordered when some are shipped MACH, will
                // create a new order with the mach order number + 1 as a new order with that one item as backordered.
                // this increments are mach order by number one, and tries that.
                $machOrderNumber += 1;

                $this->execute($order, $machOrderNumber);

                return;
            }

            $machOrderData = $this->orderDetail->get($this->_getOrder(), $alternativeMachOrderNumber);
            if ($machOrderData === false) {
                $this->debugLog->debug(__('Could not get a mach order id for %1', $order->getIncrementId()));

                return;
            } else {
                $this->_setMachOrderData($machOrderData);
            }

            $orderStatus = $this->_getMachOrderData()->getOrderOut1();
            $this->debugLog->debug(__('%1 order_out1 for %2', $orderStatus, $order->getIncrementId()));
            switch ($orderStatus) {
                case AbstractApi::MACH_ORDER_STATUS_OPEN:
                    //if order is open, we are not yet ready to process it as no items have shipped yet.

                    return;
                case AbstractApi::MACH_ORDER_STATUS_CANCELLED:
                    $order->cancel();
                    $order->setData('state', Order::STATE_CANCELED)
                        ->setData('status', Order::STATE_CANCELED);
                    $this->orderRepository->save($order);

                    return;
                default:
                    break;
            }

            $qtys = $this->_getChangedQtyAmounts();
            if ($qtys === false) {
                /** @noinspection PhpParamsInspection */
                $this->debugLog->debug(
                    __('No qtys to invoice/ship on order "%1".  Exiting UpdateOrder Service.', $order->getIncrementId())
                );

                //backwards compatibility, if we have a mach order number, it has been partially shipped, and nothing has changed,
                // we've made a shipment from mach, insert now.
                if ($order->hasShipments()) {
                    $this->debugLog->debug(
                        sprintf(
                            'We have shipments for order "%s", marking order number as shipped.',
                            $order->getIncrementId()
                        )
                    );
                    $this->processedMachShipmentsResource->markMachOrderNumberShipmentProcessed(
                        $order, $machOrderNumber
                    );
                }

                return;
            }

            //invoice items in Magento
            $this->_invoice($qtys);

            //create shipments with tracking numbers in Magento.
            $this->_ship($qtys);

            //mark order as updated so cron doesn't pick it up again.
            $order->getExtensionAttributes()->setMachUpdateScheduled(false);

            $this->orderRepository->save($order);

            $this->processedMachShipmentsResource->markMachOrderNumberShipmentProcessed($order, $machOrderNumber);
        } catch (LocalizedException $e) {
            if ($this->adminSession->isLoggedIn()) {
                $this->messageManager->addWarningMessage(
                    __('MACH integration :: An error occurred updating this order')
                );
            }
        }

        return;
    }

    /**
     * Reset the class before we begin working with it.
     * @return $this
     */
    protected function _reset(): UpdateOrderFromMach
    {
        $this->unsetData();

        return $this;
    }

    /**
     * @param array $qtys
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _invoice($qtys = []): UpdateOrderFromMach
    {
        $invoice = $this->invoiceService->prepareInvoice($this->_getOrder(), $qtys);

        //we do not want our order to be captured.
        $this->_getOrder()->getPayment()->setSkipTransactionCreation(true);

        /** @noinspection PhpUndefinedMethodInspection */
        $invoice->setRequestedCaptureCase(Order\Invoice::CAPTURE_OFFLINE);

        $invoice->register();

        /** @noinspection PhpUndefinedMethodInspection */
        $invoice->getOrder()->setIsInProcess(true);
        $invoice->addComment('Order invoiced automatically by MACH integration', false, false);

        $this->_getOrder()->addRelatedObject($invoice);

        return $this;
    }

    /**
     * @param array $qtys
     *
     * @return $this
     */
    protected function _ship($qtys = []): UpdateOrderFromMach
    {
        /** @var Order\Shipment $shipment */
        $shipment = $this->shipmentFactory->create(
            $this->_getOrder(),
            $qtys
        );

        $tracks = $this->_getNewTrackingData();
        if ($tracks) {
            foreach ($tracks as $track) {
                if (!$track->getTrackNumber()) {
                    continue;
                }

                $shipment->addTrack($track);
            }
        }

        $shipment->addComment('Order shipped automatically by MACH integration', false, false);

        $this->orderRegistrar->register($this->_getOrder(), $shipment);

        $this->_getOrder()->addRelatedObject($shipment);

        return $this;
    }

    /**
     * @return ShipmentTrackInterface[]
     */
    protected function _getNewTrackingData(): array
    {
        $tracks = [];

        if (!$this->_getMachOrderData()->getPackageInfo()) {
            return [];
        }

        foreach ($this->_getMachOrderData()->getPackageInfo() as $packageInfo) {
            $packageId = $packageInfo->getPackageId();

            //check if package has already been added.
            if ($this->_hasPackageBeenProcessed($packageId)) {
                continue;
            }

            $track = $this->trackFactory->create();

            //if it hasn't build our data array!
            $number      = $packageInfo->getTrackingNumber();
            $carrierCode = $this->_getCarrierByTrackingCode($number);
            $title       = $this->_getTrackingLabelByCode($carrierCode);

            $track->setCarrierCode($carrierCode)
                ->setTitle($title)
                ->setTrackNumber($number);

            $track->getExtensionAttributes()
                ->setMachPackageId($packageId)
                ->setMachCustomTrackUrl($packageInfo->getPackageLink() ?: null);

            $tracks[] = $track;
        }

        return $tracks;
    }

    /**
     * @param $packageId
     *
     * @return bool
     */
    protected function _hasPackageBeenProcessed($packageId): bool
    {
        if (!$this->_getTracksSearchResult()->getTotalCount()) {
            return false;
        }

        foreach ($this->_getTracksSearchResult()->getItems() as $shipmentTrack) {
            if ($shipmentTrack->getExtensionAttributes()->getMachPackageId() == $packageId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ShipmentTrackSearchResultInterface
     */
    protected function _getTracksSearchResult()
    {
        if (!$this->_getData('track_search_results')) {
            $this->searchCriteriaBuilder->addFilter(ShipmentTrackInterface::ORDER_ID, $this->_getOrder()->getId());

            $tracks = $this->shipmentTrackRepository->getList($this->searchCriteriaBuilder->create());
            $this->setData('track_search_results', $tracks);
        }

        return $this->_getData('track_search_results');
    }

    /**
     * @return array|bool - false if can't invoice anything new, empty array if invoice everything, populated array for
     *                    specific items.
     */
    protected function _getChangedQtyAmounts()
    {
        $qtys       = array();
        foreach ($this->_getOrder()->getAllVisibleItems() as $orderItem) {
            $orderItemData = array();

            /** @var Order\Item $orderItem */
            switch ($orderItem->getProductType()) {
                case Configurable::TYPE_CODE:
                    //configurable items, ship/invoice simple
                    foreach ($orderItem->getChildrenItems() as $childItem) {
                        /** @var Order\Item $childItem */
                        $orderItemData = $this->_findMachOrderItem($childItem->getSku());
                        break;
                    }
                    break;
                case BundleType::TYPE_CODE:
                    //bundle items, ship/invoice parent
                case Type::TYPE_SIMPLE:
                default:
                    $orderItemData = $this->_findMachOrderItem($orderItem->getSku());
                    break;
            }

            if (!$orderItemData) {
                continue; //order item foreach
            }

            //if we have newly shipped items, record it.
            $checkQty = ($orderItemData->getQtyShipped() - $orderItem->getQtyShipped());
            if ($checkQty > 0) {
                $qtys[$orderItem->getId()] = ($orderItemData->getQtyShipped() - $orderItem->getQtyShipped());
            }

            if ($orderItemData->getQtyBackordered() != $orderItem->getQtyBackordered()) {
                $orderItem->setQtyBackordered($orderItemData->getQtyBackordered());
            }
        }

        /*
         * if we can't process anything and qtys[] is empty, return fail (false) status
         * otherwise, return what can be invoiced/shipped.
         */
        if (empty($qtys)) {
            return false;
        } else {
            return $qtys;
        }
    }

    /**
     * @param string $sku
     *
     * @return bool|MachOrderItemInterface
     */
    protected function _findMachOrderItem($sku)
    {
        foreach ($this->_getMachOrderData()->getOrderItems() as $orderItemData) {
            if ($orderItemData['sku'] == $this->apiContext->getMachSku()->get($sku)) {
                return $orderItemData;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function _canUpdateOrder()
    {
        return !in_array($this->_getOrder()->getState(), $this->_closedStatus);
    }

    /**
     * @return Order
     */
    protected function _getOrder(): Order
    {
        return $this->_getData('order');
    }

    protected function _setOrder(OrderInterface $order): UpdateOrderFromMach
    {
        return $this->setData('order', $order);
    }

    /**
     * @return MachOrderInterface
     */
    protected function _getMachOrderData(): MachOrderInterface
    {
        return $this->getData('mach_order_data');
    }

    /**
     * @param MachOrderInterface $machOrderData
     * @return UpdateOrderFromMach
     */
    protected function _setMachOrderData(MachOrderInterface $machOrderData): UpdateOrderFromMach
    {
        return $this->setData('mach_order_data', $machOrderData);
    }

    /**
     * @param $trackingCode
     * @return string
     */
    protected function _getCarrierByTrackingCode($trackingCode): string
    {
        $tracking_code = $trackingCode ?: "";
        if (preg_match('/^1Z[A-Z0-9]{3}[A-Z0-9]{3}[0-9]{2}[0-9]{4}[0-9]{4}$/i', $tracking_code)) {
            return 'ups';
        } else {
            if (preg_match('/^([0-9]{20})?([0-9]{4}[0-9]{4}[0-9]{4}[0-9]{2})$/', $tracking_code, $matches)) {
                return 'fedex';
            } else {
                if (preg_match('/^[0-9]{2}[0-9]{4}[0-9]{4}$/', $tracking_code, $matches)) {
                    return 'dhl';
                } else {
                    if (preg_match('/^[0-9]{4}[0-9]{4}[0-9]{4}[0-9]{4}[0-9]{4}[0-9]{2}$/', $tracking_code)) {
                        return 'usps';
                    } elseif (preg_match(
                        '/^420[0-9]{5}([0-9]{4}[0-9]{4}[0-9]{4}[0-9]{4}[0-9]{4}[0-9]{2})$/', $tracking_code, $matches
                    )) {
                        return 'usps';
                    }
                }
            }
        }

        //if we can't determine, return ups (default shipper).
        return 'ups';
    }

    /**
     * @param $carrierCode
     * @return mixed
     */
    protected function _getTrackingLabelByCode($carrierCode)
    {
        $carrier = $this->carrierFactory->create($carrierCode);
        if (!$carrier) {
            $carrier = $this->carrierFactory->create('ups');
        }

        return $carrier->getConfigData('title');
    }
}
