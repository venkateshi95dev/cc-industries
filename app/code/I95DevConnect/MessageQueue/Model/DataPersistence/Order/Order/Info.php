<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order;

use Exception;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\BillingAddress;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\Customer;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\DiscountEntity;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\OrderComments;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\OrderItems;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\Payment\PaymentInfo;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\ShippingAddress;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;

/**
 * Class for preparing order result data to be sent to ERP
 */
class Info
{
    public const SHIPPINGMETHOD = "shippingMethod";
    public const TARGETCUSTOMERID = "targetCustomerId";

    /**
     * @var OrderRepositoryInterfaceFactory
     */
    public $orderRepo;

    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var Forward\Customer
     */
    public $customerEntity;

    /**
     * @var Forward\BillingAddress
     */
    public $billingAddressEntity;

    /**
     * @var ShippingAddress
     */
    public $shippingAddressEntity;

    /**
     * @var Forward\OrderItems
     */
    public $orderItemsEntity;

    /**
     * @var PaymentInfo
     */
    public $orderPaymentEntity;

    /**
     * @var Forward\OrderComments
     */
    public $orderCommentsEntity;

    /**
     * @var Forward\DiscountEntity
     */
    public $discountEntity;

    /**
     * @var object
     */
    public $order;

    /**
     * @var int
     */
    public $orderId;

    /**
     * @var SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;

    /**
     * @var array
     */
    public $InfoData = [];

    /**
     *
     * @param OrderRepositoryInterfaceFactory $orderRepo
     * @param Manager $eventManager
     * @param Customer $customerEntity
     * @param BillingAddress $billingAddressEntity
     * @param ShippingAddress $shippingAddressEntity
     * @param OrderItems $orderItemsEntity
     * @param PaymentInfo $orderPaymentEntity
     * @param OrderComments $orderCommentsEntity
     * @param DiscountEntity $discountEntity
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct( // NOSONAR
        OrderRepositoryInterfaceFactory $orderRepo,
        Manager $eventManager,
        Forward\Customer $customerEntity,
        Forward\BillingAddress $billingAddressEntity,
        ShippingAddress $shippingAddressEntity,
        Forward\OrderItems $orderItemsEntity,
        PaymentInfo $orderPaymentEntity,
        Forward\OrderComments $orderCommentsEntity,
        Forward\DiscountEntity $discountEntity,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderRepo = $orderRepo;
        $this->eventManager = $eventManager;
        $this->customerEntity = $customerEntity;
        $this->billingAddressEntity = $billingAddressEntity;
        $this->shippingAddressEntity = $shippingAddressEntity;
        $this->orderItemsEntity = $orderItemsEntity;
        $this->orderPaymentEntity = $orderPaymentEntity;
        $this->orderCommentsEntity = $orderCommentsEntity;
        $this->discountEntity = $discountEntity;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Method to process order information to ERP
     *
     * @param int $orderId
     * @return array
     * @throws Exception
     * @throws Exception
     * @createdBy Sravani Polu
     */
    public function getInfo($orderId)
    {
        try {
            $this->orderId = $orderId;
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('increment_id', $orderId, 'eq')
                ->create();
            $orderCollection = $this->orderRepo->create()->getList($searchCriteria);
            $this->order = $orderCollection->getFirstItem();
            if (is_object($this->order)) {
                $this->InfoData['sourceId'] = $this->order->getIncrementId();
                $this->InfoData['taxAmount'] = $this->order->getBaseTaxAmount();
                $this->InfoData['shippingAmount'] = $this->order->getBaseShippingAmount();
                /** @author Sravani Polu-Added subtotal for order as to fix  shipping charges sync issue in AX build. */
                $this->InfoData['subTotal'] = (float)$this->order->getBaseSubTotal();
                $this->InfoData['reference'] = $this->order->getCustomerEmail();
                $this->InfoData['orderCreatedDate'] = $this->order->getCreatedAt();
                $this->InfoData['lastUpdatedDate'] = $this->order->getUpdatedAt();
                $customer = $this->customerEntity->getCustomerEntity($this->order);
                $billingAddress = $this->billingAddressEntity->getBillingAddress($this->order);
                $shippingAddress = $this->shippingAddressEntity->getShippingAddress($this->order);
                $orderItems = $this->orderItemsEntity->getOrderItemEntities($this->order);
                $orderPayment = $this->orderPaymentEntity->getOrderPayment($this->order);
                $orderComments = $this->orderCommentsEntity->getOrderComments($this->order->getEntityId());
                $discount = $this->discountEntity->getOrderDiscount($this->order);
                $this->InfoData['orderDocumentAmount'] = $this->order->getGrandTotal();
                $this->InfoData[self::TARGETCUSTOMERID] = $customer[self::TARGETCUSTOMERID];
                $this->InfoData['customer'] = $customer;
                if (!empty($billingAddress)) {
                    $billingAddress[self::TARGETCUSTOMERID] = $customer[self::TARGETCUSTOMERID];
                    $this->InfoData['targetBillingAddressId'] = $billingAddress['targetId'];
                }
                $this->InfoData['billingAddress'] = $billingAddress;
                if (!empty($shippingAddress)) {
                    $shippingAddress[self::TARGETCUSTOMERID] = $customer[self::TARGETCUSTOMERID];
                    $this->InfoData[self::SHIPPINGMETHOD] = $shippingAddress[self::SHIPPINGMETHOD];
                    unset($shippingAddress[self::SHIPPINGMETHOD]);
                    $this->InfoData['targetShippingAddressId'] = $shippingAddress['targetId'];
                    $this->InfoData['shippingAddress'] = $shippingAddress;
                }

                $this->InfoData['orderItems'] = $orderItems;
                $this->InfoData['payment'] = $orderPayment;
                $this->InfoData['comments'] = $orderComments;

                /** @updatedBy Debashis S. Gopal. Field name changed from discountAmount to discount. */
                $this->InfoData['discount'] = $discount;
                $this->InfoData['origin'] = "website";
                $this->InfoData['websiteId'] = $this->order->getStore()->getWebsiteId();
            }

            $orderInfoEvent = "erpconnect_forward_orderinfo";
            $this->eventManager->dispatch($orderInfoEvent, ['order' => $this]);
            return $this->InfoData;
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }
}
