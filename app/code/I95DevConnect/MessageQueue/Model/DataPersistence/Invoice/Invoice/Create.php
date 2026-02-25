<?php /** @noinspection ALL */

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Invoice\Invoice;

use Exception;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\ErpOrderStatus;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\DataPersistence\ValidateFactory;
use I95DevConnect\MessageQueue\Model\SalesInvoiceFactory;
use I95DevConnect\MessageQueue\Model\SalesOrderFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\InvoiceCommentInterface;
use Magento\Sales\Api\Data\InvoiceCommentInterfaceFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\InvoiceSearchResultInterface;
use Magento\Sales\Api\InvoiceCommentRepositoryInterfaceFactory;
use Magento\Sales\Api\InvoiceManagementInterfaceFactory;
use Magento\Sales\Api\InvoiceOrderInterfaceFactory;
use Magento\Sales\Api\InvoiceRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Model\I95DevInvoiceHistoryFactory;
use I95DevConnect\MessageQueue\Model\I95DevInvoiceItemHistoryFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Class for creating invoice in magento
 */
class Create
{
    public const TRANSTYPE = 'transaction_type';

    /**
     * @var array
     */
    public $invoiceItems;

    /**
     * @var int
     */
    public $customSalesOrderId;

    /**
     * @var ErpOrderStatus
     */
    public $erpOrderStatus;

    /**
     * @var InvoiceShipment
     */
    public $invoiceShipment;

    /**
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     * @var Data
     */
    public $baseHelperData;

    /**
     * @var SalesInvoiceFactory
     */
    public $customInvoice;

    /**
     * @var SalesOrderFactory
     */
    public $customSalesOrder;

    /**
     * @var object
     */
    public $orderObject;

    /**
     * @var array
     */
    public $invoiceCollection;

    /**
     * @var int
     */
    public $totalQtyOrdered;

    /**
     * @var int
     */
    public $totalQtyInvoiced;

    /**
     * @var array
     */
    public $postData = [];

    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     * @var ManagerInterface
     */
    public $eventManager;

    /**
     * @var InvoiceRepositoryInterfaceFactory
     */
    public $invoiceRepository;

    /**
     * @var InvoiceManagementInterfaceFactory
     */
    public $invoiceMgmt;

    /**
     * @var InvoiceCommentRepositoryInterfaceFactory
     */
    public $invoiceCmtRepo;

    /**
     * @var InvoiceCommentInterfaceFactory
     */
    public $invoiceCmtFactory;

    /**
     * @var InvoiceOrderInterfaceFactory
     */
    public $invoiceOrder;

    /**
     * @var ValidateFactory
     */
    public $validate;

    /**
     * @var InvoiceItemCreationInterfaceFactory
     */
    public $invoiceItemObjFactory;

    /**
     * @var string[]
     */
    public $validateFields = [
        'targetId'=>'i95dev_empty_targetInvoiceId',
        'targetOrderId'=>'i95dev_empty_targetOrderId',
    ];

    /**
     * @var string
     */
    public $stringData;

    /**
     * @var string
     */
    public $entityCode;

    /**
     * @var int
     */
    public $targetInvoiceId;
    /**
     * @var int
     */
    public $invoiceId;
    /**
     * @var float
     */
    public $grandTotal;
    /**
     * @var array
     */
    public $invoiceItemEntity = [];

    /**
     * @var null
     */
    public $validationResult = null;

    /**
     *
     * @var I95DevInvoiceHistoryFactory
     */
    public $invoiceHistory;
    /**
     * @var I95DevInvoiceItemHistoryFactory
     */
    public $invoiceItemHistory;
    /**
     * @var ResourceConnection
     */
    protected $connection;
    /**
     * @var ResourceConnection
     */
    protected $resource;
    /**
     *
     * @param Data $baseHelperData
     * @param SalesInvoiceFactory $customInvoice
     * @param SalesOrderFactory $customSalesOrder
     * @param ErpOrderStatus $erpOrderStatus
     * @param InvoiceShipment $invoiceShipment
     * @param LoggerInterfaceFactory $logger
     * @param AbstractDataPersistence $abstractDataPersistence
     * @param ManagerInterface $eventManager
     * @param InvoiceRepositoryInterfaceFactory $invoiceRepository
     * @param InvoiceManagementInterfaceFactory $invoiceMgmt
     * @param InvoiceCommentRepositoryInterfaceFactory $invoiceCmtRepo
     * @param InvoiceCommentInterfaceFactory $invoiceCmt
     * @param InvoiceOrderInterfaceFactory $invoiceOrder
     * @param InvoiceItemCreationInterfaceFactory $invoiceItemObjFactory
     * @param ValidateFactory $validate
     * @param I95DevInvoiceHistoryFactory $invoiceHistory
     * @param I95DevInvoiceItemHistoryFactory $invoiceItemHistory
     * @param ResourceConnection $resource
     */
    public function __construct( // NOSONAR
        Data $baseHelperData,
        SalesInvoiceFactory $customInvoice,
        SalesOrderFactory $customSalesOrder,
        ErpOrderStatus $erpOrderStatus,
        InvoiceShipment $invoiceShipment,
        LoggerInterfaceFactory $logger,
        AbstractDataPersistence $abstractDataPersistence,
        ManagerInterface $eventManager,
        InvoiceRepositoryInterfaceFactory $invoiceRepository,
        InvoiceManagementInterfaceFactory $invoiceMgmt,
        InvoiceCommentRepositoryInterfaceFactory $invoiceCmtRepo,
        InvoiceCommentInterfaceFactory $invoiceCmt,
        InvoiceOrderInterfaceFactory $invoiceOrder,
        InvoiceItemCreationInterfaceFactory $invoiceItemObjFactory,
        ValidateFactory $validate,
        I95DevInvoiceHistoryFactory $invoiceHistory,
        I95DevInvoiceItemHistoryFactory $invoiceItemHistory,
        ResourceConnection $resource
    ) {
        $this->baseHelperData = $baseHelperData;
        $this->customInvoice = $customInvoice;
        $this->customSalesOrder = $customSalesOrder;
        $this->erpOrderStatus = $erpOrderStatus;
        $this->invoiceShipment=$invoiceShipment;
        $this->logger = $logger;
        $this->abstractDataPersistence = $abstractDataPersistence;
        $this->eventManager = $eventManager;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceMgmt = $invoiceMgmt;
        $this->invoiceCmtRepo = $invoiceCmtRepo;
        $this->invoiceCmtFactory = $invoiceCmt;
        $this->invoiceOrder = $invoiceOrder;
        $this->validate = $validate;
        $this->invoiceItemObjFactory = $invoiceItemObjFactory;
        $this->invoiceHistory = $invoiceHistory;
        $this->invoiceItemHistory = $invoiceItemHistory;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
    }

    /**
     * Create Invoice.
     *
     * @param array $stringData
     * @param string $entityCode
     * @param string $erp
     * @return I95DevResponseInterface
     * @updatedBy Arushi Bansal
     */
    public function createInvoice($stringData, $entityCode, $erp) // NOSONAR
    {
        $this->stringData = $stringData;
        $this->entityCode = $entityCode;

        try {
            $status = $this->validateData();
            if (is_object($status)) {
                return $status;
            }
            $prepareData = $this->preparePostData();
            if (is_array($prepareData)) {
                $this->invoiceItems = $prepareData;
                $response = $this->doInvoice($entityCode);
                // Updated by Sravani Polu for mapping target invoice id for existing invoice in magento.
            } elseif ($prepareData) {
                $response = $prepareData;
            } else {
                throw new LocalizedException(__('invoice_not_synced'));
            }

            return $this->abstractDataPersistence->setResponse(
                Data::SUCCESS,
                "Record Synced Successfully",
                $response
            );
        } catch (Exception $ex) {
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __($ex->getMessage())
            );
        }
    }

    /**
     * Prepare invoice item data array
     *
     * @return mixed
     * @createdBy Arushi Bansal
     * @throws LocalizedException
     * @throws Exception
     */
    public function preparePostData() // NOSONAR
    {
        $this->invoiceCollection = $this->getInvoices($this->orderObject->getEntityId());

        $orderItems = $this->orderObject->getItems();
        $productTypeMapArray = [];
        foreach ($orderItems as $item) {
            if ($item->getProductType() != 'configurable') {
                if ($item->getParentItemId() != null &&
                    $item->getParentItem()->getProductType() == 'bundle') {
                    $this->logger->create()->createLog(
                        __METHOD__,
                        'Skip bundle child item ' . $item->getId(),
                        \I95DevConnect\MessageQueue\Api\LoggerInterface::INFO,
                        'info'
                    );
                } else {
                    $productTypeMapArray[$item->getId()] = $item->getProductType();
                }
            }
        }

        $this->totalQtyInvoiced = 0;
        if (!$this->invoiceCollection) {
            $invoiceCount = 0;
        } else {
            $invoiceCount = count($this->invoiceCollection);
            $invoice = $this->invoiceCollection->getItems();

            foreach ($invoice as $itemsInInvoice) {
                $itemsInInvoice = $itemsInInvoice->getItems();
                foreach ($itemsInInvoice as $invoiceItemsArr) {
                    if (isset($productTypeMapArray[$invoiceItemsArr->getOrderItemId()])) {
                        $this->totalQtyInvoiced = $this->totalQtyInvoiced + $invoiceItemsArr->getQty();
                    }
                }
            }
        }

        $this->totalQtyOrdered  = $this->orderObject->getTotalQtyOrdered();
        if ($invoiceCount > 0 && $this->totalQtyInvoiced >= $this->totalQtyOrdered) {
            return $this->invoiceExists();
        }
        $mapArray = $this->getMapProductsIds();
        $itemsIds = $mapArray['itemsIds'] ?? [];
        $parentItemIds = $mapArray['parentItemIds'] ?? [];
        $productMapArray = $mapArray['productMapArray'] ?? [];
        $invoiceItemsArr = $this->prepareInvoiceItemByERPString(
            $this->invoiceItemEntity,
            $itemsIds,
            $parentItemIds,
            $productMapArray
        );

        foreach ($itemsIds as $itemId) {
            if (!key_exists($itemId, $invoiceItemsArr)) {
                $invoiceItemsArr[$itemId]['order_item_id'] = $itemId;
                $invoiceItemsArr[$itemId]['qty'] = 0;
            }
        }

        return $this->prepareItemArray($invoiceItemsArr);
    }

    /**
     * Prepare invoice items data on basis of erp data
     *
     * @param array $invoiceItemEntity
     * @param array $itemsIds
     * @param array $parentItemIds
     * @param array $mapArr
     * @return array
     * @createdBy Arushi Bansal
     */
    public function prepareInvoiceItemByERPString($invoiceItemEntity, $itemsIds, $parentItemIds, $mapArr) // NOSONAR
    {
        $productMapArray = $mapArr;
        $invItems = [];
        foreach ($invoiceItemEntity as $invoiceData) {
            $this->emptyFieldCheck("orderItemId", $invoiceData, "i95dev_empty_invoiceOrderItemId");
            $this->emptyFieldCheck("qty", $invoiceData, "i95dev_empty_qtyToInvoice");

            /** @updatedBy Debashis S. Gopal. Validating shipment item sku irrespective of their case **/
            $erpSku = strtolower($invoiceData['orderItemId']);
            if (isset($productMapArray[$erpSku])) {
                $productId = $productMapArray[$erpSku];
            } else {
                throw new LocalizedException(
                    __('i95dev_invalid_invoice_item %1', $invoiceData['orderItemId'])
                );
            }

            $itemId = $itemsIds[$productId];
            if (isset($parentItemIds[$itemId])) {
                $parentItemId = $parentItemIds[$itemId];
                if ($parentItemId > 0) {
                    $itemId = $parentItemId;
                }
            }

            // phpcs:disable
            $invItems[$itemId]['order_item_id'] = $itemId;
            if (isset($invoiceData['qty'])) {
                if (isset($invItems[$itemId]['qty']) && $invItems[$itemId]['qty'] > 0) {
                    $invItems[$itemId]['qty'] += $invoiceData['qty'];
                } else {
                    $invItems[$itemId]['qty'] = $invoiceData['qty'];
                }
            } else {
                $invItems[$itemId]['qty'] = 0;
            }
            // phpcs:enable
        }

        return $invItems;
    }

    /**
     * Check for internal field if they exists
     *
     * @param string $fieldname
     * @param string $dataString
     * @param string $errormsg
     * @throws LocalizedException
     * @createdBy Arushi Bansal
     * @noinspection PhpIllegalStringOffsetInspection
     */
    public function emptyFieldCheck($fieldname, $dataString, $errormsg)
    {

        if (!isset($dataString[$fieldname]) || $dataString[$fieldname] === "") {
            throw new LocalizedException(__($errormsg));
        }
    }

    /**
     * Prepare item array
     *
     * @param array $invoiceItems
     * @return array
     * @createdBy Arushi Bansal
     */
    public function prepareItemArray($invoiceItems)
    {
        $invoiceItemObj = [];
        foreach ($invoiceItems as $items) {
            $item = $this->invoiceItemObjFactory->create();
            $item->setOrderItemId($items['order_item_id']);
            $item->setQty($items['qty']);

            $invoiceItemObj[] = $item;
        }

        return $invoiceItemObj;
    }

    /**
     * Method to do invoice for an existing order
     *
     * @param string $entityCode
     * @return string|null
     * @throws LocalizedException
     * @throws Exception
     * @updatedBy Arushi Bansal
     */
    public function doInvoice($entityCode)
    {
        $component = $this->baseHelperData->getComponent();

        $beforeeventname = 'erpconnect_messagequeuetomagento_beforesave_' . $entityCode;
        $this->eventManager->dispatch($beforeeventname, ['currentObject' => $this]);

        $isCaptureAllowed = $this->baseHelperData->isCaptureInvoiceEnabled();
        $capture = (bool)$isCaptureAllowed;

        $this->baseHelperData->unsetGlobalValue('i95_observer_skip');
        $this->baseHelperData->setGlobalValue('i95_observer_skip', true);

        /** @updatedBy kavya.k. if created date comes from erp then set created date **/
        $invoiceCreatedDate = isset($this->stringData['createdDate']) ? $this->stringData['createdDate'] : '' ;
        $this->baseHelperData->unsetGlobalValue('invoice_date');
        $this->baseHelperData->setGlobalValue('invoice_date', $invoiceCreatedDate);

        $result = $this->invoiceOrder->create()->execute(
            $this->orderObject->getEntityId(),
            $capture,
            $this->invoiceItems,
            false,
            false
        );

        $invoiceData = $this->getInvoice($result);
        if ($invoiceData) {
            $invoicedQty = 0;
            $this->invoiceId = $invoiceData->getId();
            foreach ($this->invoiceItemEntity as $invoiceItem) {
                $invoicedQty += $invoiceItem['qty'];
            }

            $this->saveCustomI95DevInvoice(
                $invoiceData->getIncrementId(),
                $invoicedQty,
                $this->targetInvoiceId
            );

            $this->saveI95DevInvoiceHistory($invoiceData->getIncrementId());
            $this->erpOrderStatus->updateCustomOrderStatus(
                $this->customSalesOrderId,
                $this->orderObject->getEntityId()
            );

            if ($component == 'AX') {
                $this->invoiceShipment->checkForShipment($this->orderObject, $this->targetInvoiceId);
            }

            if ($this->baseHelperData->isEmailNotifyEnable('invoice') &&
                $this->sendEmail($invoiceData->getId())) {
                $this->addComment($invoiceData);
            }

            $aftereventname = 'erpconnect_messagequeuetomagento_aftersave_' . $entityCode;
            $this->eventManager->dispatch($aftereventname, ['currentObject' => $this]);

            return $invoiceData->getIncrementId();
        } else {
            throw new LocalizedException(__('invoice_not_synced'));
        }
    }

    /**
     * Validate invoice Data.
     *
     * @throws LocalizedException
     * @throws Exception
     * @updatedBy Arushi Bansal
     */
    public function validateData()
    {
        $this->validate = $this->validate->create();
        $this->validate->validateFields = $this->validateFields;
        if ($this->validate->validateData($this->stringData)) {
            $targetOrderId = $this->baseHelperData->getValueFromArray("targetOrderId", $this->stringData);
            /*@FIX - CLOUD-551*/
            $customOrderDataCollection = $this->getCustomOrderByTargetId($targetOrderId);
            $this->customSalesOrderId = $customOrderDataCollection->getData()[0]['source_order_id'];
            $this->orderObject = $this->getOrder($this->customSalesOrderId);

            if (!$this->orderObject) {
                throw new LocalizedException(__("order_not_exist"));
            }

            $this->invoiceItemEntity = $this->baseHelperData->getValueFromArray("invoiceItemEntity", $this->stringData);
            if ($this->invoiceItemEntity == "") {
                throw new LocalizedException(__('i95dev_invoice_itemNotExist'));
            }

            $this->targetInvoiceId = $this->baseHelperData->getValueFromArray("targetId", $this->stringData);
            $this->grandTotal = $this->baseHelperData->getValueFromArray(
                "grandTotal",
                $this->stringData
            );
            $transType = $this->checkPaymentType();
            if ($transType === 'auth_capture') {
                return $this->checkInvoiceAlreadySync($this->targetInvoiceId);
            }
        } else {
            return $this->abstractDataPersistence->setResponse(Data::ERROR, __("validation_error"));
        }
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
            throw new LocalizedException(__("i95dev_order_not_exists"));
        }

        return $customOrderDataCollection;
    }

    /**
     * Verify if invoice is already synced
     *
     * @param string $targetInvoiceId
     * @return I95DevResponseInterface|null
     * @throws LocalizedException
     * @throws Exception
     * @createdBy Arushi Bansal
     */
    public function checkInvoiceAlreadySync($targetInvoiceId) // NOSONAR
    {
        $customInvoiceObj = $this->customInvoice->create();
        $customInvoiceCollection = $customInvoiceObj->getCollection()
            ->addFieldToFilter('target_invoice_id', $targetInvoiceId);

        if ($customInvoiceCollection->getSize() > 0) {
            $cusInvoice = $customInvoiceCollection->getData();
            return $this->abstractDataPersistence->setResponse(
                Data::SUCCESS,
                "Record Successfully Synced",
                $cusInvoice[0]['source_invoice_id']
            );
        } else {
            $this->invoiceCollection = $this->getInvoices($this->orderObject->getEntityId());
            if ($this->invoiceCollection) {
                $response = $this->invoiceExists();
                if ($response) {
                    $customInvoiceList = $customInvoiceObj->getCollection()
                        ->addFieldToFilter('source_invoice_id', $response);
                    if ($customInvoiceList->getSize() > 0) {
                        $data = $customInvoiceList->getData();
                        $targetInvoiceList = explode(",", $data[0]['target_invoice_id']);
                        if (in_array($targetInvoiceId, $targetInvoiceList)) {
                            return $this->abstractDataPersistence->setResponse(
                                Data::SUCCESS,
                                "Record Successfully Synced",
                                $response
                            );
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * Check if invoice exists for an order or not
     *
     * @return string
     * @throws Exception
     * @updatedBy Arushi Bansal
     */
    public function invoiceExists()
    {
        $invoice = $this->invoiceCollection->getData();
        $invoiceIncId = $invoice[0]['increment_id'];
        $invoicedQty = 0;

        $transType = $this->checkPaymentType();

        foreach ($this->invoiceItemEntity as $invoiceItem) {
            $invoicedQty += $invoiceItem['qty'];
        }

        $customInvoiceData = $this->customInvoice->create()
            ->load($invoiceIncId, 'source_invoice_id')->getData();

        $targetId = '';
        if (!empty($customInvoiceData)) {
            $customInvoicId = $customInvoiceData['id'];
            $custInvoice = $this->customInvoice->create()->load($customInvoicId);
            $targetInvId = $this->baseHelperData->getValueFromArray(
                "targetId",
                $this->stringData
            );
            if ($custInvoice->getTargetInvoiceId()) {
                $targetInvoiceIdList = array_unique(explode(',', $custInvoice->getTargetInvoiceId()));
                if (!in_array($targetInvId, $targetInvoiceIdList)
                    && $transType === 'auth_capture') {
                    array_push($targetInvoiceIdList, $targetInvId);
                }
                $targetId = implode(",", $targetInvoiceIdList);
            } else {
                $targetId = $targetInvId;
            }
            $invoicedQty += (is_numeric($custInvoice->getTargetInvoicedQty()))?$custInvoice->getTargetInvoicedQty():0;
            $customInvoiceObj = $custInvoice;
        } else {
            $targetId = $this->baseHelperData->getValueFromArray(
                "targetId",
                $this->stringData
            );
            $customInvoiceObj = null;
        }
        $this->invoiceId = $invoice[0]['entity_id'];
        $beforeEvent = 'erpconnect_custominvoice_beforesave';
        $this->eventManager->dispatch($beforeEvent, ['currentObject' => $this]);
        $this->saveCustomI95DevInvoice($invoiceIncId, $invoicedQty, $targetId, $customInvoiceObj);
        $this->saveI95DevInvoiceHistory($invoiceIncId);
        $this->erpOrderStatus->updateCustomOrderStatus($this->customSalesOrderId, $this->orderObject->getEntityId());
        $afterEvent = 'erpconnect_custominvoice_aftersave';
        $this->eventManager->dispatch($afterEvent, ['currentObject' => $this]);

        return $invoiceIncId;
    }

    /**
     * Function responsible to save data in custom invoice table
     *
     * @param int $invoiceId
     * @param int $invoicedQty
     * @param int $targetInvoiceId
     * @param Object $customInvoiceObj
     *
     * @throws Exception
     * @createdBy Arushi Bansal
     */
    public function saveCustomI95DevInvoice($invoiceId, $invoicedQty, $targetInvoiceId, $customInvoiceObj = null)
    {
        try {
            if (!isset($customInvoiceObj)) {
                $customInvoiceData = $this->customInvoice->create();
            } else {
                $customInvoiceData = $customInvoiceObj;
            }
            $customInvoiceData->setSourceInvoiceId($invoiceId);
            $customInvoiceData->setTargetInvoicedQty($invoicedQty);
            $customInvoiceData->setTargetInvoiceId($targetInvoiceId);
            $customInvoiceData->setUpdatedDt($this->baseHelperData->date->gmtDate());
            $customInvoiceData->setUpdateBy('ERP');
            $customInvoiceData->save();
        } catch (LocalizedException $e) {
            $this->logger->create()->createLog(
                __METHOD__,
                $e->getMessage(),
                \I95DevConnect\MessageQueue\Api\LoggerInterface::INFO,
                'info'
            );
        }
    }

    /**
     * Get order data by order id
     *
     * @param string $orderId
     * @return array
     * @updatedBy Arushi Bansal
     */
    public function getOrder($orderId)
    {
        return $this->erpOrderStatus->getOrderByIncrementId($orderId);
    }

    /**
     * Get Invoices of  given order id
     *
     * @param string $orderId
     * @return InvoiceSearchResultInterface|null
     * @updatedBy Arushi Bansal
     */
    public function getInvoices($orderId)
    {
        return $this->erpOrderStatus->getInvoices($orderId);
    }

    /**
     * Get Invoice by invoice id
     *
     * @param string $id
     *
     * @return InvoiceInterface|NULL
     * @updatedBy Arushi Bansal
     */
    public function getInvoice($id)
    {
        $result = $this->invoiceRepository->create()->get($id);

        if ($result instanceof InvoiceInterface) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Send invoice mail to customer
     *
     * @param string $id
     * @return string|null
     * @updatedBy Arushi Bansal
     */
    public function sendEmail($id)
    {
        $email_sent = false;
        try {
        $email_sent = $this->invoiceMgmt->create()->notify($id);

        if ($email_sent) {
            return $email_sent;
        } else {
                $this->logger->create()->createLog(
                    __METHOD__,
                    "There was some issue in sending invoice email (Invoice id :-"  . $id . ")",
                    \I95DevConnect\MessageQueue\Api\LoggerInterface::INFO,
                    'info'
                );
                return false;
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                __METHOD__,
                "There was some issue in sending invoice email (Invoice id :-"  . $id . ")",
                \I95DevConnect\MessageQueue\Api\LoggerInterface::INFO,
                'info'
            );
            return false;
        }
    }

    /**
     * Add comments to the given invoice
     *
     * @param InvoiceInterface $invoiceData
     *
     * @return string|null
     * @updatedBy Arushi Bansal
     */
    public function addComment($invoiceData)
    {

        $invoiceCmt = $this->invoiceCmtFactory->create();
        $invoiceCmt->setComment("Notified customer about invoice " . $invoiceData['increment_id']);
        $invoiceCmt->setIsCustomerNotified(1);
        $invoiceCmt->setIsVisibleOnFront(0);
        $invoiceCmt->setParentId($invoiceData['entity_id']);
        $invoiceCmt->setEntityId($invoiceData['entity_id']);

        $result = $this->invoiceCmtRepo->create()->save($invoiceCmt);

        if ($result instanceof InvoiceCommentInterface) {
            return $result;
        } else {
            $id = $invoiceData['increment_id'];
            $this->logger->create()->createLog(
                __METHOD__,
                "There was some issue in sending invoice comments (Invoice id :-"  . $id . ")",
                \I95DevConnect\MessageQueue\Api\LoggerInterface::INFO,
                'info'
            );
            return null;
        }
    }

    /**
     * Check order payment type
     *
     * @return string
     */
    public function checkPaymentType()
    {
        $transactionDetails = $this->orderObject->getPayment()->getAdditionalInformation();
        $transactionType = '';
        if (isset($transactionDetails[self::TRANSTYPE])) {
            $transactionType = $transactionDetails[self::TRANSTYPE];
        }
        return strtolower($transactionType);
    }

    /**
     * Get array of mapped product ids
     *
     * @return array
     */
    public function getMapProductsIds()
    {
        $itemsIds = [];
        $parentItemIds = [];
        $productMapArray = [];
        foreach ($this->orderObject->getItems() as $item) {
            $itemsIds[$item->getProductId()] = $item->getItemId();
            /** @updatedBy Debashis S. Gopal. Validating shipment item sku irrespective of their case **/
            $productMapArray[strtolower($item->getSku())] = $item->getProductId();
            $product_id = $item->getParentItem();
            if (isset($product_id)) {
                $parentItemIds[$item->getItemId()] = $item->getParentItemId();
            }
        }
        return ['itemsIds' => $itemsIds, 'parentItemIds' => $parentItemIds, 'productMapArray' => $productMapArray];
    }
    /**
     * Save invoice history and item details
     *
     * @param string $invoiceId
     * @throws \Exception
     * @addedBy Subhan. To store invoice history and its items
     */
    public function saveI95DevInvoiceHistory($invoiceId)
    {
        $targetInvoiceId = $this->baseHelperData->getValueFromArray(
            'targetId',
            $this->stringData
        );
        $targetOrderId = $this->baseHelperData->getValueFromArray(
            'targetOrderId',
            $this->stringData
        );
        try {
            $invoiceHistory = $this->invoiceHistory->create();
            $invoiceHistory->setTargetInvoiceId($targetInvoiceId);
            $invoiceHistory->setTargetOrderId($targetOrderId);
            $invoiceHistory->save();
            $invoiceEntityId = $invoiceHistory->getId();
            foreach ($this->invoiceItemEntity as $invoiceItem) {
                $data[] = [
                    'invoice_entity_id' => $invoiceEntityId,
                    'item_sku' => $invoiceItem['orderItemId'],
                    'item_qty' => $invoiceItem['qty']
                ];
            }
            $invoiceItemsTable = $this->resource->getTableName('i95dev_sales_invoice_item_history');
            $this->connection->insertMultiple($invoiceItemsTable, $data);
        } catch (LocalizedException $e) {
            $this->logger->create()->createLog(
                __METHOD__,
                $e->getMessage(),
                \I95DevConnect\MessageQueue\Api\LoggerInterface::INFO,
                'info'
            );
        }
    }
}
