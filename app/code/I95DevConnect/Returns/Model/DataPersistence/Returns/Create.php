<?php

namespace I95DevConnect\Returns\Model\DataPersistence\Returns;

use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use I95DevConnect\MessageQueue\Model\SalesOrderFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Rma\Model\RmaFactory;
use Magento\Rma\Model\Rma\RmaDataMapper;
use Magento\Rma\Model\ResourceModel\ItemFactory as RmaItemFactory;
use Magento\Rma\Model\Rma\Source\Status as RmaStatus;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use I95DevConnect\Returns\Model\RmaEntityFactory;
use I95DevConnect\Returns\Helper\Data as RmaHelper;
use Magento\Rma\Model\ResourceModel\Item\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Rma\Model\Rma\Status\HistoryFactory as RMAHistory;

class Create
{
    /**
     *
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     *
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     *
     * @var Validate
     */
    public $validate;

    /**
     *
     * @var Data
     */
    public $dataHelper;

    /**
     *
     * @var  Order
     */
    public $order;

    /**
     *
     * @var Customer
     */
    public $customer;

    /**
     *
     * @var ProductFactory
     */
    public $productFactory;

    /**
     *
     * @var  Product
     */
    public $productRepo;

    /**
     *
     * @var DateTime
     */
    public $date;
    /**
     * @var string[]
     */
    public $validateFields = [
        'returnItems' => 'Return Items Required'
    ];
    /**
     * @var SalesOrderFactory
     */
    protected $i95devOrderFactory;
    /**
     * @var RmaEntityFactory
     */
    // @codingStandardsIgnoreLine
    protected $_i95devRma;
    /**
     * @var RmaFactory
     */
    // @codingStandardsIgnoreLine
    protected $magentoRma;

    /**
     * @var Registry
     */
    // @codingStandardsIgnoreLine
    protected $_coreRegistry;
    /**
     * @var RmaDataMapper
     */
    protected $rmaDataMapper;
    /**
     * @var RmaItemFactory
     */
    // @codingStandardsIgnoreLine
    protected $_rmaitemFactory;
    /**
     * @var RmaStatus
     */
    // @codingStandardsIgnoreLine
    protected $_rmaStatus;
    /**
     * @var ResourceConnection
     */
    // @codingStandardsIgnoreLine
    protected $_resourceConn;
    /**
     * @var RmaHelper
     */
    // @codingStandardsIgnoreLine
    public $_rmaHelper;
    /**
     * @var CollectionFactory
     */
    // @codingStandardsIgnoreLine
    public $_itemsFactory;
    /**
     * @var object
     */
    // @codingStandardsIgnoreLine
    public $_component;
    /**
     * @var \I95DevConnect\MessageQueue\Model\I95DevInvoiceHistoryFactory
     */
    // @codingStandardsIgnoreLine
    public $_salesInvHistory;

    /**
     * @var RMAHistory
     */
    public $rmaHistory;

    /**
     * @var string
     */
    public $stringData;
    
    /**
     * @var string|null
     */
    public $entityCode;

    /**
     * @var string
     */
    public $reason;

    /**
     * @var string
     */
    public $condition;

    /**
     * @var string
     */
    public $resolution;
    
    /**
     * @var string
     */
    public $itemsku;
    
    /**
     * @var string
     */
    public $itemstatus;

    /**
     * @var string
     */
    public $qtyreturned;

    /**
     * @var string
     */
    public $qtyyreturned;

    /**
     * Create constructor.
     * @param AbstractDataPersistence $abstractDataPersistence
     * @param LoggerInterfaceFactory $logger
     * @param Validate $validate
     * @param Data $dataHelper
     * @param Order $order
     * @param Customer $customer
     * @param ProductFactory $productFactory
     * @param Product $productRepo
     * @param DateTime $date
     * @param SalesOrderFactory $i95devOrderFactory
     * @param RmaEntityFactory $rmaEntity
     * @param RmaFactory $magentoRma
     * @param Registry $coreRegistry
     * @param RmaDataMapper $rmaDataMapper
     * @param RmaItemFactory $rmaitem
     * @param RmaHelper $rmaHelper
     * @param CollectionFactory $itemsFactory
     * @param RmaStatus $rmaStatus
     * @param \I95DevConnect\MessageQueue\Model\I95DevInvoiceHistoryFactory $salesInvHistory
     * @param ResourceConnection $resourceConn
     * @param RMAHistory $rmaHistory
     */
    public function __construct(
        AbstractDataPersistence $abstractDataPersistence,
        LoggerInterfaceFactory $logger,
        Validate $validate,
        Data $dataHelper,
        Order $order,
        Customer $customer,
        ProductFactory $productFactory,
        Product $productRepo,
        DateTime $date,
        SalesOrderFactory $i95devOrderFactory,
        RmaEntityFactory $rmaEntity,
        RmaFactory $magentoRma,
        Registry $coreRegistry,
        RmaDataMapper $rmaDataMapper,
        RmaItemFactory $rmaitem,
        RmaHelper $rmaHelper,
        CollectionFactory $itemsFactory,
        RmaStatus $rmaStatus,
        \I95DevConnect\MessageQueue\Model\I95DevInvoiceHistoryFactory $salesInvHistory,
        ResourceConnection $resourceConn,
        RMAHistory $rmaHistory
    ) {
        $this->abstractDataPersistence = $abstractDataPersistence;
        $this->logger = $logger;
        $this->validate = $validate;
        $this->date = $date;
        $this->order = $order;
        $this->dataHelper = $dataHelper;
        $this->customer = $customer;
        $this->productFactory = $productFactory;
        $this->productRepo = $productRepo;
        $this->i95devOrderFactory = $i95devOrderFactory;
        $this->_i95devRma = $rmaEntity;
        $this->magentoRma = $magentoRma;
        $this->_coreRegistry = $coreRegistry;
        $this->rmaDataMapper = $rmaDataMapper;
        $this->_rmaitemFactory = $rmaitem;
        $this->_rmaHelper = $rmaHelper;
        $this->_rmaStatus = $rmaStatus;
        $this->_itemsFactory = $itemsFactory;
        $this->_salesInvHistory = $salesInvHistory;
        $this->_resourceConn = $resourceConn;
        $this->rmaHistory = $rmaHistory;
    }

    /**
     * Create Return.
     *
     * @param array $stringData
     * @param string $entityCode
     * @param string $erp
     * @return I95DevResponseInterface
     * @throws LocalizedException
     */

    public function createReturn($stringData, $entityCode, $erp) // NOSONAR
    {
        try {
            $this->dataHelper->unsetGlobalValue('i95_observer_skip');
            $this->dataHelper->setGlobalValue('i95_observer_skip', true);

            $this->_component = $this->dataHelper
                ->getscopeConfig('i95dev_messagequeue/I95DevConnect_settings/component', ScopeInterface::SCOPE_STORE);
            $this->stringData = $stringData;
            $this->entityCode = $this->dataHelper->getValueFromArray("entityCode", $this->stringData);

            $cancelStatus = $this->cancelReturn();
            if ($cancelStatus["status"]) {
                return $this->abstractDataPersistence->setResponse(
                    Data::SUCCESS,
                    __("Returns synced successfully"),
                    $cancelStatus["id"]
                );
            }

            $this->validateReturnsData();

            $components = ['BC','GP'];
            if (in_array($this->_component, $components)) {
                $targetReturnId = $this->dataHelper->getValueFromArray("targetId", $this->stringData);
            } else {
                $targetReturnId = $this->dataHelper->getValueFromArray("targetReturnId", $this->stringData);
            }

            $targetReturnReceiveId = '';
            if ($this->entityCode == 'returnreceive') {
                $targetReturnReceiveId = $this->dataHelper->getValueFromArray("targetId", $this->stringData);
                $targetReturnId = $this->dataHelper->getValueFromArray("targetReturnId", $this->stringData);
            }

            if (empty($targetReturnId)) {
                return $this->abstractDataPersistence->setResponse(
                    Data::ERROR,
                    __("Please mention targetReturnId"),
                    null
                );
            }

            $targetOrderId = $this->dataHelper->getValueFromArray("targetOrderId", $this->stringData);
            $targetInvoiceId = $this->dataHelper->getValueFromArray("targetInvoiceId", $this->stringData);
            if ($targetOrderId != null) {
                $orderId = $this->getMagentoOrderId($targetOrderId);
            } elseif ($targetInvoiceId != null) {
                $sInvHisty = $this->_salesInvHistory->create()->getCollection()
                    ->addFieldToFilter('target_invoice_id', $targetInvoiceId)->getFirstItem();
                $orderId = $this->getMagentoOrderId($sInvHisty->getTargetOrderId());
            } else {
                $orderId = '';
                return $this->abstractDataPersistence->setResponse(
                    Data::ERROR,
                    "Please specify either targetOrderId or targetInvoiceId",
                    null
                );
            }

            if ($orderId != '') {
                $sales_order = $this->order->loadByIncrementId($orderId);
                if ($targetReturnId) {
                    $returnsRecord = $this->_i95devRma->create()->load($targetReturnId, 'target_return_id');

                    $returnitemsList = [];
                    $orderItemArr = [];
                    $magentoRmaId = $returnsRecord->getReturnId() ?? '';
                    $this->getReturnedItems($sales_order, $returnitemsList, $magentoRmaId, $orderItemArr);

                    $itemsArr = [
                        "items" => $returnitemsList
                    ];
                    $model = $this->_initModel($magentoRmaId, $sales_order->getEntityId());
                    $saveRequest = $this->rmaDataMapper->filterRmaSaveRequest($itemsArr);
                    $isNew = false;
                    if ($magentoRmaId) {
                        $itemStatuses = $this->rmaDataMapper->combineItemStatuses($saveRequest['items'], $magentoRmaId);
                        $model->setStatus($this->_rmaStatus->getStatusByItems($itemStatuses))->setIsUpdate(1);
                    } else {
                        $model->setData(
                            $this->rmaDataMapper->prepareNewRmaInstanceData(
                                $saveRequest,
                                $this->_coreRegistry->registry('current_order')
                            )
                        );
                        $isNew = true;
                    }

                    $savedRma = $model->saveRma($saveRequest);

                    if (!$savedRma) {
                        throw new LocalizedException(__('We can\'t save this RMA.'));
                    }

                    try {
                        if ($isNew && $savedRma->getId()) {
                            $comment = __('i95dev_new_return');
                            $history = $this->rmaHistory->create();
                            $history->setRmaEntityId($savedRma->getId());
                            $history->sendNewRmaEmail();
                            $history->saveComment($comment, true, true);
                        }
                    } catch (\Magento\Framework\Exception\MailException $exception) {
                        $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($exception);
                        $this->messageManager->addWarning(
                            __('You did not email your customer. Please check your email settings.')
                        );
                    }

                    if ($magentoRmaId) {
                        $this->unAuthorizedItems($orderItemArr, $magentoRmaId);
                        $returnsRecord->setUpdateBy("ERP");
                        $returnsRecord->setTargetReceiveId($targetReturnReceiveId);
                        $returnsRecord->setUpdatedDt($this->date->gmtDate());
                        $returnsRecord->save();
                    } else {
                        $returnsRecord->setReturnId($savedRma->getId());
                        $returnsRecord->setTargetReturnId($targetReturnId);
                        $returnsRecord->setTargetReceiveId($targetReturnReceiveId);
                        $returnsRecord->setTargetOrderId($targetOrderId);
                        $returnsRecord->setUpdateBy("ERP");
                        $returnsRecord->setCreatedDt($this->date->gmtDate());
                        $returnsRecord->setUpdatedDt($this->date->gmtDate());
                        $returnsRecord->save();
                    }
                    $this->dataHelper->setGlobalValue('reverseRma', true);
                }
                if ($savedRma->getId() != 0) {
                    return $this->abstractDataPersistence->setResponse(
                        Data::SUCCESS,
                        __("Returns synced successfully"),
                        $savedRma->getId()
                    );
                } else {
                    return $this->abstractDataPersistence->setResponse(
                        Data::ERROR,
                        "Unable to Sync returns",
                        null
                    );
                }
            }
        } catch (\Exception $e) {
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                $e->getMessage(),
                null
            );
        }
    }

    /**
     * Validating return data
     */
    public function validateReturnsData()
    {
        try {
            $this->validate->validateFields = $this->validateFields;
            $this->validate->validateData($this->stringData);
        } catch (Exception $ex) {
            $this->logger->create()->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
            throw new LocalizedException(__($ex->getMessage()));
        }
    }

    /**
     * Get magento order id
     *
     * @param string $erp_order_id
     * @return mixed
     */
    public function getMagentoOrderId($erp_order_id)
    {
        if (!empty($erp_order_id)) {
            $orderData = $this->i95devOrderFactory->create()->load($erp_order_id, 'target_order_id');
            if (!$orderData->getSourceOrderId()) {
                throw new LocalizedException(__("i95dev_order_not_exists"));
            }

            return $orderData->getSourceOrderId();
        }
    }

    /**
     * Get returned items
     *
     * @param object $sales_order
     * @param array $magentormaItems
     * @param string $magentoRmaId
     * @param array $orderItemArr
     */
    protected function getReturnedItems($sales_order, &$magentormaItems, $magentoRmaId, &$orderItemArr) //NOSONAR
    {
        $items = $this->dataHelper->getValueFromArray("returnItems", $this->stringData);
        $components = ['NAV','BC','GP','D365FO'];
        $i = 0;
        $magentormaItems = [];
        foreach ($items as $item) {
            $qtyType = $this->checkQtyType($item);
            if ($qtyType != 'requested' && empty($item['quantityReturned'])) {
                throw new LocalizedException(__('Please specify return quantity'));
            }
            if (in_array($this->_component, $components)) {
                $orderItemId = $this->getOrderItemIdBySku($sales_order, $item['sku']);
                $orderItemQty = $item['quantityReturned'];
            } else {
                $orderItemId = $this->getOrderItemIdBySku($sales_order, $item['Sku']);
                $orderItemQty = $item['QuantityReturned'];
            }
            array_push($orderItemArr, $orderItemId);
            if (empty($orderItemId)) {
                throw new LocalizedException(__("Requested items does not exists"));
            }

            $requestedItems[$orderItemId] = $orderItemQty;
            if ($this->entityCode == 'returnreceive' || $magentoRmaId) {
                unset($requestedItems);
                $requestedItems = [];
            }
            $this->checkItemDetails($item);
            $qtykey1 = '';
            $qtykey2 = '';
            $qtykey3 = '';
            $qtykey4 = '';
            if ($qtyType == 'authorized') {
                $qtykey1 = 'qty_requested';
                $qtykey2 = 'qty_authorized';
            } elseif ($qtyType == 'approved') {
                $qtykey4 = 'qty_returned';
                $qtykey3 = 'qty_approved';
                $qtykey2 = 'qty_authorized';
            } else {
                $qtykey1 = 'qty_requested';
            }
            if ($this->entityCode == 'returnreceive' || !$magentoRmaId) {
                $qtykey1 = 'qty_requested';
            }
            $entityId = $this->getItemEntityId($orderItemId, $magentoRmaId);
            $keyArr = $entityId ?? 'new' . $i;
            // check if same key exists
            if (array_key_exists($keyArr, $magentormaItems)) {
                $keyArr .= "-" . $i;
            }
            $otherreason = '';
            if ($this->_rmaHelper->getRmaAttributeId('reason', $this->reason, $this->itemsku) == 'other') {
                $otherreason = $this->reason;
            }
            if (strtolower($this->resolution) == 'credit') {
                $resolution = $this->_rmaHelper->getRmaAttributeId('resolution', 'Refund', $this->itemsku);
            } elseif (strtolower($this->resolution) == 'exchange') {
                $resolution = $this->_rmaHelper->getRmaAttributeId('resolution', 'Exchange', $this->itemsku);
            } else {
                $resolution = '';
            }
            $magentormaItems[$keyArr] = [
                $qtykey1 => $orderItemQty,
                $qtykey2 => $orderItemQty,
                "reason" => $this->_rmaHelper->getRmaAttributeId('reason', $this->reason, $this->itemsku),
                "reason_other" => $otherreason,
                "condition" => '',
                "resolution" => $resolution,
                /*"condition" => $this->_rmaHelper->getRmaAttributeId('condition', $this->condition, $this->itemsku),
                "resolution" => $this->_rmaHelper->getRmaAttributeId('resolution', $this->resolution, $this->itemsku),*/
                "order_item_id" => $orderItemId
            ];
            if ($qtykey3 != '') {
                $magentormaItems[$keyArr][$qtykey3] = $orderItemQty;
            }
            if ($qtykey4 != '') {
                $magentormaItems[$keyArr][$qtykey4] = $orderItemQty;
            }
            if ($magentoRmaId) {
                unset($magentormaItems[$keyArr]['qty_requested']);
                $magentormaItems[$keyArr]['status'] = $this->itemstatus;
            }
            $i++;
        }
        $itemsValid = $this->validateReturnItems($requestedItems, $sales_order);
        if (!$itemsValid) {
            throw new LocalizedException(__("Requested returns quantity is more than shipped"));
        }
    }

    /**
     * Get order itemid by sku
     *
     * @param OrderInterface $salesOrder
     * @param string $sku
     * @return int|null
     */
    private function getOrderItemIdBySku(OrderInterface $salesOrder, $sku)
    {
        foreach ($salesOrder->getItems() as $item) {
            if (strtolower($item->getSku()) === strtolower($sku)) {
                return $item->getItemId();
            }
        }

        return null;
    }

    /**
     * Validate return items
     *
     * @param array $requestedArr
     * @param object $sales_order
     * @return bool
     */
    protected function validateReturnItems($requestedArr, $sales_order)
    {
        $itemResource = $this->_rmaitemFactory->create();
        $returnableItems = $itemResource->getReturnableItems($sales_order->getEntityId());
        $valid = 0;
        if (!$requestedArr) {
            $valid = 1;
        }
        foreach (array_filter($returnableItems) as $key => $items) {
            if (array_key_exists($key, $requestedArr)) {
                if ($requestedArr[$key] <= $items) {
                    $valid = 1;
                } else {
                    $valid = 0;
                    break;
                }
            }
        }
        if ($valid) {
            return true;
        }
    }

    /**
     * Initializing model
     *
     * @param int $rmaId
     * @param int $orderId
     * @return Rma
     * @codingStandardsIgnoreStart
     */
    protected function _initModel($rmaId, $orderId)
    {
        /** @var $model Rma */
        $model = $this->magentoRma->create();

        if ($rmaId) {
            $model->load($rmaId);
            if (!$model->getId()) {
                throw new LocalizedException(__('The wrong RMA was requested.'));
            }
            $this->_coreRegistry->register('current_rma', $model);
        }

        if ($orderId) {
            /** @var $order Order */
            $sorder = $this->order->load($orderId);
            if (!$sorder->getId()) {
                throw new LocalizedException(__('This is the wrong RMA order ID.'));
            }
            $this->_coreRegistry->register('current_order', $sorder);
        }

        return $model;
    }
    // @codingStandardsIgnoreEnd

    /**
     * Check item details
     *
     * @param array $item
     */
    protected function checkItemDetails($item)
    {
        $components = ['NAV','BC','GP','D365FO'];
        if (in_array($this->_component, $components)) {
            $this->reason = $item['reason'] ?? '';
            $this->condition = $item['condition'] ?? '';
            $this->resolution = $item['resolution'] ?? '';
            $this->itemsku = $item['sku'];
            $this->itemstatus = $item['itemStatus'] ?? 'authorized';
        } else {
            $this->reason = $item['reason'] ?? '';
            $this->condition = $item['Condition'] ?? '';
            $this->resolution = $item['Resolution'] ?? '';
            $this->itemsku = $item['Sku'];
            $this->itemstatus = $item['ItemStatus'] ?? 'authorized';
        }
        if ($this->entityCode == 'returnreceive') {
            $this->itemstatus = 'received';
        }
        if (empty($this->reason)) {
            throw new LocalizedException(__('Item reason for return is required for sku ' . $this->itemsku));
        }
    }

    /**
     * Check quantity type
     *
     * @param array $item
     * @return string
     */
    protected function checkQtyType($item)
    {
        $components = ['NAV','BC','GP','D365FO'];
        if (in_array($this->_component, $components)) {
            $this->qtyreturned = $item['quantityReturned'];
        } else {
            $this->qtyyreturned = $item['QuantityReturned'];
        }
        if ($this->entityCode == 'returnreceive' &&
            isset($this->qtyreturned) && $this->qtyreturned > 0
        ) {
            return 'approved';
        } elseif ($this->entityCode == 'returns' &&
            isset($this->qtyreturned) && $this->qtyreturned > 0
        ) {
            return 'authorized';
        } elseif ($this->entityCode == 'returns' &&
            isset($item['qtyRequested']) && $item['qtyRequested'] > 0
        ) {
            return 'requested';
        } else {
            throw new LocalizedException(__('Please specify return quantity'));
        }
    }

    /**
     * Get item entity Id
     *
     * @param int $orderItemId
     * @param string $rmaEntity
     * @return mixed
     */
    protected function getItemEntityId($orderItemId, $rmaEntity)
    {
        if (!empty($orderItemId) && !empty($rmaEntity)) {
            $itemFactory = $this->_itemsFactory->create();
            $itemFactory->addFieldToFilter('rma_entity_id', $rmaEntity)
                ->addFieldToFilter('order_item_id', $orderItemId);
            if (empty($itemFactory->getData()[0]['entity_id'])) {
                throw new LocalizedException(__('Incorrect items found for this return'));
            }
            return $itemFactory->getData()[0]['entity_id'];
        }
    }

    /**
     * Unauthorized Items
     *
     * @param array $authorized
     * @param object $rmaEntity
     */
    protected function unAuthorizedItems($authorized, $rmaEntity)
    {
        $connection = $this->_resourceConn->getConnection();
        $tableName = $this->_resourceConn->getTableName('magento_rma_item_entity');
        $sql = "update " . $tableName . " SET status='denied'
        WHERE order_item_id NOT IN (" . implode(',', $authorized) . ") AND
        rma_entity_id = " . $rmaEntity;
        $connection->query($sql);
    }

    /**
     * Cancel Return
     */
    protected function cancelReturn(): array
    {
        $status = false;
        $rmaId = 0;
        try {
            $targetReturnId = $this->dataHelper->getValueFromArray("targetReturnId", $this->stringData);
            $returnsRecord = $this->_i95devRma->create()->load($targetReturnId, 'target_return_id');
            $returnStatus = $this->dataHelper->getValueFromArray("returnStatus", $this->stringData);
            if ($returnStatus == "Cancelled" && $returnsRecord) {
                $rmaId = $returnsRecord->getReturnId();
                $rmaModel = $this->magentoRma->create()->load($rmaId);
                if ($rmaModel && $rmaModel->canClose()) {
                    $rmaModel->close()->save();
                }
                $comment = __('i95dev_rma_cancel');
                if ($rmaModel->getStatus() == "closed") {
                    $history = $this->rmaHistory->create();
                    $history->setRmaEntityId($rmaModel->getId());
                    $history->setComment($comment);
                    $history->sendCommentEmail();
                    $history->saveComment($comment, true, true);
                    $status = true;
                }
            }
        } catch (\Exception $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }

        return [
            "status" => $status,
            "id" => $rmaId

        ];
    }
}
