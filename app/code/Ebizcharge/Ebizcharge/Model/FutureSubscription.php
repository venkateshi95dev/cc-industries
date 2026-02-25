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

use Ebizcharge\Ebizcharge\Api\Data\FutureSubscriptionInterface;
use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ResourceModel\FutureSubscription\Collection as FutureCollection;
use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Ebizcharge Future Model
 *
 * Class Recurring
 */
class FutureSubscription extends AbstractModel implements FutureSubscriptionInterface, IdentityInterface
{

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $_timezoneInterface;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productFactory;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;

    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $_recurringFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $_searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManagerInterface;
    /**
     * @var TranApiFactory
     */
    protected TranApiFactory $_soapApiFactory;
    /**
     * @var FutureCollection
     */
    protected FutureCollection $futureCollection;

    /**
     * @var array
     */
    public static array $futureUpcomingStatuses = [
        FutureSubscriptionInterface::EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_PENDING => FutureSubscriptionInterface::EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_PENDING_TITLE,
        FutureSubscriptionInterface::EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_COMPLETED => FutureSubscriptionInterface::EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_COMPLETED_TITLE,
        FutureSubscriptionInterface::EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_FAILED => FutureSubscriptionInterface::EBIZCHARGE_FUTURE_SUBSCRIPTION_STATUS_FAILED_TITLE
    ];

    /**
     * @param Context $context
     * @param Registry $registry
     * @param EbizchargeLogger $ebizchargeLogger
     * @param ProductFactory $productFactory
     * @param CustomerFactory $customerFactory
     * @param OrderFactory $orderFactory
     * @param RecurringFactory $recurringFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManagerInterface
     * @param TranApiFactory $soapApiFactory
     * @param TimezoneInterface $timezoneInterface
     * @param FutureCollection $futureCollection
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context               $context,
        Registry              $registry,
        EbizchargeLogger      $ebizchargeLogger,
        ProductFactory        $productFactory,
        CustomerFactory       $customerFactory,
        OrderFactory          $orderFactory,
        RecurringFactory      $recurringFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManagerInterface,
        TranApiFactory        $soapApiFactory,
        TimezoneInterface     $timezoneInterface,
        FutureCollection      $futureCollection,
        AbstractResource      $resource = null,
        AbstractDb            $resourceCollection = null,
        array                 $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _timezoneInterface */
        $this->_timezoneInterface = $timezoneInterface;
        /** @var _productFactory */
        $this->_productFactory = $productFactory;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _orderFactory */
        $this->_orderFactory = $orderFactory;
        /** @var _recurringFactory */
        $this->_recurringFactory = $recurringFactory;
        /** @var _soapApiFactory */
        $this->_soapApiFactory = $soapApiFactory;
        /** @var _searchCriteriaBuilder */
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        /** @var  _storeManagerInterface */
        $this->_storeManagerInterface = $storeManagerInterface;
        /** @var  futureCollection */
        $this->futureCollection = $futureCollection;
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [
            self::FUTURE_SUBSCRIPTION_CACHE_TAG . '_' . $this->getEntityId()
        ];
    }



    /**
     * Get Entity Id
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(FutureSubscriptionInterface::ENTITY_ID);
    }

    /**
     * Set Entity Id
     *
     * @param int $entityId
     * @return FutureSubscriptionInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(FutureSubscriptionInterface::ENTITY_ID, $entityId);
    }

    /**
     * Set Recurring Id
     *
     * @param int $recurring_id
     * @return FutureSubscriptionInterface
     */
    public function setRecurringId(int $recurring_id): FutureSubscriptionInterface
    {
        return $this->setData(FutureSubscriptionInterface::RECURRING_ID, $recurring_id);
    }

    /**
     * Get Recurring Id
     *
     * @return int
     */
    public function getRecurringId(): int
    {
        return (int)$this->getData(FutureSubscriptionInterface::RECURRING_ID);
    }

    /**
     * Set Recurring Date
     *
     * @param mixed $recurring_date
     * @return FutureSubscriptionInterface
     */
    public function setRecurringDate($recurring_date): FutureSubscriptionInterface
    {
        return $this->setData(FutureSubscriptionInterface::RECURRING_DATE, $recurring_date);
    }

    /**
     * Get Recurring Date
     *
     * @return string
     */
    public function getRecurringDate(): string
    {
        return $this->getData(FutureSubscriptionInterface::RECURRING_DATE);
    }

    /**
     * Get Cusomter Id
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return $this->getData(FutureSubscriptionInterface::CUSTOMER_ID);
    }

    /**
     * Set Customer Id
     *
     * @param int $customerId
     * @return FutureSubscriptionInterface
     */
    public function setCustomerId(int $customerId): FutureSubscriptionInterface
    {
        return $this->setData(FutureSubscriptionInterface::CUSTOMER_ID, $customerId);
    }

    /**
     * Get Store Id
     *
     * @return int
     */
    public function getStoreId(): int
    {
        return $this->getData(FutureSubscriptionInterface::STORE_ID);
    }

    /**
     * Set Store Id
     *
     * @param int $storeId
     * @return FutureSubscriptionInterface
     */
    public function setStoreId(int $storeId): FutureSubscriptionInterface
    {
        return $this->setData(FutureSubscriptionInterface::STORE_ID, $storeId);
    }

    /**
     * Get Ordered Quantity
     *
     * @return float
     */
    public function getOrderedQty(): float
    {
        return $this->getData(FutureSubscriptionInterface::ORDERED_QTY);
    }

    /**
     * Set Ordered Quantity
     *
     * @param float $orderedQty
     * @return FutureSubscriptionInterface
     */
    public function setOrderedQty(float $orderedQty): FutureSubscriptionInterface
    {
        return $this->setData(FutureSubscriptionInterface::ORDERED_QTY, $orderedQty);
    }

    /**
     * Get Ordered Product Id
     *
     * @return int
     */
    public function getOrderedProductId(): int
    {
        return $this->getData(FutureSubscriptionInterface::ORDERED_PRODUCT_ID);
    }

    /**
     * Set Ordered Product Id
     *
     * @param int $orderedProductId
     * @return FutureSubscriptionInterface
     */
    public function setOrderedProductId(int $orderedProductId): FutureSubscriptionInterface
    {
        return $this->setData(FutureSubscriptionInterface::ORDERED_PRODUCT_ID, $orderedProductId);
    }

    /**
     * Get Ordered Product Final Price
     *
     * @return float
     */
    public function getOrderedProducFinalPrice(): float
    {
        return $this->getData(FutureSubscriptionInterface::ORDERED_PRODUCT_FINAL_PRICE);
    }

    /**
     * Set Ordered Product Final Price
     *
     * @param int $orderedProductFinalPrice
     * @return FutureSubscriptionInterface
     */
    public function setOrderedProducFinalPrice(int $orderedProductFinalPrice): FutureSubscriptionInterface
    {
        return $this->setData(FutureSubscriptionInterface::ORDERED_PRODUCT_FINAL_PRICE, $orderedProductFinalPrice);
    }

    /**
     * Get Coupon Code
     *
     * @return string
     */
    public function getCouponCode(): string
    {
        return $this->getData(FutureSubscriptionInterface::COUPON_CODE);
    }

    /**
     * Set Coupon Code
     *
     * @param string $couponCode
     * @return FutureSubscriptionInterface
     */
    public function setCouponCode(string $couponCode): FutureSubscriptionInterface
    {
        return $this->setData(FutureSubscriptionInterface::COUPON_CODE, $couponCode);
    }

    /**
     * Get Discount
     *
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->getData(FutureSubscriptionInterface::DISCOUNT);
    }

    /**
     * Set Discount
     *
     * @param float $discount
     * @return FutureSubscriptionInterface
     */
    public function setDiscount(float $discount): FutureSubscriptionInterface
    {
        return $this->setData(FutureSubscriptionInterface::DISCOUNT, $discount);
    }

    /**
     * Get Ordered Status
     *
     * @return string
     */
    public function getOrderedStatus(): string
    {
        return $this->getData(FutureSubscriptionInterface::ORDERED_STATUS);
    }

    /**
     * Set Order Status
     *
     * @param string $orderStatus
     * @return FutureSubscriptionInterface
     */
    public function setOrderedStatus(string $orderStatus): FutureSubscriptionInterface
    {
        return $this->setData(FutureSubscriptionInterface::ORDERED_STATUS, $orderStatus);
    }

    /**
     * Get Remarks
     *
     * @return string
     */
    public function getRemarks(): string
    {
        return $this->getData(FutureSubscriptionInterface::REMARKS);
    }

    /**
     * Set Remarks
     *
     * @param string $remarks
     * @return FutureSubscriptionInterface
     */
    public function setRemarks(string $remarks): FutureSubscriptionInterface
    {
        return $this->setData(FutureSubscriptionInterface::REMARKS, $remarks);
    }

    /**
     * Add Future Subscriptions
     *
     * @param array $futureSubscriptionsParams
     * @return array
     */
    public function addFutureSubscriptions(array $futureSubscriptionsParams = []): array
    {
        /** @var  $futureResponse */
        $futureResponse = [
            'error' => true,
            'message' => __('Error occurred during adding future subscriptions.'),
            'response' => [
                'future_subscription_id' => 0
            ]
        ];
        try {

            /** @var  $futureSubscriptionId */
            $futureSubscriptionId = $futureSubscriptionsParams['future_subscription_id'] ?? 0;

            /** @var  $futureSubscriptionsParams */
            $futureSubscriptionsParams = [
                FutureSubscriptionInterface::RECURRING_ID => $futureSubscriptionsParams[FutureSubscriptionInterface::RECURRING_ID] ?? '',
                FutureSubscriptionInterface::RECURRING_DATE => $futureSubscriptionsParams[FutureSubscriptionInterface::RECURRING_DATE] ?? '',
                FutureSubscriptionInterface::CUSTOMER_ID => $futureSubscriptionsParams[FutureSubscriptionInterface::CUSTOMER_ID] ?? 0,
                FutureSubscriptionInterface::STORE_ID => $futureSubscriptionsParams[FutureSubscriptionInterface::STORE_ID] ?? 1,
                FutureSubscriptionInterface::ORDERED_QTY => $futureSubscriptionsParams[FutureSubscriptionInterface::ORDERED_QTY] ?? 0,
                FutureSubscriptionInterface::ORDERED_PRODUCT_ID => $futureSubscriptionsParams[FutureSubscriptionInterface::ORDERED_PRODUCT_ID] ?? 0,
                FutureSubscriptionInterface::ORDERED_PRODUCT_FINAL_PRICE => $futureSubscriptionsParams[FutureSubscriptionInterface::ORDERED_PRODUCT_FINAL_PRICE] ?? 0,
                FutureSubscriptionInterface::AMOUNT => $futureSubscriptionsParams[FutureSubscriptionInterface::AMOUNT] ?? 0,
                FutureSubscriptionInterface::SHIPPING_AMOUNT => $futureSubscriptionsParams[FutureSubscriptionInterface::SHIPPING_AMOUNT] ?? 0,
                FutureSubscriptionInterface::TAX_AMOUNT => $futureSubscriptionsParams[FutureSubscriptionInterface::TAX_AMOUNT] ?? 0,
                FutureSubscriptionInterface::ITEM_PRICE => $futureSubscriptionsParams[FutureSubscriptionInterface::ITEM_PRICE] ?? 0,
                FutureSubscriptionInterface::SURCHARGE_AMOUNT => $futureSubscriptionsParams[FutureSubscriptionInterface::SURCHARGE_AMOUNT] ?? 0,
                FutureSubscriptionInterface::SUBTOTAL => $futureSubscriptionsParams[FutureSubscriptionInterface::SUBTOTAL] ?? 0,
                FutureSubscriptionInterface::GRAND_TOTAL => $futureSubscriptionsParams[FutureSubscriptionInterface::GRAND_TOTAL] ?? 0,
                FutureSubscriptionInterface::COUPON_CODE => $futureSubscriptionsParams[FutureSubscriptionInterface::COUPON_CODE] ?? '',
                FutureSubscriptionInterface::DISCOUNT => $futureSubscriptionsParams[FutureSubscriptionInterface::DISCOUNT] ?? 0,
                FutureSubscriptionInterface::ORDERED_STATUS => $futureSubscriptionsParams[FutureSubscriptionInterface::ORDERED_STATUS] ?? '',
                FutureSubscriptionInterface::REMARKS => $futureSubscriptionsParams[FutureSubscriptionInterface::REMARKS] ?? ''
            ];

            /** @var  $futureSubscriptionModel */
            $futureSubscriptionModel = $this;

            if ($futureSubscriptionId !== 0) {
                $this->load($futureSubscriptionId);
            }
            /** @var  $futureSubscriptionResult */
            $futureSubscriptionResult = $this
                ->setData($futureSubscriptionsParams)
                ->save();

            if ($futureSubscriptionResult->getEntityId()) {
                $futureResponse = [
                    'error' => false,
                    'message' => __('Success, the future subscription recurring has been added'),
                    'response' => [
                        'future_subscription_id' => $futureSubscriptionResult->getEntityId()
                    ]
                ];

                $this->_ebizchargeLogger->addInfo(__(
                    'Success, the subscription has been added to the database'
                ));
            } else {
                $futureResponse = [
                    'error' => true,
                    'message' => __('Error occurred during adding future subscriptions'),
                    'response' => [
                        'future_subscription_id' => 0
                    ]
                ];

                $this->_ebizchargeLogger->addError(__('Error occurred during adding future subscriptions'));
            }

        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during adding | updating Future Subscription " . $exception->getMessage()
            ));
            $futureResponse = [
                'error' => true,
                'message' => __('Exception occurred during adding Subscriptions ' . $exception->getMessage()),
                'response' => [
                    'future_subscription_id' => 0
                ]
            ];
        }
        return $futureResponse;
    }

    /**
     * Delete By Recurring Id
     *
     * @param int $recurringId
     * @return bool
     * @throws Exception
     */
    public function deleteByRecurringId($recurringId = 0)
    {
        if (!$recurringId) {
            return false;
        }

        /** @var $futureSubscriptionsCollection */
        $futureSubscriptionsCollection = $this->getCollection()
            ->addFieldToFilter(FutureSubscriptionInterface::RECURRING_ID, $recurringId);
        if (count($futureSubscriptionsCollection) > 0) {
            foreach ($futureSubscriptionsCollection as $futureSubscription) {
                $this->load($futureSubscription->getEntityId())->delete();
            }
        }
        return true;
    }

    /**
     * Prepare Future Recurring Orders Collection
     *
     * @param string $fromDate
     * @param bool $isCron
     * @param string $outType
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareFutureRecurringOrdersCollection($fromDate = '', $isCron = false, $outType = 'cli')
    {
        /** @var $futureRecurrigOrders */
        $futureRecurrigOrders = [];
        $store = $this->_storeManagerInterface->getStore();
        $createdAt = $this->_soapApiFactory->create()->formateDateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s');

        /** @var tmp $fromDate */
        //  $fromDate = $fromDate;

        /** @var $futureRecurrigOrdersCollections */
        $futureRecurrigOrdersCollections = $this->futureCollection->prepareFutureRecurringOrdersCollection(
            $fromDate,
            $isCron,
            $outType
        );

        /** @var $alreadyPlacedMagentoOrders */
        $alreadyPlacedMagentoOrders = $this->getAlreadyPlacedRecurringOrders($futureRecurrigOrdersCollections);

        if (count($futureRecurrigOrdersCollections) > 0) {

            foreach ($futureRecurrigOrdersCollections as $futureRecurrigOrderItem) {
                /** @var $magentoOrderNumber */
                $magentoOrderNumber = $futureRecurrigOrderItem->getData(RecurringInterface::MAGE_ORDER_ID);
                $futureRecurringId = $futureRecurrigOrderItem->getData('future_recurring_id');
                /** @var $recurringItemId */
                $recurringItemId = $futureRecurrigOrderItem->getData(FutureSubscriptionInterface::RECURRING_ID);

                if (in_array($magentoOrderNumber, $alreadyPlacedMagentoOrders)) {
                    $recurringOrderedItems = $this->getMageOrderedRecurringItems($futureRecurrigOrderItem);

                    /** @var  $orderedItems */
                    $futureRecurrigOrders[$magentoOrderNumber] = [
                        'future_recurring_id' => $futureRecurringId,
                        'recurring_item_id' => $recurringItemId,
                        'recurring_item_name' => $futureRecurrigOrderItem->getData(RecurringInterface::MAGE_ITEM_NAME),
                        'recurring_id' => $futureRecurrigOrderItem->getData(FutureSubscriptionInterface::RECURRING_ID),
                        'order_number' => $magentoOrderNumber,
                        'salesordernumber' => $magentoOrderNumber,
                        'item_amount' => $futureRecurrigOrderItem->getData(RecurringInterface::AMOUNT),
                        'recurring_date' => $futureRecurrigOrderItem->getData(FutureSubscriptionInterface::RECURRING_DATE),
                        'store_id' => $futureRecurrigOrderItem->getData(FutureSubscriptionInterface::STORE_ID),
                        'customer_id' => $futureRecurrigOrderItem->getData(FutureSubscriptionInterface::CUSTOMER_ID),
                        'shipping_method' => $futureRecurrigOrderItem->getData(RecurringInterface::SHIPPING_METHOD),
                        'shipping_address_id' => $futureRecurrigOrderItem->getData(RecurringInterface::SHIPPING_ADDRESS_ID),
                        'billing_address_id' => $futureRecurrigOrderItem->getData(RecurringInterface::BILLING_ADDRESS_ID),
                        'payment_method_title' => $futureRecurrigOrderItem->getData(RecurringInterface::PAYMENT_METHOD_NAME),
                        'ebiz_scheduled_payment_reference_id' => $futureRecurrigOrderItem->getData(
                            RecurringInterface::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID
                        ),
                        'ebiz_payment_recurring_method_id' => $futureRecurrigOrderItem->getData(
                            RecurringInterface::EB_REC_METHOD_ID
                        ),
                        'ebiz_recurring_total' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_TOTAL),
                        'ebiz_recurring_due_dates' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_DUE_DATES),
                        'failed_attempts' => $futureRecurrigOrderItem->getData(RecurringInterface::PAYMENT_METHOD_NAME),
                        'payment_method_id' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_METHOD_ID),
                        'recurring_remaining' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_REMAINING),
                        'recurring_status' => $futureRecurrigOrderItem->getData(RecurringInterface::REC_STATUS),
                        'cart_rule_id' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_CART_RULE_ID),
                        'product_rule_id' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_PRODUCT_RULE_ID),
                        'created_at' => $futureRecurrigOrderItem->getData(FutureSubscriptionInterface::RECURRING_DATE),
                        'mage_order_id' => $futureRecurrigOrderItem->getData(RecurringInterface::MAGE_ORDER_ID),
                        'mage_item_id' => $futureRecurrigOrderItem->getData(RecurringInterface::MAGE_ITEM_ID),
                        'recurring_start_date' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_START_DATE),
                        'recurring_end_date' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_END_DATE),
                        'recurring_frequency' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_FREQUENCY),
                        'recurring_processed' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_PROCESSED),
                        'recurring_failed_attempts' => $futureRecurrigOrderItem->getData(RecurringInterface::FAILED_ATTEMPTS),
                        'ordered_date' => $futureRecurrigOrderItem->getData(RecurringInterface::ORDERED_DATE) ?
                            $futureRecurrigOrderItem->getData(RecurringInterface::ORDERED_DATE) :
                            $this->_soapApiFactory->create()->getStoreDefaultDateTime('', 'Y-m-d'),
                        'recurring_message' => '',
                        'currency' => $store->getCurrentCurrency()->getCurrencyCode(),
                        'items' => $recurringOrderedItems

                    ];
                } else {

                    $magentoOrderNumber = $this->getRecurrigOrderNumber();

                    $futureRecurrigOrders[$magentoOrderNumber] = [
                        'future_recurring_id' => $futureRecurringId,
                        'recurring_item_id' => $recurringItemId,
                        'item_amount' => $futureRecurrigOrderItem->getData(RecurringInterface::AMOUNT),
                        'recurring_item_name' => $futureRecurrigOrderItem->getData(RecurringInterface::MAGE_ITEM_NAME),
                        'recurring_id' => $futureRecurrigOrderItem->getData(FutureSubscriptionInterface::RECURRING_ID),
                        'order_number' => $magentoOrderNumber,
                        'salesordernumber' => $magentoOrderNumber,
                        'recurring_date' => $futureRecurrigOrderItem->getData(FutureSubscriptionInterface::RECURRING_DATE),
                        'store_id' => $futureRecurrigOrderItem->getData(FutureSubscriptionInterface::STORE_ID),
                        'customer_id' => $futureRecurrigOrderItem->getData(FutureSubscriptionInterface::CUSTOMER_ID),
                        'shipping_method' => $futureRecurrigOrderItem->getData(RecurringInterface::SHIPPING_METHOD),
                        'shipping_address_id' => $futureRecurrigOrderItem->getData(RecurringInterface::SHIPPING_ADDRESS_ID),
                        'billing_address_id' => $futureRecurrigOrderItem->getData(RecurringInterface::BILLING_ADDRESS_ID),
                        'payment_method_title' => $futureRecurrigOrderItem->getData(RecurringInterface::PAYMENT_METHOD_NAME),
                        'ebiz_scheduled_payment_reference_id' => $futureRecurrigOrderItem->getData(
                            RecurringInterface::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID
                        ),
                        'ebiz_payment_recurring_method_id' => $futureRecurrigOrderItem->getData(
                            RecurringInterface::EB_REC_METHOD_ID
                        ),
                        'ebiz_recurring_total' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_TOTAL),
                        'ebiz_recurring_due_dates' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_DUE_DATES),
                        'failed_attempts' => $futureRecurrigOrderItem->getData(RecurringInterface::PAYMENT_METHOD_NAME),
                        'payment_method_id' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_METHOD_ID),
                        'recurring_remaining' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_REMAINING),
                        'recurring_status' => $futureRecurrigOrderItem->getData(RecurringInterface::REC_STATUS),
                        'cart_rule_id' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_CART_RULE_ID),
                        'product_rule_id' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_PRODUCT_RULE_ID),
                        'created_at' => $futureRecurrigOrderItem->getData(FutureSubscriptionInterface::RECURRING_DATE),
                        'mage_order_id' => $futureRecurrigOrderItem->getData(RecurringInterface::MAGE_ORDER_ID),
                        'mage_item_id' => $futureRecurrigOrderItem->getData(RecurringInterface::MAGE_ITEM_ID),
                        'recurring_start_date' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_START_DATE),
                        'recurring_end_date' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_END_DATE),
                        'recurring_frequency' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_FREQUENCY),
                        'recurring_processed' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_PROCESSED),
                        'recurring_failed_attempts' => $futureRecurrigOrderItem->getData(RecurringInterface::FAILED_ATTEMPTS),
                        'ordered_date' => $futureRecurrigOrderItem->getData(RecurringInterface::ORDERED_DATE) ?
                            $futureRecurrigOrderItem->getData(RecurringInterface::ORDERED_DATE) :
                            $this->_soapApiFactory->create()->getStoreDefaultDateTime('', 'Y-m-d'),
                        'recurring_message' => '',
                        'currency' => $store->getCurrentCurrency()->getCurrencyCode(),
                        'items' => [
                            [
                                'mage_order_id' => $futureRecurrigOrderItem->getData(RecurringInterface::MAGE_ORDER_ID),
                                'item_id' => $futureRecurrigOrderItem->getData(RecurringInterface::ENTITY_ID),
                                'itemid' => $futureRecurrigOrderItem->getData(RecurringInterface::MAGE_ITEM_ID),
                                'final_amount' => $futureRecurrigOrderItem->getData(RecurringInterface::AMOUNT),
                                'qty_ordered' => $futureRecurrigOrderItem->getData(RecurringInterface::QTY_ORDERED),
                                'amount' => $futureRecurrigOrderItem->getData(RecurringInterface::AMOUNT),
                                'qty' => $futureRecurrigOrderItem->getData(RecurringInterface::QTY_ORDERED),
                                'product_id' => $futureRecurrigOrderItem->getData(RecurringInterface::MAGE_ITEM_ID),
                                'item_name' => $futureRecurrigOrderItem->getData(RecurringInterface::MAGE_ITEM_NAME),
                                'cart_rule_id' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_CART_RULE_ID),
                                'coupon_code' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_COUPON_CODE),
                                'discount_amount' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_DISCOUNT),
                                'entity_id' => $futureRecurrigOrderItem->getData(RecurringInterface::ENTITY_ID),
                                'recurring_remaining' => $futureRecurrigOrderItem->getData(RecurringInterface::EB_REC_REMAINING),
                                'ebiz_scheduled_payment_reference_id' => $futureRecurrigOrderItem->getData(
                                    RecurringInterface::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID
                                ),
                            ]
                        ]
                    ];
                }
            }
        }

        return $futureRecurrigOrders;
    }

    /**
     * Get Already Placed Recurring Orders
     *
     * @param mixed $futureRecurringSubscriptionsCollection
     * @return array
     */
    public function getAlreadyPlacedRecurringOrders($futureRecurringSubscriptionsCollection)
    {
        $recurringAlreadyPlacedOrders = [];
        if (count($futureRecurringSubscriptionsCollection) > 0) {
            foreach ($futureRecurringSubscriptionsCollection as $futureRecurringSubscriptionItem) {
                /** @var $mageOrderId */
                $mageOrderId = $futureRecurringSubscriptionItem->getMageOrderId();
                if ($mageOrderId != "0") {
                    $recurringAlreadyPlacedOrders[] = $mageOrderId;
                }
            }
        }
        return array_unique($recurringAlreadyPlacedOrders);
    }

    /**
     * Get Mage Ordered Recurring Items
     *
     * @param string $futureOrderedItem
     * @return array
     */
    public function getMageOrderedRecurringItems($futureOrderedItem = '')
    {
        /** @var $futureOrderNumber */
        $futureOrderNumber = $futureOrderedItem->getData(RecurringInterface::MAGE_ORDER_ID);
        $recurringId = $futureOrderedItem->getData(RecurringInterface::ENTITY_ID);
        $recurringDate = $futureOrderedItem->getData(FutureSubscriptionInterface::RECURRING_DATE);
        $orderedItems = [];

        /** @var $recurringOrdersCollections */
        $recurringOrdersCollections = $this->_recurringFactory->create()
            ->getCollection()
            ->addFieldToSelect('*')
            //   ->addFieldToFilter(FutureSubscriptionInterface::RECURRING_DATE, $recurringDate)
            ->addFieldToFilter(RecurringInterface::MAGE_ORDER_ID, $futureOrderNumber);

        if (count($recurringOrdersCollections) > 0) {
            foreach ($recurringOrdersCollections as $recurringOrdersItem) {
                $mageOrderNumber = $recurringOrdersItem->getData(RecurringInterface::MAGE_ORDER_ID);
                $orderedItems[] = [
                    'mage_order_id' => $recurringOrdersItem->getData(RecurringInterface::MAGE_ORDER_ID),
                    'item_id' => $recurringOrdersItem->getData(RecurringInterface::ENTITY_ID),
                    'itemid' => $recurringOrdersItem->getData(RecurringInterface::MAGE_ITEM_ID),
                    'final_amount' => $recurringOrdersItem->getData(RecurringInterface::AMOUNT),
                    'qty_ordered' => $recurringOrdersItem->getData(RecurringInterface::QTY_ORDERED),
                    'qty' => $recurringOrdersItem->getData(RecurringInterface::QTY_ORDERED),
                    'amount' => $recurringOrdersItem->getData(RecurringInterface::AMOUNT),
                    'product_id' => $recurringOrdersItem->getData(RecurringInterface::MAGE_ITEM_ID),
                    'item_name' => $recurringOrdersItem->getData(RecurringInterface::MAGE_ITEM_NAME),
                    'cart_rule_id' => $recurringOrdersItem->getData(RecurringInterface::EB_REC_CART_RULE_ID),
                    'coupon_code' => $recurringOrdersItem->getData(RecurringInterface::EB_REC_COUPON_CODE),
                    'discount_amount' => $recurringOrdersItem->getData(RecurringInterface::EB_REC_DISCOUNT),
                    'entity_id' => $recurringOrdersItem->getData(RecurringInterface::ENTITY_ID),
                    'recurring_remaining' => $recurringOrdersItem->getData(RecurringInterface::EB_REC_REMAINING),
                    'ebiz_scheduled_payment_reference_id' => $recurringOrdersItem->getData(
                        RecurringInterface::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID
                    ),
                ];
            }
        }

        return $orderedItems;
    }

    /**
     * Get Recurring Order Number
     *
     * @return int
     */
    public function getRecurrigOrderNumber()
    {
        return rand(289214564, 998874797);
    }

    /**
     * Get Created At
     *
     * @return array|mixed|null
     */
    public function getCreatedAt()
    {
        return $this->getData(FutureSubscriptionInterface::CREATED_AT);
    }

    /**
     * Set Created At
     *
     * @param mixed $createdAt
     * @return FutureSubscriptionInterface
     */
    public function setCreatedAt($createdAt): FutureSubscriptionInterface
    {
        return $this->setData(FutureSubscriptionInterface::CREATED_AT, $createdAt);
    }

    /**
     * Set Updated At
     *
     * @param mixed $updatedAt
     * @return FutureSubscriptionInterface
     */
    public function setUpdatedAt($updatedAt): FutureSubscriptionInterface
    {
        return $this->setData(FutureSubscriptionInterface::UPDATED_AT, $updatedAt);
    }

    /**
     * Get Updated At
     *
     * @return array|mixed|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(FutureSubscriptionInterface::UPDATED_AT);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\FutureSubscription::class);
    }
}
