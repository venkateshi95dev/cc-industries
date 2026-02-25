<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Shipment\Shipment;

use Exception;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\ErpOrderStatus;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\DataPersistence\ValidateFactory;
use I95DevConnect\MessageQueue\Model\SalesOrderFactory;
use I95DevConnect\MessageQueue\Model\SalesShipmentFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Api\Data\ShipmentCommentInterface;
use Magento\Sales\Api\Data\ShipmentCommentInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory;
use Magento\Sales\Api\ShipmentCommentRepositoryInterfaceFactory;
use Magento\Sales\Api\ShipmentManagementInterfaceFactory;
use Magento\Sales\Api\ShipmentRepositoryInterfaceFactory;
use Magento\Sales\Api\ShipmentTrackRepositoryInterfaceFactory;
use Magento\Sales\Api\ShipOrderInterfaceFactory;

/**
 * Class for shipment creation.
 */
class Create
{
    public const SOURCE_SHIPMENT_ID = 'source_shipment_id';
    public const TRACKING = 'tracking';
    public const TRACKNUMBER = 'trackNumber';

    /**
     * @var object
     */
    public $orderObject;

    /**
     * @var SalesOrderFactory
     */
    public $customSalesOrder;

    /**
     * @var SalesShipmentFactory
     */
    public $customShipment;

    /**
     * @var array
     */
    public $shipmentItems;

    /**
     * @var int
     */
    public $targetShipmentId;

    /**
     * @var int
     */
    public $sourceShipmentId;

    /**
     * @var int
     */
    public $customSalesOrderId;

    /**
     * @var ErpOrderStatus string
     */
    public $erpOrderStatus;

    /**
     * @var Data
     */
    public $baseHelperData;

    /**
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var ShipmentInterfaceFactory
     */
    public $shipmentFactory;

    /**
     * @var ShipmentTrackRepositoryInterfaceFactory
     */
    public $trackRepository;

    /**
     * @var ShipmentRepositoryInterfaceFactory
     */
    public $shipmentRepo;

    /**
     * @var ShipmentTrackInterfaceFactory
     */
    public $shipmentTrack;

    /**
     * @var ShipmentCommentInterfaceFactory
     */
    public $shipmentCmtFactory;

    /**
     * @var ShipmentCommentRepositoryInterfaceFactory
     */
    public $shipmentCmtRepo;

    /**
     * @var ShipmentManagementInterfaceFactory
     */
    public $shipmentMgmt;

    /**
     * @var ShipOrderInterfaceFactory
     */
    public $shipOrder;

    /**
     * @var ShipmentItemCreationInterfaceFactory
     */
    public $shipmentItemObj;

    /**
     * @var ShipmentTrackCreationInterfaceFactory
     */
    public $shipTrackCreate;

    /**
     * @var ValidateFactory
     */
    public $validate;

    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     * @var null
     */
    public $shipmentObject = null;

    /**
     * @var string[]
     */
    public $validateFields = [
        'targetId' => 'i95dev_shipment_order_shipmentid',
        'targetOrderId' => 'i95dev_empty_targetOrderId',
    ];

    /**
     * @var array
     */
    public $stringData = [];

    /**
     * @var string
     */
    public $entityCode = '';

    /**
     *
     * @param Manager $eventManager
     * @param Data $baseHelperData
     * @param SalesOrderFactory $customSalesOrder
     * @param SalesShipmentFactory $customShipment
     * @param ErpOrderStatus $erpOrderStatus
     * @param LoggerInterfaceFactory $logger
     * @param ShipmentInterfaceFactory $shipmentFactory
     * @param ShipmentTrackRepositoryInterfaceFactory $trackRepository
     * @param ShipmentRepositoryInterfaceFactory $shipmentRepo
     * @param ShipmentTrackInterfaceFactory $shipmentTrack
     * @param ShipmentManagementInterfaceFactory $shipmentMgmt
     * @param ShipmentCommentRepositoryInterfaceFactory $shipmentCmtRepo
     * @param ShipmentCommentInterfaceFactory $shipmentCmtFactory
     * @param ShipOrderInterfaceFactory $shipOrder
     * @param ShipmentItemCreationInterfaceFactory $shipmentItemObj
     * @param ShipmentTrackCreationInterfaceFactory $shipTrackCreate
     * @param ValidateFactory $validate
     * @param AbstractDataPersistence $abstractDataPersistence
     */
    public function __construct(//NOSONAR
        Manager $eventManager,
        Data $baseHelperData,
        SalesOrderFactory $customSalesOrder,
        SalesShipmentFactory $customShipment,
        ErpOrderStatus $erpOrderStatus,
        LoggerInterfaceFactory $logger,
        ShipmentInterfaceFactory $shipmentFactory,
        ShipmentTrackRepositoryInterfaceFactory $trackRepository,
        ShipmentRepositoryInterfaceFactory $shipmentRepo,
        ShipmentTrackInterfaceFactory $shipmentTrack,
        ShipmentManagementInterfaceFactory $shipmentMgmt,
        ShipmentCommentRepositoryInterfaceFactory $shipmentCmtRepo,
        ShipmentCommentInterfaceFactory $shipmentCmtFactory,
        ShipOrderInterfaceFactory $shipOrder,
        ShipmentItemCreationInterfaceFactory $shipmentItemObj,
        ShipmentTrackCreationInterfaceFactory $shipTrackCreate,
        ValidateFactory $validate,
        AbstractDataPersistence $abstractDataPersistence
    ) {
        $this->customShipment = $customShipment;
        $this->customSalesOrder = $customSalesOrder;
        $this->erpOrderStatus = $erpOrderStatus;
        $this->baseHelperData = $baseHelperData;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->shipmentFactory = $shipmentFactory;
        $this->trackRepository = $trackRepository;
        $this->shipmentRepo = $shipmentRepo;
        $this->shipmentTrack = $shipmentTrack;
        $this->shipmentCmtFactory = $shipmentCmtFactory;
        $this->shipmentCmtRepo = $shipmentCmtRepo;
        $this->shipmentMgmt = $shipmentMgmt;
        $this->shipOrder = $shipOrder;
        $this->shipmentItemObj = $shipmentItemObj;
        $this->shipTrackCreate = $shipTrackCreate;
        $this->validate = $validate;
        $this->abstractDataPersistence = $abstractDataPersistence;
    }

    /**
     * Create Shipment.
     *
     * @param array $stringData
     * @param string $entityCode
     * @return I95DevResponseInterface
     * @updatedBy Arushi Bansal
     */
    public function createShipment($stringData, $entityCode)
    {
        $this->stringData = $stringData;
        $this->entityCode = $entityCode;
        try {
            $this->validateData();
            $parentItems = $this->baseHelperData->getParentItems($this->orderObject->getItems());
            $itemsIds = $parentItems['itemsIds'];
            $parentItemIds = $parentItems['parentItemIds'];
            $productMapArray = $parentItems['productMapArray'];
            $bundleChilds = $parentItems['bundleChilds'];

            $shipmentItemsData = [];
            $shipmentItemEntity = $this->baseHelperData->getValueFromArray("shipmentItemEntity", $this->stringData);

            if (!empty($shipmentItemEntity)) {
                foreach ($shipmentItemEntity as $shipmentData) {
                    /** @updatedBy Debashis S. Gopal. Validating shipment item sku irrespective of their case * */
                    $erpSku = strtolower($shipmentData['orderItemId']);
                    $productId = $this->validateShipmentItem($productMapArray, $erpSku, $shipmentData);

                    $itemId = $itemsIds[$productId];
                    $itemId = $this->setItemId($parentItemIds, $itemId);
                    $shipmentItemsData = $this->baseHelperData->getItemsData(
                        $itemId,
                        $shipmentData,
                        $shipmentItemsData
                    );
                }
            }
            $this->setShipmentItemsQtyAsZero($shipmentItemsData, $itemsIds);
            $shipmentItemsData = $this->addBundleChilds($bundleChilds, $shipmentItemsData);
            $this->shipmentItems = $shipmentItemsData;
            $result = $this->doShipment();

            $aftereventname = 'erpconnect_messagequeuetomagento_aftersave_' . $this->entityCode;
            $this->eventManager->dispatch($aftereventname, ['currentObject' => $this]);

            return $this->abstractDataPersistence->setResponse(
                Data::SUCCESS,
                "Record Synced Successfully",
                $result
            );
        } catch (Exception $ex) {
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Set Item Id
     *
     * @param arary $parentItemIds
     * @param string $itemId
     * @return mixed
     */
    public function setItemId($parentItemIds, $itemId)
    {
        if (isset($parentItemIds[$itemId])) {
            $parentItemId = $parentItemIds[$itemId];
            if ($parentItemId > 0) {
                $itemId = $parentItemId;
            }
        }

        return $itemId;
    }

    /**
     * Set Shipment Item Qty as zero
     *
     * @param array $shipmentItemsData
     * @param array $itemsIds
     */
    public function setShipmentItemsQtyAsZero(&$shipmentItemsData, $itemsIds)
    {
        foreach ($itemsIds as $itemId) {
            if (!key_exists($itemId, $shipmentItemsData)) {
                $shipmentItemsData[$itemId]['qty'] = 0;
            }
        }
    }

    /**
     * Validate Shipment Item
     *
     * @param array $productMapArray
     * @param string $erpSku
     * @param string $shipmentData
     * @return mixed
     * @return mixed
     */
    public function validateShipmentItem($productMapArray, $erpSku, $shipmentData)
    {
        if (isset($productMapArray[$erpSku])) {
            return $productMapArray[$erpSku];
        } else {
            /** @noinspection PhpIllegalStringOffsetInspection */
            throw new LocalizedException(
                __('i95dev_shipment_invalid_item %1', $shipmentData['orderItemId']),
                null,
                104
            );
        }
    }

    /**
     * Do shipment of an order
     *
     * @updatedBy Arushi Bansal
     * @return string
     * @throws LocalizedException
     * @throws Exception
     */
    public function doShipment()
    {
        $customShipmentCollection = $this->checkShipmentAlreadySync();

        if ($customShipmentCollection->getSize() > 0) {
            // if same shipment reqest come again then update tracking number
            $customShipmentData = $customShipmentCollection->getData();
            $customShipmentData = $customShipmentData[0];
            $source_shipment_id = $customShipmentData[self::SOURCE_SHIPMENT_ID];

            $this->updateTrackingNumber($customShipmentData);

            return $source_shipment_id;
        } else {
            // for new shipment request
            $parentSimpleItems = $this->erpOrderStatus->getParentSimpleItems($this->orderObject);
            $shipQty = $this->prepareShipQty($parentSimpleItems);
            $shippedOrderedQty = $shipQty["shippedOrderedQty"];
            $shippedQty = $shipQty["shippedQty"];

            if ($shippedOrderedQty != $shippedQty) {
                $shipTrack = $this->setShipmentTrackDetails();

                $shipmentItemsDetails = $this->setShipmentItemsDetails();

                $beforeeventname = 'erpconnect_messagequeuetomagento_beforesave_' . $this->entityCode;
                $this->eventManager->dispatch($beforeeventname, ['currentObject' => $this]);

                $this->baseHelperData->unsetGlobalValue('i95_observer_skip');
                $this->baseHelperData->setGlobalValue('i95_observer_skip', true);

                $shipmentResponse = $this->shipOrder->create()->execute(
                    $this->orderObject->getEntityId(),
                    $shipmentItemsDetails,
                    false,
                    false,
                    null,
                    $shipTrack
                );

                if (!is_numeric($shipmentResponse)) {
                    throw new LocalizedException(
                        __("shipment_not_synced"),
                        null,
                        105
                    );
                }

                $this->shipmentObject = $this->getShipment($shipmentResponse);
                return $this->processAfterShipmentResponse($this->shipmentObject);
            } else {
                throw new LocalizedException(
                    __('i95dev_shipment_fullyshipped'),
                    null,
                    102
                );
            }
        }
    }

    /**
     * Process after shipment response
     *
     * @param object $shipmentObject
     * @return mixed
     * @throws Exception
     */
    public function processAfterShipmentResponse($shipmentObject)
    {
        if ($shipmentObject) {
            $this->saveCustomI95DevShipment($shipmentObject->getIncrementId(), $this->targetShipmentId);
            $this->erpOrderStatus->updateCustomOrderStatus(
                $this->customSalesOrderId,
                $this->orderObject->getEntityId()
            );

            if ($this->baseHelperData->isEmailNotifyEnable('shipment')
                && $this->sendEmail($shipmentObject->getEntityId())
            ) {
                $this->addComment($shipmentObject);
            }
            return $shipmentObject->getIncrementId();
        } else {
            throw new LocalizedException(
                __('shipment_not_synced'),
                null,
                105
            );
        }
    }

    /**
     * Prepare ship quantity
     *
     * @param array $parentSimpleItems
     * @return array
     */
    public function prepareShipQty($parentSimpleItems)
    {
        $shippedOrderedQty = 0;
        $shippedQty = 0;
        foreach ($this->orderObject->getItems() as $item) {
            $nonShippedTypes = ['virtual', 'downloadable'];
            if (!in_array($item->getProductType(), $nonShippedTypes)
                && !in_array($item->getItemId(), $parentSimpleItems)
            ) {
                $shippedOrderedQty += $item->getQtyOrdered();
                $shippedQty += $item->getQtyShipped();
            }
        }
        return ['shippedOrderedQty' => $shippedOrderedQty, 'shippedQty' => $shippedQty];
    }

    /**
     * Set shipment item details
     *
     * @return array
     */
    public function setShipmentItemsDetails()
    {
        $shipmentItemsData = [];
        foreach ($this->shipmentItems as $shipmentItemKey => $shipmentItemval) {
            $shipItem = $this->shipmentItemObj->create();
            $shipItem->setOrderItemId($shipmentItemKey);
            $shipItem->setQty($shipmentItemval['qty']);
            $shipmentItemsData[] = $shipItem;
        }

        return $shipmentItemsData;
    }

    /**
     * Set shipment track details
     *
     * @return array
     */
    public function setShipmentTrackDetails()
    {
        $shipTrackCreate = [];
        $trackingNo = '';
        if (isset($this->stringData[self::TRACKING][0][self::TRACKNUMBER])) {
            $trackingNo = $this->stringData[self::TRACKING][0][self::TRACKNUMBER];
        }
        if (isset($this->stringData[self::TRACKING]) && $this->stringData[self::TRACKING] != ''
            && $trackingNo != ''
        ) {
            $trackingDetails = $this->stringData[self::TRACKING];
        } else {
            return $shipTrackCreate;
        }
        $carriersList = ['dhl','fedex','ups','usps'];
        foreach ($trackingDetails as $trackingData) {
            $trackNumber = isset($trackingData[self::TRACKNUMBER]) ? $trackingData[self::TRACKNUMBER] : '';
            $data = $this->shipTrackCreate->create();
            if (isset($trackingData['carrier']) && !empty($trackingData['carrier']) && 
            in_array(strtolower($trackingData['carrier']),$carriersList)) {
                $carrier = $trackingData['carrier'];
            } else {
                $carrier = 'custom';
            }

            $title = (isset($trackingData['title']) && !empty($trackingData['title'])) ? $trackingData['title'] : '';
            $data->setCarrierCode(strtolower($carrier));
            $data->setTitle($title);
            $data->setTrackNumber($trackNumber);
            $shipTrackCreate[] = $data;
        }

        return $shipTrackCreate;
    }

    /**
     * Function responsible to save data in custom invoice table
     *
     * @param int $shipmentId
     * @param string $targetShipmentId
     * @param object $customShipmentObj
     *
     * @throws Exception
     * @createdBy Arushi Bansal
     */
    public function saveCustomI95DevShipment($shipmentId, $targetShipmentId, $customShipmentObj = null)
    {
        try {
            if (!isset($customShipmentObj)) {
                $customShipmentData = $this->customShipment->create();
            } else {
                $customShipmentData = $customShipmentObj;
            }
            $customShipmentData->setSourceShipmentId($shipmentId);
            $customShipmentData->setTargetShipmentId($targetShipmentId);
            $customShipmentData->setUpdatedDt($this->baseHelperData->date->gmtDate());
            $customShipmentData->setUpdateBy('ERP');
            $customShipmentData->save();
        } catch (LocalizedException $e) {
            $this->logger->create()->createLog(
                __METHOD__,
                $e->getMessage(),
                LoggerInterface::INFO,
                'info'
            );
        }
    }

    /**
     * Validate shipment Date from ERP
     *
     * @return boolean
     * @throws LocalizedException
     */
    public function validateData()
    {
        $this->validate = $this->validate->create();
        $this->validate->validateFields = $this->validateFields;
        if ($this->validate->validateData($this->stringData)) {
            $this->targetShipmentId = $this->baseHelperData->getValueFromArray("targetId", $this->stringData);

            $targetOrderId = $this->baseHelperData->getValueFromArray("targetOrderId", $this->stringData);
            /*@FIX - CLOUD-551*/
            $customOrderDataCollection = $this->getCustomOrderByTargetId($targetOrderId);
            $this->customSalesOrderId = $customOrderDataCollection->getData()[0]['source_order_id'];
            $this->orderObject = $this->getOrder($this->customSalesOrderId);
            if (!$this->orderObject) {
                throw new LocalizedException(
                    __("order_not_exist"),
                    null,
                    108
                );
            }

            $shipmentItemEntity = $this->baseHelperData->getValueFromArray("shipmentItemEntity", $this->stringData);
            if (!is_array($shipmentItemEntity) || count($shipmentItemEntity) == 0) {
                throw new LocalizedException(
                    __('i95dev_shipment_itemNotExist'),
                    null,
                    108
                );
            }
        } else {
            throw new LocalizedException(
                __("validation_error"),
                null,
                104
            );
        }
        return true;
    }
    /**
     * Update tracking number
     *
     * @param array $customShipmentData
     *
     * @throws LocalizedException
     */
    public function updateTrackingNumber($customShipmentData)
    {
        try {
            /* Code for deleting existing tracking number */
            $shipmentData = $this->shipmentFactory->create()->loadByIncrementId(
                $customShipmentData["source_shipment_id"]
            );
            $shipmentDetails = $shipmentData->getTracks();
            if (!empty($shipmentDetails)) {
                foreach ($shipmentDetails as $shipmentDetail) {
                    $shipmentTrackData = $this->shipmentTrack->create();
                    $shipmentTrackData->load($shipmentDetail['entity_id'])->delete();
                }
            }

            $trackingData = $this->baseHelperData->getValueFromArray(self::TRACKING, $this->stringData);
            if (!empty($trackingData)) {
                foreach ($trackingData as $trackrow) {
                    $this->addTrackingDetails($trackrow, $shipmentData);
                }
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                $ex->getMessage(),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Add tracking details
     *
     * @param array $trackrow
     * @param object $shipmentData
     */
    public function addTrackingDetails($trackrow, $shipmentData)
    {
        if (isset($trackrow[self::TRACKNUMBER])) {
            /* Add tracking number to shipment */
            $carrier = (isset($trackrow['carrier']) && !empty($trackrow['carrier'])) ? $trackrow['carrier'] : 'custom';
            $title = (isset($trackrow['title']) && !empty($trackrow['title'])) ? $trackrow['title'] : '';
            $shipmentTrackData = $this->shipmentTrack->create();
            $shipmentTrackData->setOrderId($shipmentData->getOrderId());
            $shipmentTrackData->setParentId($shipmentData->getEntityId());
            $shipmentTrackData->setNumber($trackrow[self::TRACKNUMBER]);
            $shipmentTrackData->setCarrierCode(strtolower($carrier));
            $shipmentTrackData->setTitle($title);

            $this->trackRepository->create()->save($shipmentTrackData);
        }
    }

    /**
     * Verify if invoice is already synced
     *
     * @return AbstractDb|AbstractCollection|null
     * @createdBy Arushi Bansal
     */
    public function checkShipmentAlreadySync()
    {
        $customShipmentData = $this->customShipment->create()->getCollection();

        $customShipmentData->addFieldToSelect(self::SOURCE_SHIPMENT_ID)
            ->addFieldToFilter('target_shipment_id', $this->targetShipmentId);
        $customShipmentData->getSelect()->limit(1);

        return $customShipmentData;
    }

    /**
     * Get order details on basis of target id
     *
     * @param string $targetOrderId
     *
     * @return customOrderDataCollection
     * @throws LocalizedException
     * @updatedBy Arushi bansal
     */
    public function getCustomOrderByTargetId($targetOrderId)
    {
        $customOrderDataCollection = $this->customSalesOrder->create()->getCollection()
            ->addFieldToSelect('source_order_id')
            ->addFieldToFilter('target_order_id', $targetOrderId)
            ->setOrder('id', 'DESC');
        $customOrderDataCollection->getSelect()->limit(1);

        if ($customOrderDataCollection->getSize() == 0) {
            throw new LocalizedException(
                __("i95dev_order_not_exists"),
                null,
                108
            );
        }

        return $customOrderDataCollection;
    }

    /**
     * Fetch order by given order id
     *
     * @param string $orderId
     * @return array|null
     */
    public function getOrder($orderId)
    {
        return $this->erpOrderStatus->getOrderByIncrementId($orderId);
    }

    /**
     * Send email to customer once shipment done
     *
     * @param int $id
     * @return bool
     */
    public function sendEmail($id)
    {
        $email_sent = false;
        try {
        $email_sent = $this->shipmentMgmt->create()->notify($id);

        if ($email_sent) {
            return $email_sent;
        } else {
                $this->logger->create()->createLog(
                    __METHOD__,
                    "There was some issue in sending shipment email (Shipment id :-" . $id . ")",
                    LoggerInterface::INFO,
                    'info'
                );
                return false;
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                __METHOD__,
                "There was some issue in sending shipment email (Shipment id :-" . $id . ")",
                LoggerInterface::INFO,
                'info'
            );
            return false;
        }
    }

    /**
     * Fetch shipment by id
     *
     * @param int $id
     * @return array|null
     */
    public function getShipment($id)
    {
        $result = $this->shipmentRepo->create()->get($id);

        if ($result instanceof ShipmentInterface) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Adds comment to given shipment
     *
     * @param object $shipmentObject
     *
     * @return array|null
     */
    public function addComment($shipmentObject)
    {
        $shipmentCmt = $this->shipmentCmtFactory->create();
        $shipmentCmt->setComment("Notified customer about Shipment " . $shipmentObject->getIncrementId());
        $shipmentCmt->setIsCustomerNotified(1);
        $shipmentCmt->setIsVisibleOnFront(0);
        $shipmentCmt->setParentId($shipmentObject->getEntityId());
        $shipmentCmt->setEntityId($shipmentObject->getEntityId());
        $result = $this->shipmentCmtRepo->create()->save($shipmentCmt);

        if ($result instanceof ShipmentCommentInterface) {
            return $result;
        } else {
            $id = $shipmentObject->getEntityId();
            $this->logger->create()->createLog(
                __METHOD__,
                "There was some issue in sending shipment comments (Shipment id :-" . $id . ")",
                LoggerInterface::INFO,
                'info'
            );
            return null;
        }
    }

    /**
     * Add bundle childs to shipment and unset bundle parents
     *
     * @param array $bundleChilds
     * @param array $shipmentItemsData
     * @return array $shipmentItemsData
     */
    public function addBundleChilds($bundleChilds, $shipmentItemsData)
    {
        $bundleParents = [];
        if (!empty($bundleChilds)) {
            foreach ($bundleChilds as $childItemId => $eachChild) {
                $parentShippedQty = $shipmentItemsData[$eachChild['parentItemId']]['qty'];
                $shipmentItemsData[$childItemId] =
                    ['qty' => ($parentShippedQty * $eachChild['qtyOrdered']) / $eachChild['parentQtyOrdered']];
                if (!in_array($eachChild['parentItemId'], $bundleParents)) {
                    $bundleParents[] = $eachChild['parentItemId'];
                }
            }
        }
        if (!empty($bundleParents)) {
            foreach ($bundleParents as $eachParent) {
                unset($shipmentItemsData[$eachParent]);
            }
        }
        return $shipmentItemsData;
    }
}
