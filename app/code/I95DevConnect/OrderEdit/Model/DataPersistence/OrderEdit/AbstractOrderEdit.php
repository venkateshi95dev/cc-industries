<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_OrderEdit
 */
namespace I95DevConnect\OrderEdit\Model\DataPersistence\OrderEdit;

use \Magento\Framework\Stdlib\DateTime\DateTime;
use \I95DevConnect\MessageQueue\Api\LoggerInterface;
use \I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Create;
use \I95DevConnect\MessageQueue\Model\AbstractDataPersistence;

/**
 * Order edit abstract class
 */
class AbstractOrderEdit extends AbstractDataPersistence
{

    /**
     *
     * @var \I95DevConnect\OrderEdit\Model\DataPersistence\Validate
     */
    public $editOrderValidator;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    public $orderManagement;

    /**
     *
     * @var \Magento\Sales\Model\Order\AddressFactory
     */
    public $orderAddressFactory;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     */
    public $addressRenderer;

    /**
     *
     * @var \I95DevConnect\MessageQueue\Helper\Data
     */
    public $dataHelper;

    /**
     * @var \I95DevConnect\OrderEdit\Helper\Data
     */
    public $editOrderHelper;

    /**
     * @var \Magento\Sales\Model\AdminOrder\EmailSender
     */
    public $emailSender;

    /**
     * @var Create
     */
    public $orderCreate;

    /**
     * @var \Magento\Sales\Api\Data\OrderAddressInterface
     */
    public $orderAddressData;

    /**
     * @var \Magento\Sales\Api\OrderAddressRepositoryInterface
     */
    public $orderAddressRepository;

    /**
     * @var \Magento\Sales\Api\Data\InvoiceCommentInterfaceFactory
     */
    public $invoiceCommentDataFactory;

    /**
     * @var \Magento\Sales\Api\InvoiceCommentRepositoryInterface
     */
    public $invoiceCommentRepository;

    /**
     * @var \Magento\Sales\Api\Data\ShipmentCommentInterfaceFactory
     */
    public $shipmentCommentDataFactory;

    /**
     * @var \Magento\Sales\Api\ShipmentCommentRepositoryInterface
     */
    public $shipmentCommentRepository;

    /**
     *
     * @var \Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory
     */
    public $statusHistoryFactory;

    /**
     * @var \I95DevConnect\MessageQueue\Helper\Generic
     */
    public $mqGenericHepler;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    public $addressRepo;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * AbstractOrderEdit constructor.
     * @param \Magento\Framework\Json\Decoder $jsonDecoder
     * @param \I95DevConnect\MessageQueue\Api\I95DevResponseInterfaceFactory $i95DevResponse
     * @param \I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory $messageErrorModel
     * @param \I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterfaceFactory $i95DevErpMQ
     * @param \I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory $logger
     * @param \I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository
     * @param DateTime $date
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \I95DevConnect\MessageQueue\Model\DataPersistence\Validate $validate
     * @param \I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository
     * @param \I95DevConnect\OrderEdit\Model\DataPersistence\Validate $editOrderValidator
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     * @param \Magento\Sales\Model\Order\AddressFactory $orderAddressFactory
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \I95DevConnect\MessageQueue\Helper\Data $dataHelper
     * @param \I95DevConnect\OrderEdit\Helper\Data $editOrderHelper
     * @param Create $orderCreate
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $orderAddressData
     * @param \Magento\Sales\Api\OrderAddressRepositoryInterface $orderAddressRepository
     * @param \Magento\Sales\Api\Data\InvoiceCommentInterfaceFactory $invoiceCommentDataFactory
     * @param \Magento\Sales\Api\InvoiceCommentRepositoryInterface $invoiceCommentRepository
     * @param \Magento\Sales\Api\Data\ShipmentCommentInterfaceFactory $shipmentCommentDataFactory
     * @param \Magento\Sales\Api\ShipmentCommentRepositoryInterface $shipmentCommentRepository
     * @param \Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory $statusHistoryFactory
     * @param \I95DevConnect\MessageQueue\Helper\Generic $mqGenericHepler
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepo
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct( // NOSONAR
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \I95DevConnect\MessageQueue\Api\I95DevResponseInterfaceFactory $i95DevResponse,
        \I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory $messageErrorModel,
        \I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterfaceFactory $i95DevErpMQ,
        \I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory $logger,
        \I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository,
        DateTime $date,
        \Magento\Framework\Event\Manager $eventManager,
        \I95DevConnect\MessageQueue\Model\DataPersistence\Validate $validate,
        \I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository,
        \I95DevConnect\OrderEdit\Model\DataPersistence\Validate $editOrderValidator,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Model\Order\AddressFactory $orderAddressFactory,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \I95DevConnect\MessageQueue\Helper\Data $dataHelper,
        \I95DevConnect\OrderEdit\Helper\Data $editOrderHelper,
        Create $orderCreate,
        \Magento\Sales\Api\Data\OrderAddressInterface $orderAddressData,
        \Magento\Sales\Api\OrderAddressRepositoryInterface $orderAddressRepository,
        \Magento\Sales\Api\Data\InvoiceCommentInterfaceFactory $invoiceCommentDataFactory,
        \Magento\Sales\Api\InvoiceCommentRepositoryInterface $invoiceCommentRepository,
        \Magento\Sales\Api\Data\ShipmentCommentInterfaceFactory $shipmentCommentDataFactory,
        \Magento\Sales\Api\ShipmentCommentRepositoryInterface $shipmentCommentRepository,
        \Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory $statusHistoryFactory,
        \I95DevConnect\MessageQueue\Helper\Generic $mqGenericHepler,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepo,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->editOrderValidator = $editOrderValidator;
        $this->orderManagement = $orderManagement;
        $this->orderAddressFactory = $orderAddressFactory;
        $this->addressRenderer = $addressRenderer;
        $this->dataHelper = $dataHelper;
        $this->editOrderHelper = $editOrderHelper;
        $this->orderCreate = $orderCreate;
        $this->orderAddressData = $orderAddressData;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->invoiceCommentDataFactory = $invoiceCommentDataFactory;
        $this->invoiceCommentRepository = $invoiceCommentRepository;
        $this->shipmentCommentDataFactory = $shipmentCommentDataFactory;
        $this->shipmentCommentRepository = $shipmentCommentRepository;
        $this->statusHistoryFactory = $statusHistoryFactory;
        $this->mqGenericHepler = $mqGenericHepler;
        $this->addressRepo = $addressRepo;
        $this->storeManager = $storeManager;

        parent::__construct(
            $jsonDecoder,
            $i95DevResponse,
            $messageErrorModel,
            $i95DevErpMQ,
            $logger,
            $i95DevErpMQRepository,
            $date,
            $eventManager,
            $validate,
            $i95DevERPDataRepository
        );
    }

    /**
     * Update order comment
     *
     * @param string $comment
     * @param int $orderId
     * @param bool $status
     * @param int $is_customer_notified
     * @param int $is_visible_on_front
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateOrderComments(
        $comment,
        $orderId,
        $status,
        $is_customer_notified = 0,
        $is_visible_on_front = 0
    ) {
        try {
            $statusHistory = $this->statusHistoryFactory->create();
            $statusHistory->setIsCustomerNotified($is_customer_notified)
                    ->setParentId($orderId)
                    ->setStatus($status)
                    ->setComment($comment)
                    ->setIsVisibleOnFront($is_visible_on_front);
            $this->orderManagement->addComment($orderId, $statusHistory);
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
            $this->logger->create()->createLog(
                __METHOD__,
                $message,
                LoggerInterface::I95EXC,
                'critical'
            );
        }
    }
}
