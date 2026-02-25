<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model;

use DateTime;
use Ebizcharge\Ebizcharge\Api\Data\FutureSubscriptionInterface;
use Ebizcharge\Ebizcharge\Api\Data\OrderSubscriptionInterface;
use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface as RecurringRepository;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ResourceModel\FutureSubscription\CollectionFactory as FutureCollection;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\CollectionFactory as RecurringCollection;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\SalesSequence\Model\Manager as SequenceManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Create orders Model Class
 *
 * Class CreateOrder
 */
class CreateOrder
{
    /**
     * @var RecurringRepository
     */
    private RecurringRepository $recurringRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var OrderSubscriptionFactory
     */
    private OrderSubscriptionFactory $orderSubscriptionFactory;

    /**
     * @var TranApi
     */
    private TranApi $tranApi;


    /**
     * @var RecurringCollection
     */
    private RecurringCollection $recurringCollection;

    /**
     * @var FutureCollection
     */
    private FutureCollection $futureCollection;

    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $ebzichargeLogger;

    /**
     * @var ResourceModel\Recurring\Collection|null
     */
    private ?ResourceModel\Recurring\Collection $suspendRecurrings;

    /**
     * @var ResourceModel\Recurring\Collection|null
     */
    private ?ResourceModel\Recurring\Collection $failedAttempts;

    /**
     * @var ResourceModel\OrderSubscription\Collection|null
     */
    private ?ResourceModel\OrderSubscription\Collection $recurringOrders;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var AddressRepositoryInterface
     */
    private AddressRepositoryInterface $addressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @var QuoteFactory
     */
    private QuoteFactory $quote;

    /**
     * @var QuoteManagement
     */
    private QuoteManagement $quoteManagement;

    /**
     * @var string|null
     */
    private ?string $paymentInternalId;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var int
     */
    private $orders = 0;

    /**
     * @var int $failedOrders
     */
    private $failedOrders = 0;

    /**
     * @var SequenceManager
     */
    private $sequenceManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param FutureCollection $futureCollection
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderSubscriptionFactory $orderSubscriptionFactory
     * @param QuoteFactory $quote
     * @param QuoteManagement $quoteManagement
     * @param RecurringCollection $recurringCollection
     * @param RecurringRepository $recurringRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param TranApi $tranApi
     * @param ManagerInterface $messageManager
     * @param ProductRepositoryInterface $productRepository
     * @param EbizchargeLogger $ebizchargeLogger
     * @param SequenceManager $sequenceManager
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository,
        FutureCollection $futureCollection,
        OrderRepositoryInterface $orderRepository,
        OrderSubscriptionFactory $orderSubscriptionFactory,
        QuoteFactory $quote,
        QuoteManagement $quoteManagement,
        RecurringCollection $recurringCollection,
        RecurringRepository $recurringRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        TranApi $tranApi,
        ManagerInterface $messageManager,
        ProductRepositoryInterface $productRepository,
        EbizchargeLogger $ebizchargeLogger,
        SequenceManager $sequenceManager
    ) {
        /** @var  addressRepository */
        $this->addressRepository = $addressRepository;
        /** @var  customerRepository */
        $this->customerRepository = $customerRepository;
        /** @var  futureCollection */
        $this->futureCollection = $futureCollection;
        /** @var  orderSubscriptionFactory */
        $this->orderSubscriptionFactory = $orderSubscriptionFactory;
        /** @var  quote */
        $this->quote = $quote;
        /** @var  quoteManagement */
        $this->quoteManagement = $quoteManagement;
        /** @var  recurringCollection */
        $this->recurringCollection = $recurringCollection;
        /** @var  recurringRepository */
        $this->recurringRepository = $recurringRepository;
        /** @var  searchCriteriaBuilder */
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        /** @var  storeManager */
        $this->storeManager = $storeManager;
        /** @var  tranApi */
        $this->tranApi = $tranApi;
        /** @var  messageManager */
        $this->messageManager = $messageManager;
        /** @var  ebizchargeLogger */
        $this->ebzichargeLogger = $ebizchargeLogger;
        /** @var  sequenceManager */
        $this->sequenceManager = $sequenceManager;
        /** @var  suspendRecurrings */
        $this->suspendRecurrings = null;
        /** @var  recurringOrders */
        $this->recurringOrders = null;
        /** @var  orderRepository */
        $this->orderRepository = $orderRepository;
        /** @var  productRepository */
        $this->productRepository = $productRepository;
        /** @var  cartRepository */
        $this->cartRepository = $cartRepository;
    }

    /**
     * Check Recurring Orders
     *
     * @param DateTime $startDate
     * @param bool $isCron
     * @return bool
     */
    public function checkRecurringOrders(DateTime $startDate, bool $isCron = false)
    {
        $orderToCreate = $this->getOrderToCreate($startDate);

        $this->processOrders($orderToCreate);

        return $this->createSuccessMessage($isCron);
    }

    /**
     * Get Order to Create
     *
     * @param DateTime $startDate
     * @return mixed
     */
    public function getOrderToCreate(DateTime $startDate)
    {
        $collection = $this->futureCollection->create();
        $collection->getSelect()
            ->joinInner(
                ['ebizcharge_recurring'],
                'main_table.recurring_id = ebizcharge_recurring.rec_id',
                ['*']
            )->joinLeft(
                ['ebizcharge_recurring_order'],
                // phpcs:ignore
                'main_table.recurring_id = ebizcharge_recurring_order.recurring_id AND date(main_table.recurring_date) = date(ebizcharge_recurring_order.order_date) AND status = 1',
                ['rec_order_id', 'status', 'order_date']
            );
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(
                'main_table.' . FutureSubscriptionInterface::RECURRING_DATE,
                [
                    'from' => $startDate->format('Y-m-d'),
                    'to' => date('Y-m-d')
                ]
            )
            ->addFilter(
                RecurringInterface::REC_STATUS,
                0
            )
            ->addFilter(
                OrderSubscriptionInterface::REC_ORDER_ID,
                ['null' => true]
            );

        return AbstractRepository::searchList($searchCriteria->create(), $collection);
    }

    /**
     * Process Orders
     *
     * @param mixed $orderToCreate
     * @return bool
     */
    public function processOrders($orderToCreate)
    {
        foreach ($orderToCreate->getItems() as $recurring) {
            $recurringDate = $recurring->getData('recurring_date');
            try {
                if ($recurring->getData('eb_rec_remaining') == 0) {

                    /** logging Cron Orders completed */
                    $this->ebzichargeLogger->addInfo(__(
                        'Create Order: All recurring Orders are completed for ' .
                        $recurring->getData('mage_item_name') . '. Status is marked as suspended on gateway.'
                    ));

                    // Suspend subscription on Econnect //0 Active //1 Suspended //2 Expired //3 Canceled
                    $result = $this->tranApi->suspendScheduledRecurringPaymentStatus($recurring, 1);
                    $this->processSuspendRecurring($recurring->getData('rec_id'));
                    continue;
                }
                $magCustomerId = $recurring->getData('mage_cust_id');

                $this->paymentInternalId = $this->tranApi->searchRecurringPayment(
                    $magCustomerId,
                    $recurring->getData('eb_rec_scheduled_payment_internal_id'),
                    $recurringDate
                );

                if (empty($this->paymentInternalId)) {
                    $msg = __('Create Order: Payment is not paid or failed.');

                    /** logging the Cron Payment is not paid */
                    $this->ebzichargeLogger->addInfo($msg);
                    $this->insertInRecurringOrder($recurring, 0, $msg);
                    continue;
                }

                $customerData = $this->tranApi->getMagentoCustomer($magCustomerId);

                if (empty($customerData)) {
                    $msg = __('Create Order: Error in loading customer ID(' . $magCustomerId .
                        ')  against order (' . $recurring->getData('mage_order_id') . ')');

                    /** Logging to the logger */
                    $this->ebzichargeLogger->addInfo($msg);

                    $this->insertInRecurringOrder($recurring, 0, $msg);
                    continue;
                }

                $savedShippingMethod = $recurring->getData('shipping_method');

                // load existing order info
                if (!empty($recurring->getData('mage_order_id'))) {

                    /**
                     * @var $savedOrderData OrderInterface
                     */
                    $savedOrderData = $this->orderRepository->getById(
                        $recurring->getData('mage_order_id'),
                        'increment_id'
                    );

                    /**
                     * @var $savedOrderPayment OrderPaymentInterface
                     */
                    $savedOrderPayment = $savedOrderData->getPayment();

                    if (empty($savedOrderData) || empty($savedOrderPayment)) {
                        $msg = __('Create Order: Error in loading parent saved Order (' .
                            $recurring->getData('mage_order_id') . ')');

                        /** Logging the error */
                        $this->ebzichargeLogger->addError($msg);

                        $this->insertInRecurringOrder($recurring, 0, $msg);
                        continue;
                    }

                    if (empty($savedShippingMethod)) {
                        $savedShippingMethod = $savedOrderData->getShippingMethod();
                    }

                    $orderAddress = $this->getExistingOrderAddress($savedOrderData);
                    $savedBillingAddress = $orderAddress['billingAddress'];
                    $savedShippingAddress = $orderAddress['shippingAddress'];

                } else {
                    /** load admin subscription info */
                    $savedBillingAddress = $this->getRecurringBillingAddress(
                        (int)$recurring->getData('billing_address_id')
                    );
                    $savedShippingAddress = $this->getRecurringShippingAddress(
                        (int)$recurring->getData('shipping_address_id')
                    );
                    $savedOrderPayment = [];
                }

                if (empty($savedShippingAddress)) {
                    $msg = __('Create Order: Shipping address is empty or invalid.');

                    /** logging to the logger */
                    $this->ebzichargeLogger->addInfo($msg);
                    $this->insertInRecurringOrder($recurring, 0, $msg);
                    // Suspend subscription on Econnect //0 Active //1 Suspended //2 Expired //3 Canceled
                    //$this->_tran->suspendScheduledRecurringPaymentStatus($recurring, 1);
                    continue;
                }

                $item = $this->dataClass->loadMagentoItem($recurring->getData('mage_item_id'));

                if (empty($item)) {
                    $msg = __('Create Order: Product not found or has configurable type.');

                    /** Logging to the logger */
                    $this->ebzichargeLogger->addInfo($msg);

                    $this->insertInRecurringOrder($recurring, 0, $msg);
                    continue;
                }

                if ((int)$item['QtyOnHand'] < (int)$recurring->getData('qty_ordered')) {

                    $msg = __("Create Order: Product #" . $recurring->getData('mage_item_id') .
                        ' is out of stock.');

                    $this->insertInRecurringOrder($recurring, 0, $msg);
                    /** adding the logging */
                    $this->ebzichargeLogger->addInfo($msg);
                    continue;
                }

                $shipmentMethod = $this->getShipmentMethod($item['itemType'], $savedShippingMethod);

                if (empty($shipmentMethod)) {
                    /** logging shipping method */
                    $this->ebzichargeLogger->addInfo(__(
                        'Create Order: Shipping method is empty and order cannot be created.'
                    ));

                    $this->insertInRecurringOrder($recurring, 0, 'Shipping method is empty');
                    continue;
                }

                $excludeAmount = ($recurring->getData('qty_ordered') * $item['itemPrice']);

                if (!empty($recurring->getData('amount'))) {
                    $excludeAmountDb = $recurring->getData('amount');
                    if ($excludeAmount != $excludeAmountDb) {
                        $excludeAmount = $excludeAmountDb;
                    }
                }

                $orderData = [
                    'items' => [
                        'item' => [
                            'product_id' => $recurring->getData('mage_item_id'),
                            'qty' => $recurring->getData('qty_ordered'),
                            'price' => $item['itemPrice'],
                            'qtyOnHand' => $item['QtyOnHand'],
                            'itemType' => $item['itemType'],
                            'excludeAmount' => $excludeAmount
                        ]
                    ],
                    'excludeAmount' => $excludeAmount,
                    'custId' => $recurring->getData('mage_cust_id'),
                    'currency_id' => $this->storeManager->getStore()->getCurrentCurrencyCode(),
                    'email' => $customerData['email'],
                    'billing_address' => $savedBillingAddress,
                    'shipping_address' => $savedShippingAddress,
                    'discountCoupon' => $savedOrderData->getCouponCode() ?? ''
                ];

                $dateToday = date("Y-m-d");
                $recurringDate = date("Y-m-d", strtotime($recurring->getData('recurring_date')));

                /** Cron occurence logging */
                $this->ebzichargeLogger->addInfo(__('Cron: Today = ' . $dateToday . ', Recurring due date = ' .
                    $recurringDate));

                if (strtotime($recurringDate) <= strtotime($dateToday)) {
                    if ((int)$item['QtyOnHand'] > (int)$recurring->getData('qty_ordered') || in_array(
                            $item['itemType'],
                            ['downloadable', 'virtual']
                        )) {
                        $this->createMageOrderRecurring(
                            $recurring,
                            $orderData,
                            $savedOrderPayment,
                            $customerData,
                            $shipmentMethod
                        );
                    }
                } else {
                    $this->ebzichargeLogger->addInfo(__(
                        'No recurring order is due today for recurring product #' .
                        $recurring->getData('mage_item_id')
                    ));
                }
            } catch (Exception $ex) {

                $msg = __('Payment is not paid or failed.');
                $this->ebzichargeLogger->addError($ex->getMessage());
                $this->insertInRecurringOrder($recurring, 0, 'Payment is not paid or failed.');
                //return $this->createErrorResponse('Failed: ' . $e->getMessage());
            }
        }
        try {
            $this->saveRecurrings();
            $this->saveRecurringOrders();

            $this->ebzichargeLogger->addInfo(__("Saving recurring orders"));

        } catch (Exception $e) {
            /** logging to the logger */
            $this->ebzichargeLogger->addError(__('Error occured during creating order: ' . $e->getMessage()));
            return false;
        }
        return true;
    }

    /**
     * Process Suspend Recurring
     *
     * @param mixed $recId
     * @return ResourceModel\Recurring\Collection|DataObject|void|null
     */
    public function processSuspendRecurring($recId = false)
    {
        if ($recId) {
            $this->suspendRecurrings = $this->suspendRecurrings ?? $this->recurringCollection->create();
            return $this->suspendRecurrings->getItemById((int)$recId)->setData(RecurringInterface::REC_STATUS, 1);
        } elseif ($this->suspendRecurrings !== null) {
            return $this->suspendRecurrings->save();
        }
    }

    /**
     * Insert In Recurring Order
     *
     * @param mixed $recurring
     * @param mixed $status
     * @param mixed $message
     * @param null|mixed $orderId
     * @param null|mixed $entityId
     * @return bool
     */
    public function insertInRecurringOrder($recurring, $status, $message, $orderId = null, $entityId = null)
    {
        return $this->setDataToCollection($recurring, $status, $message, $orderId, $entityId);
    }

    /**
     * Set Data Collection
     *
     * @param mixed $recurring
     * @param mixed $status
     * @param mixed $message
     * @param null|mixed $orderId
     * @param null|mixed $entityId
     * @return bool
     */
    public function setDataToCollection($recurring, $status, $message, $orderId = null, $entityId = null)
    {
        $orderDate = $recurring->getData('recurring_date') ?? date('Y-m-d H:i:s');
        $orderId = $orderId == null ? $orderId : (int)$orderId;
        $entityId = $entityId == null ? $entityId : (int)$entityId;
        try {
            $dataObject = $this->orderSubscriptionFactory->create()
                ->setData(
                    [
                        'id' => null,
                        'recurring_id' => (int)$recurring->getData('rec_id'),
                        'rec_order_id' => $orderId,
                        'created_date' => date('Y-m-d H:i:s'),
                        'order_date' => date('Y-m-d', strtotime($orderDate)),
                        'status' => $status,
                        'message' => $message,
                        'order_entity_id' => $entityId
                    ]
                );

            $dataObject->save();
            return true;
        } catch (Exception $e) {

            /** logging to the logger exception */
            $this->ebzichargeLogger->addError(__('Function name is: ' . __FUNCTION__ .
                ': Log by Create order cron: Exception6:' . $e->getMessage()));
            return false;
        }
//        @todo 'commented due to Error in bulk saving records: Item (Ebizcharge\Ebizcharge\Model\OrderSubscription)
// with the same ID "1" already exists'
//        $dataObject->isObjectNew(true);
//        return $dataObject;
    }

    /**
     * Get Existing Order Address
     *
     * @param OrderInterface $order
     * @return array[]
     */
    public function getExistingOrderAddress(OrderInterface $order): array
    {
        $orderBilling = $order->getBillingAddress();
        $orderShipping = $order->getShippingAddress();

        return [
            'billingAddress' => $this->address($orderBilling),
            'shippingAddress' => $this->address($orderShipping)
        ];
    }

    /**
     * Address
     *
     * @param mixed $address
     * @return array
     */
    public function address($address): array
    {
        if (empty($address)) {
            return [];
        }
        $region = $address->getRegion();

        if (!empty($address->getRegion()) && is_object($address->getRegion())) {
            $region = $address->getRegion()->getRegion();
        }

        return [
            'firstname' => $address->getFirstName(),
            'lastname' => $address->getLastName(),
            'company' => $address->getCompany(),
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'country_id' => $address->getCountryId(),
            'region' => $region,
            'region_id' => $address->getRegionId(),
            'postcode' => $address->getPostcode(),
            'telephone' => $address->getTelephone(),
            'save_in_address_book' => 0,
        ];
    }

    /**
     * Get Recurring Billing Address
     *
     * @param int $billingAddressId
     * @return array
     */
    public function getRecurringBillingAddress(int $billingAddressId)
    {
        try {
            $billing = $this->addressRepository->getById($billingAddressId);
            if (!empty($billing)) {
                return $this->address($billing);
            }
        } catch (Exception $e) {

            /** logggin exception */
            $this->ebzichargeLogger->addError('Exception occured : ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Get Recurring Shipping Address
     *
     * @param int $shippingAddressId
     * @return array
     */
    public function getRecurringShippingAddress(int $shippingAddressId)
    {
        try {
            $shipping = $this->addressRepository->getById($shippingAddressId);
            if (!empty($shipping)) {
                return $this->address($shipping);
            }
        } catch (Exception $e) {
            /** logggin exception */
            $this->ebzichargeLogger->addError('Exception occured : ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Get Shipment Method
     *
     * @param mixed $productType
     * @param mixed $orderShippingMethod
     * @return false|mixed|string
     */
    public function getShipmentMethod($productType, $orderShippingMethod)
    {
        $shippingMethod = (!empty($orderShippingMethod))
            ? $orderShippingMethod
            : $this->dataClass->getShippingMethods(); //

        if (in_array($productType, ['virtual', 'downloadable'])) {
            $shippingMethod = 'freeshipping_freeshipping';
        }

        return $shippingMethod;
    }

    /**
     * Create Mage Order Recurring
     *
     * @param mixed $recurring
     * @param mixed $orderData
     * @param OrderPaymentInterface|array|null $savedOrderPayment
     * @param mixed $customerData
     * @param mixed $shipmentMethod
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createMageOrderRecurring(
        $recurring,
        $orderData,
        $savedOrderPayment,
        $customerData,
        $shipmentMethod
    ) {

        $this->ebzichargeLogger->addInfo("Cron: Method called ." . __METHOD__);

        // Select 1st active shipping method
        $order = $this->createOrderQuote($recurring, $orderData, $savedOrderPayment, $customerData, $shipmentMethod);

        $order->setEmailSent(0);

        if ($order->getEntityId()) {
            $this->orders++;
            /** logging to the cron */
            $this->ebzichargeLogger->addInfo(__("Cron: New recurring order #" . $order->getIncrementId() .
                " created in magento."));
            // Updating db recurring table start
            $this->updateRecurringTable($recurring);
            // mark this recurring payment as applied
            if (!empty($this->paymentInternalId)) {
                $this->tranApi->markRecurringPaymentAsApplied($this->paymentInternalId);
            }
            // add new order recurring order
            $this->insertInRecurringOrder(
                $recurring,
                1,
                'Order Added.',
                $order->getIncrementId(),
                $order->getEntityId()
            );

            /** logging to the logger */
            $this->ebzichargeLogger->addInfo(__('Cron: End: Order saved successfully.'));
        } else {
            $this->failedOrders++;
            $this->insertInRecurringOrder($recurring, 0, 'Order not saved!');

            /** logging cron logs */
            $this->ebzichargeLogger->addInfo(__("Cron: Order is not saved to the database"));
        }
    }

    /**
     * Create Order Quote
     *
     * @param mixed $recurring
     * @param mixed $orderData
     * @param OrderPaymentInterface|array|null $savedOrderPayment
     * @param mixed $customerData
     * @param mixed $shipmentMethod
     * @return AbstractExtensibleModel|OrderInterface|object|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createOrderQuote(
        $recurring,
        $orderData,
        $savedOrderPayment,
        $customerData,
        $shipmentMethod
    ) {
        $paymentMethodName = (is_object($savedOrderPayment->getMethod()))
            ? $savedOrderPayment->getMethod()
            : $this->dataClass->getPaymentMethods(); // Select Magento 1st active payment method

        $paymentObject = $this->getPaymentInfo(
            $recurring,
            $savedOrderPayment,
            $customerData,
            $paymentMethodName,
            $orderData['excludeAmount']
        );
        if (is_object($savedOrderPayment)) {
            $storeId = $savedOrderPayment->getOrder()->getStoreId();
        } else {
            $storeId = $customerData['store_id'];
        }
        $quote = $this->quote->create(); //Create object of quote
        $quote->setStoreId($storeId); // set store for which you create quote

        // if you have already buyer id then you can load customer directly
        $customer = $this->customerRepository->getById($customerData['entity_id']);
        $quote->setCurrency();
        $quote->assignCustomer($customer); //Assign quote to customer

        if ($maxReserveId = $this->getMaxReserveId((int)$storeId)) {
            $quote->setReservedOrderId($maxReserveId);
        }

        //add items in quote
        foreach ($orderData['items'] as $item) {
            $product = $this->productRepository->getById($item['product_id']);
            $product->setPrice($item['price']);
            // phpcs:ignore
            $quote->addProduct($product, intval($item['qty']));
        }

        //Set Address to quote
        $quote->getBillingAddress()->addData($orderData['billing_address']);
        $quote->getShippingAddress()->addData($orderData['shipping_address']);

        // Collect Rates and Set Shipping & Payment Method
        $quote->getShippingAddress()
            ->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($shipmentMethod); //shipping method

        $quote->setPaymentMethod($paymentMethodName); //payment method
        $quote->setInventoryProcessed(true); // update inventory

        $this->cartRepository->save($quote);

        // Set Sales Order Payment
        $quote->getPayment()->importData($paymentObject);
        // Collect Totals & Save Quote
        $quote->collectTotals();
        $this->cartRepository->save($quote);

        // Create Order From Quote
        return $this->quoteManagement->submit($quote);
    }

    /**
     * Get Payment Info
     *
     * @param mixed $recurring
     * @param mixed $savedOrderPayment
     * @param mixed $customerData
     * @param mixed $paymentMethodName
     * @param mixed $excludeAmount
     * @return array
     */
    public function getPaymentInfo(
        $recurring,
        $savedOrderPayment,
        $customerData,
        $paymentMethodName,
        $excludeAmount
    ) {
        $paymentMethodId = !empty($recurring['eb_rec_method_id']) ? $recurring['eb_rec_method_id'] :
            $savedOrderPayment->getEbzcMethodId();

        $customerEntityId = $customerData['entity_id'];
        $additionalData = [
            'method' => $paymentMethodName,
            'ebzc_option' => 'recurring',
            'ebzc_option_new' => 'recurring',
            'ebzc_option_existing' => $savedOrderPayment->getEbzcOption() ?? '',
            'ebzc_cust_id' => $customerData['ec_cust_token'],
            'ebzc_method_id' => $paymentMethodId,
            'ebzc_avs_street' => $savedOrderPayment->getEbzcAvsStreet() ?? '',
            'ebzc_avs_zip' => $savedOrderPayment->getEbzcAvsZip() ?? '',
            'ebzc_save_payment' => false,
            'mage_cust_id' => $customerData['entity_id'],
            'excludeAmount' => $excludeAmount
        ];

        if ($paymentMethodName == 'ebizcharge_ebizcharge') {
            $paymentObject = [
                'method' => $paymentMethodName,
                'so_number' => $recurring['mage_order_id'],
                'po_number' => $recurring['mage_order_id'],
                'mage_cust_id' => $customerEntityId,
                'additional_data' => $additionalData
            ];
        } else {
            $paymentObject = [
                'method' => $paymentMethodName,
                'so_number' => $recurring['mage_order_id'],
                'po_number' => $recurring['mage_order_id'],
                'mage_cust_id' => $customerEntityId
            ];
        }

        return $paymentObject;
    }

    /**
     * Get max reserved order id
     *
     * @param int $storeId
     * @return int|false
     */
    public function getMaxReserveId(int $storeId)
    {
        $defaultAdminStoreId = 0;
        try {
            $defaultAdminStoreMaxId = $this->sequenceManager->getSequence(
                Order::ENTITY,
                $defaultAdminStoreId
            )->getNextValue();
            $currentStoreMaxId = $this->sequenceManager->getSequence(
                Order::ENTITY,
                $storeId
            )->getNextValue();

            return max($currentStoreMaxId, $defaultAdminStoreMaxId);
        } catch (Exception $e) {

            /** logging Error to the logger */
            $this->ebzichargeLogger->addError(__("Exception occured " . $e->getMessage()));
            return false;
        }
    }

    /**
     * Update Recurring Table
     *
     * @param mixed $recurring
     * @return RecurringInterface|false|null
     */
    public function updateRecurringTable($recurring)
    {
        $recProcessedNew = ((int)$recurring['eb_rec_processed'] + 1);
        $recRemainingNew = ((int)$recurring['eb_rec_remaining'] - 1);
        // phpcs:ignore
        $recNextDueDateArray = unserialize($recurring['eb_rec_due_dates']);

        $updateRecord = $this->recurringRepository->getById($recurring['rec_id']);
        $updateRecord = $updateRecord->setEbRecProcessed($recProcessedNew)
            ->setEbRecNext($recNextDueDateArray[$recProcessedNew] ?? '')
            ->setEbRecRemaining($recRemainingNew);

        return $this->recurringRepository->save($updateRecord);
    }

    /**
     * Save Recurring
     *
     * @return bool
     */
    public function saveRecurrings()
    {
        $upatedRecurrings = $this->suspendRecurrings !== null ? $this->suspendRecurrings : $this->failedAttempts;
        if ($upatedRecurrings !== null) {
            try {
                $upatedRecurrings->save();
                return true;
            } catch (Exception $e) {
                /** logging the exception */
                $this->ebzichargeLogger->addError(__("Exception occured: " . $e->getMessage()));
            }
        }
        return false;
    }

    /**
     * Save Recurring Orders
     *
     * @return bool
     */
    public function saveRecurringOrders(): bool
    {
        if ($this->recurringOrders !== null) {
            try {
                $this->recurringOrders->save();
                return true;
            } catch (Exception $e) {
                /** logging to the logger */
                $this->ebzichargeLogger->addError(__("Exception occured " . $e->getMessage()));

            }
        }
        return false;
    }

    /**
     * if the Subscription fails on the Third time it is put on Suspension.
     *
     * @param mixed $recurring
     * @return bool
     */
//    private function addFailedAttempts($recurring)
//    {
//        $failedAttempts = (int)$recurring->getData('failed_attempts') + 1;
//        $recurringRecord = $this->recurringRepository->getById((int)$recurring->getData('rec_id'));
//        $recurringRecord->setFailedAttempts($failedAttempts);
//        $this->recurringRepository->save($recurringRecord);
//
//        if ($failedAttempts > 2) {
//            $this->tranApi->cronlog('This is the 3rd failed attempt of recurring record# ' .
// $recurring->getData('rec_id') . ' Suspending subscription.');
//            $result = $this->tranApi->suspendScheduledRecurringPaymentStatus($recurring, 1);
//        }
//        return true;
//    }

    /**
     * Creates a success message, and passes it to the "Manage
     *
     * @param bool $cron
     * @return bool
     */
    public function createSuccessMessage($cron = false): bool
    {
        $message = 'No new order created';
        $numberOfOrders = $this->orders;
        $failedOrders = $this->failedOrders;
        if ($numberOfOrders > 0) {
            $message = $numberOfOrders . ' Order(s) are created successfully';
        }
        if ($failedOrders > 0) {
            $message .= " and $failedOrders order(s) are failed.";
        }
        if (!$cron) {
            $this->messageManager->addSuccessMessage($message);
        }
        /** adding order in cron messages */
        $this->ebzichargeLogger->addInfo(__('Order create cron message: ' . $message));

        return true;
    }

    /**
     * Process Failed Attempts
     *
     * @param mixed $recurring
     * @return ResourceModel\Recurring\Collection|DataObject|void|null
     */
    public function processFailedAttempts($recurring = false)
    {
        $recId = $recurring ? (int)$recurring->getData('rec_id') : false;
        if ($recId) {
            $failedCount = (int)$recurring->getData('failed_attempts') + 1;
            $this->failedAttempts = $this->suspendRecurrings !== null ? $this->suspendRecurrings :
                $this->recurringCollection->create();
            if ($failedCount > 2) {
                $this->tranApi->cronlog('This is the 3rd failed attempt of recurring record# ' .
                    $recurring->getData('rec_id') . ' Suspending subscription.');
                $result = $this->tranApi->suspendScheduledRecurringPaymentStatus($recurring, 1);
            }
            /** logging the failing count */
            $this->ebzichargeLogger->addInfo(__('Failed Attemps: ' . $failedCount));

            return $this->failedAttempts->getItemById($recId)->setData(
                RecurringInterface::FAILED_ATTEMPTS,
                $failedCount
            );
        } elseif ($this->failedAttempts !== null) {
            /** logging the save failing count */
            $this->ebzichargeLogger->addInfo(__('Failed Attemps Saved '));

            return $this->failedAttempts->save();
        }
    }
}