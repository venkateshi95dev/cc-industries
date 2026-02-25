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
use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\Data\SoapApiModelInterface;
use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ProductFactory as EbizProductFactory;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring as RecurringResourceModel;
use Exception;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\Tax\Api\TaxCalculationInterface as TaxCalculation;
use SoapFault;

/**
 * Ebizcharge Recurring Model
 *
 * Class Recurring
 */
class Recurring extends AbstractModel implements RecurringInterface, IdentityInterface, ScopeConfigInterface
{
    /**
     * @var array
     */
    public static array $recurringStatuses = [
        RecurringInterface::EBIZCHARGE_RECURRING_STATUS_ACTIVE => RecurringInterface::EBIZCHARGE_RECURRING_STATUS_ACTIVE_TITLE,
        RecurringInterface::EBIZCHARGE_RECURRING_STATUS_SUSPENDED => RecurringInterface::EBIZCHARGE_RECURRING_STATUS_SUSPENDED_TITLE,
        RecurringInterface::EBIZCHARGE_RECURRING_STATUS_EXPIRED => RecurringInterface::EBIZCHARGE_RECURRING_STATUS_EXPIRED_TITLE,
        RecurringInterface::EBIZCHARGE_RECURRING_STATUS_CANCELED => RecurringInterface::EBIZCHARGE_RECURRING_STATUS_CANCELED_TITLE
    ];
    /**
     * Recurring Subscription Statues
     *
     * @var int[]
     */
    public array $_recurringStatuses;

    /**
     * Recurring Frequencies
     *
     * @var $recurringFrequencies
     */
    //public $recurringFrequencies;
    /**
     * Status Labels
     *
     * @var array
     */
    public array $_recurringStatusesLabels;
    /**
     * Cache Tag var
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;
    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;
    /**
     * @var ProductFactory
     */
    protected EbizProductFactory $_productFactory;
    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;
    /**
     * @var TranApi
     */
    protected TranApi $_soapApiModel;
    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;
    /**
     * @var FutureSubscriptionFactory
     */
    protected FutureSubscriptionFactory $_futureSubscriptionsFactory;
    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $_timezoneInterface;
    /**
     * @var Coupon
     */
    protected Coupon $_couponCodeModel;
    /**
     * @var RecurringRepositoryInterface
     */
    protected RecurringRepositoryInterface $_recurringRepository;
    /**
     * @var Resolver
     */
    protected Resolver $_localeResolver;
    /**
     * @var Config
     */
    protected Config $_configModel;
    /**
     * @var Magento\Sales\Api\OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;
    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;
    /**
     * @var OrderItemRepositoryInterface
     */
    protected OrderItemRepositoryInterface $orderItemRepository;
    /**
     * @var TaxCalculation
     */
    protected TaxCalculation $taxCalculation;
    /**
     * @var CustomerSession
     */
    private CustomerSession $_customerSession;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CustomerFactory $customerFactory
     * @param \Ebizcharge\Ebizcharge\Model\ProductFactory $ebizProductFactory
     * @param OrderFactory $orderFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param TranApi $soapApiModel
     * @param Coupon $couponCodeModel
     * @param RecurringRepositoryInterface $recurringRepository
     * @param EbizchargeLogger $ebizchargeLogger
     * @param Resolver $localeResolver
     * @param TimezoneInterface $timezoneInterface
     * @param FutureSubscriptionFactory $futureSubscriptionFactory
     * @param Config $configModel
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param TaxCalculation $taxCalculation
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context                      $context,
        Registry                     $registry,
        CustomerFactory              $customerFactory,
        EbizProductFactory           $ebizProductFactory,
        OrderFactory                 $orderFactory,
        OrderRepositoryInterface     $orderRepository,
        TranApi                      $soapApiModel,
        Coupon                       $couponCodeModel,
        RecurringRepositoryInterface $recurringRepository,
        EbizchargeLogger             $ebizchargeLogger,
        Resolver                     $localeResolver,
        TimezoneInterface            $timezoneInterface,
        FutureSubscriptionFactory    $futureSubscriptionFactory,
        Config                       $configModel,
        OrderItemRepositoryInterface $orderItemRepository,
        CheckoutSession              $checkoutSession,
        CustomerSession              $customerSession,
        TaxCalculation               $taxCalculation,
        AbstractResource             $resource = null,
        AbstractDb                   $resourceCollection = null,
        array                        $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _productFactory */
        $this->_productFactory = $ebizProductFactory;
        /** @var _orderFactory */
        $this->_orderFactory = $orderFactory;
        /** @var _soapApiModel */
        $this->_soapApiModel = $soapApiModel;
        /** @var  _timezoneInterface */
        $this->_timezoneInterface = $timezoneInterface;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _futureSubscriptionsFactory */
        $this->_futureSubscriptionsFactory = $futureSubscriptionFactory;
        /** @var Coupon */
        $this->_couponCodeModel = $couponCodeModel;
        /** @var  _recurringRepository */
        $this->_recurringRepository = $recurringRepository;
        /** @var _localeResolver */
        $this->_localeResolver = $localeResolver;
        /** @var  _configModel */
        $this->_configModel = $configModel;
        /** @var _customerSession */
        $this->_customerSession = $customerSession;
        /** @var  orderRepository */
        $this->orderRepository = $orderRepository;
        /** @var  checkoutSession */
        $this->checkoutSession = $checkoutSession;
        /** @var  orderItemRepository */
        $this->orderItemRepository = $orderItemRepository;
        /**
         * Tax Calculation
         */
        $this->taxCalculation = $taxCalculation;


        /** @var  _recurringStatuses */
        $this->_recurringStatuses = [
            RecurringInterface::EBIZCHARGE_RECURRING_STATUS_KEY_UNSUBSCRIBED => RecurringInterface::EBIZCHARGE_RECURRING_STATUS_CANCELED,
            RecurringInterface::EBIZCHARGE_RECURRING_STATUS_KEY_SUSPENDED => RecurringInterface::EBIZCHARGE_RECURRING_STATUS_SUSPENDED,
            RecurringInterface::EBIZCHARGE_RECURRING_STATUS_KEY_EXPIRED => RecurringInterface::EBIZCHARGE_RECURRING_STATUS_EXPIRED,
            RecurringInterface::EBIZCHARGE_RECURRING_STATUS_KEY_ACTIVE => RecurringInterface::EBIZCHARGE_RECURRING_STATUS_ACTIVE
        ];

        /** @var  _recurringStatusesLabels */
        // phpcs:disable
        $this->_recurringStatusesLabels = [
            RecurringInterface::EBIZCHARGE_RECURRING_STATUS_ACTIVE => __(RecurringInterface::RECURRING_STATUS_LABEL_ACTIVE),
            RecurringInterface::EBIZCHARGE_RECURRING_STATUS_SUSPENDED => __(RecurringInterface::RECURRING_STATUS_LABEL_SUSPENDED),
            RecurringInterface::EBIZCHARGE_RECURRING_STATUS_EXPIRED => __(RecurringInterface::RECURRING_STATUS_LABEL_EXPIRED),
            RecurringInterface::EBIZCHARGE_RECURRING_STATUS_CANCELED => __(RecurringInterface::RECURRING_STATUS_LABEL_UN_SUBSCRIBED),
        ];
        // phpcs:enable

        /**
         * Frequencies
         */
        //$this->recurringFrequencies = RecurringInterface::getRecurringFrequencies();
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId(), self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    /**
     * Get Entity Id
     *
     * @return int|mixed
     */
    public function getEntityId()
    {
        return (int)$this->getData(RecurringInterface::ENTITY_ID);
    }

    /**
     * Set Entity Id
     *
     * @param string $entityId
     * @return RecurringInterface
     */
    public function setEntityId($entityId): RecurringInterface
    {
        return $this->setData(RecurringInterface::ENTITY_ID, $entityId);
    }

    /**
     * Get Recurring Id
     *
     * @return string
     */
    public function getRecId(): string
    {
        return $this->getData(RecurringInterface::REC_ID);
    }

    /**
     * Set Recurring Indefinitely
     *
     * @param int $rec_indefinitely
     * @return RecurringInterface
     */
    public function setRecIndefinitely(int $rec_indefinitely): RecurringInterface
    {
        return $this->setData(RecurringInterface::REC_INDEFINITELY, $rec_indefinitely);
    }

    /**
     * Get Recurring Indefinitely
     *
     * @return int
     */
    public function getRecIndefinitely(): int
    {
        return (int)$this->getData(RecurringInterface::REC_INDEFINITELY);
    }

    /**
     * Set Mage Customer Id
     *
     * @param string $mage_cust_id
     * @return RecurringInterface
     */
    public function setMageCustId(string $mage_cust_id): RecurringInterface
    {
        return $this->setData(RecurringInterface::MAGE_CUST_ID, $mage_cust_id);
    }

    /**
     * Product Rule Id
     *
     * @return int
     */
    public function getProductRuleId(): int
    {
        return (int)$this->getData(RecurringInterface::EB_REC_PRODUCT_RULE_ID);
    }

    /**
     * Set Product Rule Id
     *
     * @param int $productRuleId
     * @return RecurringInterface
     */
    public function setProductRuleId(int $productRuleId): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_PRODUCT_RULE_ID, $productRuleId);
    }

    /**
     * Get Cart Rule Id
     *
     * @return int
     */
    public function getCartRuleId(): int
    {
        return (int)$this->getData(RecurringInterface::EB_REC_CART_RULE_ID);
    }

    /**
     * Set Cart Rule Id
     *
     * @param int $cartRuleId
     * @return RecurringInterface
     */
    public function setCartRuleId(int $cartRuleId): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_CART_RULE_ID, $cartRuleId);
    }

    /**
     * Get Discount
     *
     * @return string
     */
    public function getDiscount(): string
    {
        return $this->getData(RecurringInterface::EB_REC_DISCOUNT);
    }

    /**
     * Set Discount
     *
     * @param string $discount
     * @return RecurringInterface
     */
    public function setDiscount(string $discount): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_DISCOUNT, $discount);
    }

    /**
     * Get Shipping Amount
     *
     * @return string
     */
    public function getShippingAmuont(): float
    {
        return (float)$this->getData(RecurringInterface::SHIPPING_AMOUNT);
    }

    /**
     * Set Shipping Amount
     *
     * @param string $shippingAmount
     * @return RecurringInterface
     */
    public function setShippingAmount(string $shippingAmount): RecurringInterface
    {
        return $this->setData(RecurringInterface::SHIPPING_AMOUNT, $shippingAmount);
    }

    /**
     * Set Grand Total
     *
     * @param string $grandTotal
     * @return RecurringInterface
     */
    public function setGrandTotal(string $grandTotal): RecurringInterface
    {
        return $this->setData(RecurringInterface::GRAND_TOTAL, $grandTotal);
    }

    /**
     * Get Coupon Code
     *
     * @return string
     */
    public function getCouponCode(): string
    {
        return $this->getData(RecurringInterface::EB_REC_COUPON_CODE);
    }

    /**
     * Set Coupon Code
     *
     * @param string $couponCode
     * @return RecurringInterface
     */
    public function setCouponCode(string $couponCode): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_COUPON_CODE, $couponCode);
    }

    /**
     * Get Mage Customer Id
     *
     * @return string
     */
    public function getMageCustId(): string
    {
        return $this->getData(RecurringInterface::MAGE_CUST_ID);
    }

    /**
     * Set Store Id
     *
     * @param int $storeId
     * @return RecurringInterface
     */
    public function setStoreId(int $storeId): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_STORE_ID, $storeId);
    }

    /**
     * Set Mage Order id
     *
     * @param string $mage_order_id
     * @return RecurringInterface
     */
    public function setMageOrderId(string $mage_order_id): RecurringInterface
    {
        return $this->setData(RecurringInterface::MAGE_ORDER_ID, $mage_order_id);
    }

    /**
     * Get Mage Order Id
     *
     * @return string
     */
    public function getMageOrderId(): string
    {
        return (string)$this->getData(RecurringInterface::MAGE_ORDER_ID);
    }

    /**
     * Set Mage Item Id
     *
     * @param string $mage_item_id
     * @return RecurringInterface
     */
    public function setMageItemId(string $mage_item_id): RecurringInterface
    {
        return $this->setData(RecurringInterface::MAGE_ITEM_ID, $mage_item_id);
    }

    /**
     * Get Mage Item Id
     *
     * @return string
     */
    public function getMageItemId(): string
    {
        return $this->getData(RecurringInterface::MAGE_ITEM_ID);
    }

    /**
     * Set Mage Item Name
     *
     * @param string $mage_item_name
     * @return RecurringInterface
     */
    public function setMageItemName(string $mage_item_name): RecurringInterface
    {
        return $this->setData(RecurringInterface::MAGE_ITEM_NAME, $mage_item_name);
    }

    /**
     * Set Qty Ordered
     *
     * @param string $qty_ordered
     * @return RecurringInterface
     */
    public function setQtyOrdered(string $qty_ordered): RecurringInterface
    {
        return $this->setData(RecurringInterface::QTY_ORDERED, $qty_ordered);
    }

    /**
     * Set Eb Recurring Start Date
     *
     * @param string $eb_rec_start_date
     * @return RecurringInterface
     */
    public function setEbRecStartDate(string $eb_rec_start_date): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_START_DATE, $eb_rec_start_date);
    }

    /**
     * Get Recurring Start Date
     *
     * @return string
     */
    public function getEbRecStartDate(): string
    {
        return $this->getData(RecurringInterface::EB_REC_START_DATE);
    }

    /**
     * Set Recurring End Date
     *
     * @param string $eb_rec_end_date
     * @return RecurringInterface
     */
    public function setEbRecEndDate(string $eb_rec_end_date): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_END_DATE, $eb_rec_end_date);
    }

    /**
     * Get Recurring Date
     *
     * @return string
     */
    public function getEbRecEndDate(): string
    {
        return $this->getData(RecurringInterface::EB_REC_END_DATE);
    }

    /**
     * Set Recurring Frequency
     *
     * @param string $eb_rec_frequency
     * @return RecurringInterface
     */
    public function setEbRecFrequency(string $eb_rec_frequency): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_FREQUENCY, $eb_rec_frequency);
    }

    /**
     * Get Recurring Frequency
     *
     * @return string
     */
    public function getEbRecFrequency(): string
    {
        return $this->getData(RecurringInterface::EB_REC_FREQUENCY);
    }

    /**
     * Set Recurring Method Id
     *
     * @param mixed $eb_rec_method_id
     * @return RecurringInterface
     */
    public function setEbRecMethodId($eb_rec_method_id): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_METHOD_ID, $eb_rec_method_id);
    }

    /**
     * Set Recurring Scheduled Payment Internal Id
     *
     * @param string $ebRecScheduledPaymentInternalId
     * @return RecurringInterface
     */
    public function setEbRecScheduledPaymentInternalId(string $ebRecScheduledPaymentInternalId): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID, $ebRecScheduledPaymentInternalId);
    }

    /**
     * Set Recurring Total
     *
     * @param int $eb_rec_total
     * @return RecurringInterface
     */
    public function setEbRecTotal(int $eb_rec_total): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_TOTAL, $eb_rec_total);
    }

    /**
     * Get Recurring Total
     *
     * @return int
     */
    public function getEbRecTotal(): int
    {
        return $this->getData(RecurringInterface::EB_REC_TOTAL);
    }

    /**
     * Set Recurring Processed
     *
     * @param int $eb_rec_processed
     * @return RecurringInterface
     */
    public function setEbRecProcessed(int $eb_rec_processed): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_PROCESSED, $eb_rec_processed);
    }

    /**
     * Get Recurring Processed
     *
     * @return int
     */
    public function getEbRecProcessed(): int
    {
        return $this->getData(RecurringInterface::EB_REC_PROCESSED);
    }

    /**
     * Set Recurring Next
     *
     * @param string $eb_rec_next
     * @return RecurringInterface
     */
    public function setEbRecNext(string $eb_rec_next): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_NEXT, $eb_rec_next);
    }

    /**
     * Get Recurring Next
     *
     * @return string
     */
    public function getEbRecNext(): string
    {
        return $this->getData(RecurringInterface::EB_REC_NEXT);
    }

    /**
     * Set Recurring Remaining
     *
     * @param int $eb_rec_remaining
     * @return RecurringInterface
     */
    public function setEbRecRemaining(int $eb_rec_remaining): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_REMAINING, $eb_rec_remaining);
    }

    /**
     * Get Recurring Remaining
     *
     * @return int
     */
    public function getEbRecRemaining(): int
    {
        return $this->getData(RecurringInterface::EB_REC_REMAINING);
    }

    /**
     * Set Recurring Due Dates
     *
     * @param string $eb_rec_due_dates
     * @return RecurringInterface
     */
    public function setEbRecDueDates(string $eb_rec_due_dates): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_DUE_DATES, $eb_rec_due_dates);
    }

    /**
     * Get Recurring Dates
     *
     * @return int
     */
    public function getEbRecDueDates(): int
    {
        return $this->getData(RecurringInterface::EB_REC_DUE_DATES);
    }

    /**
     * Set Mage Parent Items Id
     *
     * @param string $mage_parent_item_id
     * @return RecurringInterface
     */
    public function setMageParentItemId(string $mage_parent_item_id): RecurringInterface
    {
        return $this->setData(RecurringInterface::MAGE_PARENT_ITEM_ID, $mage_parent_item_id);
    }

    /**
     * Get Mage Parent Item Id
     *
     * @return string
     */
    public function getMageParentItemId(): string
    {
        return $this->getData(RecurringInterface::MAGE_PARENT_ITEM_ID);
    }

    /**
     * Set Billing Address Id
     *
     * @param int $billing_address_id
     * @return RecurringInterface
     */
    public function setBillingAddressId(int $billing_address_id): RecurringInterface
    {
        return $this->setData(RecurringInterface::BILLING_ADDRESS_ID, $billing_address_id);
    }

    /**
     * Set Shipping Address id
     *
     * @param int $shipping_address_id
     * @return RecurringInterface
     */
    public function setShippingAddressId(int $shipping_address_id): RecurringInterface
    {
        return $this->setData(RecurringInterface::SHIPPING_ADDRESS_ID, $shipping_address_id);
    }

    /**
     * Get Shipping Address id
     *
     * @return int
     */
    public function getShippingAddressId(): int
    {
        return (int)$this->getData(RecurringInterface::SHIPPING_ADDRESS_ID);
    }

    /**
     * Set Amount
     *
     * @param float $amount
     * @return RecurringInterface
     */
    public function setAmount(float $amount): RecurringInterface
    {
        return $this->setData(RecurringInterface::AMOUNT, $amount);
    }

    /**
     * Set payment Method Name
     *
     * @param string $payment_method_name
     * @return RecurringInterface
     */
    public function setPaymentMethodName(string $payment_method_name): RecurringInterface
    {
        return $this->setData(RecurringInterface::PAYMENT_METHOD_NAME, $payment_method_name);
    }

    /**
     * Set Shippping Method
     *
     * @param string $shipping_method
     * @return RecurringInterface
     */
    public function setShippingMethod(string $shipping_method): RecurringInterface
    {
        return $this->setData(RecurringInterface::SHIPPING_METHOD, $shipping_method);
    }

    /**
     * Set Failed Attempts
     *
     * @param int $failed_attempts
     * @return RecurringInterface
     */
    public function setFailedAttempts(int $failed_attempts): RecurringInterface
    {
        return $this->setData(RecurringInterface::FAILED_ATTEMPTS, $failed_attempts);
    }

    /**
     * Get Failed Attempts
     *
     * @return int
     */
    public function getFailedAttempts(): int
    {
        return $this->getData(RecurringInterface::FAILED_ATTEMPTS);
    }

    /**
     * Get Final Product Final Price
     *
     * @return string
     */
    public function getEbOrderedProductFinalPrice(): string
    {
        return $this->getData(RecurringInterface::EB_ORDERED_PRODUCT_FINAL_PRICE);
    }

    /**
     * Set EB Ordred Product Final Price
     *
     * @param string $eb_rec_ordered_product_final_price
     * @return RecurringInterface
     */
    public function setEbOrderedProductFinalPrice(string $eb_rec_ordered_product_final_price): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_ORDERED_PRODUCT_FINAL_PRICE, $eb_rec_ordered_product_final_price);
    }

    /**
     * Load By Scheduled Payment Internal Id
     *
     * @param null|mixed $scheduledPaymentInternalId
     * @return Recurring
     */
    public function loadByScheduledPaymentInternalId($scheduledPaymentInternalId = null)
    {
        return $this->load($this->getResource()->loadByScheduledPaymentInternalId($scheduledPaymentInternalId));
    }

    /**
     * Update Recurring Status
     *
     * @param int $recurringId
     * @param int $recurringStatus
     * @return array
     */
    public function updateRecurringStatus($recurringId = 0, $recurringStatus = 0)
    {
        $recurringStatusResp = [
            'error' => true,
            'status' => 'error',
            'message' => __(''),
        ];

        try {
            /** @var  $recurring */
            $recurring = $this->load((int)$recurringId);
            if ($recurring) {
                $statusUpdated = $recurring->setRecStatus((int)$recurringStatus)
                    ->save();
                if ($statusUpdated) {
                    $this->_ebizchargeLogger->addInfo(__("Success, the recurring status has been updated"));
                    $recurringStatusResp = [
                        'error' => false,
                        'status' => 'success',
                        'message' => __("Success, the recurring status has been updated"),
                    ];

                    return $recurringStatusResp;
                }
            } else {
                $recurringStatusResp['message'] = __("Error occurred during updating status of recurring ");
                $this->_ebizchargeLogger->addInfo(__("Error occurred during updating status of recurring"));
                return $recurringStatusResp;
            }
        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Error occurred during updating status of recurring. Error:" . $exception->getMessage()
            ));
            $recurringStatusResp['message'] = __("Exception occurding during updating status Exception: " .
                $exception->getMessage());
            return $recurringStatusResp;
        }

        return $recurringStatusResp;
    }

    /**
     * Set Recurring Status
     *
     * @param int $rec_status
     * @return RecurringInterface
     */
    public function setRecStatus(int $rec_status): RecurringInterface
    {
        return $this->setData(RecurringInterface::REC_STATUS, $rec_status);
    }

    /**
     * Suspended Scheduled Recurring Payment Status
     *
     * @param string $recurringPaymentInternalId
     * @param int $recurringStatus
     * @return array
     */
    public function suspendScheduledRecurringPaymentStatus($recurringPaymentInternalId = '', $recurringStatus = 1)
    {
        /** @var $recurringStatusSuspendResp */
        $recurringStatusSuspendResp = [
            'error' => true,
            'message' => __(),
            'status' => false
        ];

        try {
            /** @var $suspendRecurringPayment */
            $suspendRecurringPayment = $this->_soapApiModel->suspendScheduledRecurringPaymentStatus(
                $recurringPaymentInternalId,
                $recurringStatus
            );

            if ($suspendRecurringPayment['error'] == false) {
                $this->_ebizchargeLogger->addInfo(__(
                    'Success, the recurring payment has been suspended at Ebizcharge Gateway'
                ));
                $recurringStatusSuspendResp['error'] = false;
                $recurringStatusSuspendResp['status'] = true;
                $recurringStatusSuspendResp['message'] = __(
                    'Success, the recurring payment has been suspended at Ebizcharge Gateway'
                );
            } else {
                $recurringStatusSuspendResp['message'] = __(
                    'Error occurred during during suspending the payment at Ebizcharge Gateway '
                );
            }
            return $recurringStatusSuspendResp;
        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                'Error occurred during during suspending the payment at Ebizcharge Gateway Error: ' .
                $exception->getMessage()
            ));
            $recurringStatusSuspendResp['message'] = __(
                'Error occurred during during suspending the payment at Ebizcharge Gateway Error: ' .
                $exception->getMessage()
            );
            return $recurringStatusSuspendResp;
        }
    }

    /**
     * @param $recurringParams
     * @return array
     */
    public function addSingleRecurringOrder($recurringParams = [])
    {
        $recurringResponse = [
            "status" => false,
            "success" => false,
            "message" => __("Error occurred during adding subscription.")
        ];

        try {
            $paymentMethodParams = isset($recurringParams["method"]) ? $recurringParams["method"] : [];
            $recurringPaymentFormParams = isset($recurringParams["payment"]) ? $recurringParams["payment"] : [];
            $quote = $this->checkoutSession->getQuote();
            $paymentFormParams = [];
            if (is_array($recurringPaymentFormParams) && count($recurringPaymentFormParams) > 0) {
                foreach ($recurringPaymentFormParams as $paymentFormParam) {
                    $paymentOptionName = isset($paymentFormParam["name"]) ? str_replace(["payment[", "]"], [], $paymentFormParam["name"]) : "";
                    $paymentOptionValue = isset($paymentFormParam["value"]) ? $paymentFormParam["value"] : "";
                    $paymentFormParams[$paymentOptionName] = $paymentOptionValue;
                }
                $recurringParams["payment"] = $paymentFormParams;
            }

            if ($this->_customerSession->isLoggedIn()) {
                $transactionInfo = null;
                $customerId = $this->_customerSession->getCustomerId();
                $customer = $this->_customerFactory->create()->load($customerId);
                $ebizCustomerId = $customer->getEcCustId() ?? "";
                $paymentAdditionalData = isset($paymentMethodParams["additional_data"]) ? $paymentMethodParams["additional_data"] : [];

                if (isset($paymentAdditionalData["transaction_info"])) {
                    $transactionInformation = json_decode($paymentAdditionalData["transaction_info"]) ?? [];
                    $transactionInfo = (array)$transactionInformation;
                    $paymentMethodParams["ebzc_method_id"] = isset($transactionInfo["PmToken"]) ? $transactionInfo["PmToken"] : 0;
                    $paymentMethodParams["cc_type"] = isset($transactionInfo["CCType"]) ? $transactionInfo["CCType"] : 0;
                    $paymentMethodParams["paymentToken"] = isset($transactionInfo["CustToken"]) ? $transactionInfo["CustToken"] : 0;
                    $paymentMethodParams["ebzc_option_type"] = isset($transactionInfo["PayByType"]) ? $transactionInfo["PayByType"] : 0;
                    $paymentMethodParams["cc_type_2"] = $this->_configModel->getLongCcType(isset($transactionInfo["CCType"]) ? $transactionInfo["CCType"] : "");
                    $paymentMethodParams["additional_data"]["ebzc_method_id"] = isset($transactionInfo["PmToken"]) ? $transactionInfo["PmToken"] : 0;
                    $paymentMethodParams["additional_data"]["cc_type"] = isset($transactionInfo["CCType"]) ? $transactionInfo["CCType"] : 0;
                    $paymentMethodParams["additional_data"]["paymentToken"] = isset($transactionInfo["CustToken"]) ? $transactionInfo["CustToken"] : 0;
                    $paymentMethodParams["additional_data"]["ebzc_option_type"] = isset($transactionInfo["PayByType"]) ? $transactionInfo["PayByType"] : 0;
                    $paymentMethodParams["additional_data"]["cc_type_2"] = $this->_configModel->getLongCcType(isset($transactionInfo["CCType"]) ? $transactionInfo["CCType"] : "");
                    $paymentMethodParams["additional_data"]["avs_street"] = isset($recurringParams["payment"]["cc_avs_street"]) ? $recurringParams["payment"]["cc_avs_street"] : "";
                    $paymentMethodParams["additional_data"]["avs_zip"] = isset($recurringParams["payment"]["cc_avs_zip"]) ? $recurringParams["payment"]["cc_avs_zip"] : "";

                    $paymentAdditionalData["ebzc_method_id"] = isset($transactionInfo["PmToken"]) ? $transactionInfo["PmToken"] : 0;
                    $paymentAdditionalData["cc_type"] = isset($transactionInfo["CCType"]) ? $transactionInfo["CCType"] : 0;
                    $paymentAdditionalData["cc_type_2"] = $this->_configModel->getLongCcType(isset($transactionInfo["CCType"]) ? $transactionInfo["CCType"] : "");

                }
                /**
                 * recurring order Params
                 */
                $recurringOrderParams = [
                    "customer_id" => $ebizCustomerId,
                    "payment" => $paymentMethodParams,
                    'method_id' => isset($paymentAdditionalData["ebzc_method_id"]) ? $paymentAdditionalData["ebzc_method_id"] : 0,
                ];
                /**
                 * adding recurring order
                 */
                $recurringOrder = $this->addRecurringOrderItems($recurringOrderParams);

                if (isset($recurringOrder["error"]) && $recurringOrder["error"] === false) {
                    $recurringResponse["status"] = true;
                    $recurringResponse["success"] = true;
                    $recurringResponse["message"] = __("Success subscribed item has been added successfully.");

                } else {

                    $recurringResponse["message"] = $recurringOrder["message"] ?? __("Error occurred during adding subscription.");
                    $recurringResponse["status"] = false;
                    $recurringResponse["success"] = false;
                }
            }

        } catch (Exception $exception) {
            $recurringResponse["message"] = $exception->getMessage();
        }

        return $recurringResponse;

    }

    /**
     * Add Recurring Order Items
     *
     * @param $recurringOrderParams
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addRecurringOrderItems(array $recurringOrderParams = []): array
    {
        /**
         * recurring Response
         */
        $recurringResp = [
            "error" => true,
            "status" => false,
            "exception" => "Exception occurred during adding subscriptions."
        ];

        $itemParams = [];
        $recurredItems = [];
        /** Item Params */
        $itemParams["payment"] = $recurringOrderParams["payment"] ?? null;
        $itemParams["method_id"] = $recurringOrderParams["method_id"] ?? null;
        $itemParams["customer_transaction_params"] = $recurringOrderParams["customer_transaction_params"] ?? [];
        $payment = $itemParams["payment"] ?? null;

        if (is_object($payment)) {
            $order = $payment->getOrder();
        } else {
            $order = $this->checkoutSession->getQuote();
        }

        $orderedItems = $order->getAllVisibleItems();

        if (!$orderedItems) {
            $orderedItems = $order->getAllItems();
        }

        try {
            if (count($orderedItems) > 0) {
                foreach ($orderedItems as $orderedItem) {
                    /** @var  $productOptions */
                    $buyRequest = $orderedItem->getBuyRequest();
                    $recurring = $buyRequest->getRecurring() ?? null;

                    if (isset($recurring["rec_frequency"]) && !empty($recurring["rec_frequency"])) {
                        $itemParams['item'] = $orderedItem;
                        $itemParams["isRecurring"] = true;
                        /** @var  $recurringOrderParams */
                        $recurringOrderParams = $this->prepareRecurringParams($itemParams);
                        /** @var  $recurringResp */
                        $recurringResp = $this->addRecurringOrder($recurringOrderParams);

                        if (isset($recurringResp["error"]) && $recurringResp["error"] === false) {
                            $recurredItems[] = $orderedItem->getSku();
                        }
                    }
                }
                if (count($recurredItems) > 0) {
                    $recurringResp = [
                        "error" => false,
                        "status" => true,
                        "exception" => "Success, the subscription(s) has been added successfully."
                    ];
                    $this->_ebizchargeLogger->addInfo(__("Success, the subscription has been added successfully."));
                }

            }
        } catch (NoSuchEntityException $e) {
            $this->_ebizchargeLogger->addCritical(__(
                "Error occurred during creating Recurring Error: " . $e->getMessage()
            ));
            $recurringResp["exception"] = __(
                "Error occurred during creating Recurring Error: " . $e->getMessage()
            );
        }
        return $recurringResp;
    }

    /**
     * @param $itemParams
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareRecurringParams(array $itemParams = []): array
    {
        $recurringParams = [];
        $quote = $this->checkoutSession->getQuote() ?? null;

        /** @var $transactionParams */
        $transactionParams = $itemParams["customer_transaction_params"] ?? [];

        if (isset($transactionParams["isRecurring"]) && !$transactionParams["isRecurring"]) {
            return $recurringParams;
        }

        /** @var  $orderItem */
        $orderItem = isset($itemParams["item"]) ? $itemParams["item"] : null;
        $itemId = $orderItem->getItemId();


        $buyRequest = $orderItem->getBuyRequest() ?? null;
        $infoBuyRequest = $buyRequest->getRecurring() ?? [];
        $recurringActivated = isset($infoBuyRequest["rec_activate"]) && !empty($infoBuyRequest["rec_activate"]) ? $infoBuyRequest["rec_activate"] : false;

        /** $recurring Options  */
        if ($recurringActivated) {

            $payment = $itemParams["payment"] ?? null;

            if (is_object($payment)) {
                $additionalInformation = $payment->getAdditionalInformation();
                /** @var Order $order */
                $order = $payment->getOrder();
                $orderQtyOrdered = (float)$orderItem->getQtyOrdered();
                $orderedQty = $orderQtyOrdered ?? 1;
            } else {
                /** @var Order $order */
                $order = $this->checkoutSession->getQuote();
                $additionalInformation = isset($payment["additional_data"]) ? $payment["additional_data"] : [];
                $orderQtyOrdered = (float)$orderItem->getQty();
                $orderedQty = $orderQtyOrdered ?? 1;
            }
            $customerId = $order->getCustomerId();
            $customer = $this->_customerFactory->create()->load($customerId);
            $customerToken = $customer->getEcCustToken() ?? "";
            $billingAddressId = $order->getBillingAddress() ? $order->getBillingAddress()->getCustomerAddressId() : "";
            $shippingAddressId = $order->getShippingAddress() ? $order->getShippingAddress()->getCustomerAddressId() : "";
            $shippingMethod = $order->getShippingMethod() ?? $order->getShippingAddress()->getShippingMethod();

            $command = $transactionParams["Command"] ?? PaymentInterface::EBIZCHARGE_COMMAND_SALE;

            /** @var $ebizOption */
            $ebizOption = $additionalInformation["ebzc_option"];
            $cardOptionType = "";
            $bankOptionType = "";

            $paymentMethodId = $itemParams["method_id"] ?? "0";
            $paymentSavedMethod = $this->_customerFactory->create()
                ->getSavedPaymentMethodById($paymentMethodId, $customerToken);
            $paymentMethodName = $paymentSavedMethod["MethodName"];

            if (strtolower($command) === strtolower(SoapApiModelInterface::ACH)) {
                $ebizOptionType = SoapApiModelInterface::ACH;

                if ($ebizOption === "saved") {
                    $bankOptionType = "bank_saved";
                } else {
                    $bankOptionType = "bank_new";
                }
            } else {
                $ebizOptionType = SoapApiModelInterface::BIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD_SAVED;
                if ($ebizOption === "saved") {
                    $cardOptionType = "card_saved";
                } else {
                    $cardOptionType = "card_new";
                }
            }
            $couponCode = isset($itemParams["coupon_code"]) ? $itemParams["coupon_code"] : '';

            $isRecurredIndefinite = $infoBuyRequest["rec_indefinitely"] ?? 0;
            $recurringStartDate = isset($infoBuyRequest["sdate"]) ? $infoBuyRequest["sdate"] : '';
            $recurringEndDate = isset($infoBuyRequest["edate"]) ? $infoBuyRequest["edate"] : '';

            if ($isRecurredIndefinite) {
                $recurringEndDate = $this->getDefaultIndefinitRecurringDate($recurringEndDate);
            }

            $shippingPrice = $orderItem->getEcShippingAmount() ?? $order->getShippingAmount();
            $itemPrice = $orderItem->getPrice() ? (float)$orderItem->getPrice() : (float)$orderItem->getOriginalPrice();
            $taxAmount = $orderItem->getTaxAmount() ?? 0;
            $taxAmount = (float)$taxAmount;
            $taxPercentage = $orderItem->getTaxPercent() ?? 0;
            $taxPercent = (float)$taxPercentage;
            $grandTotalInclTax = $orderItem->getRowTotalInclTax() ?? 0;
            $rowSubtotal = $orderItem->getRowTotal() ?? 0;
            $surchargeAmount = $orderItem->getEcSurchargeAmount() ?? 0;
            $surchargeAmount = (float)$surchargeAmount;
            $grandTotalInclTax = (float)$grandTotalInclTax + (float)$shippingPrice;
            $itemId = $orderItem->getId() ?? 0;
            $quoteId = $orderItem->getQuoteId() ?? $quote->getId();

            if (!(float)$orderItem->getPrice()) {
                $surchargeSettings = $this->_soapApiModel->getSurchargeSettings($order->getStoreId());

                $surchargePercentage = isset($surchargeSettings["surchargePercentage"]) ? (float)$surchargeSettings["surchargePercentage"] : 0;
                $rowSubtotal = $itemPrice * (float)$orderedQty;
                $itemPriceInclTax = $itemPrice + ((float)$taxPercent) / 100 * $itemPrice;
                $taxAmount = ((float)$taxPercent) / 100 * $rowSubtotal;
                $rowSubTotalInclTax = $rowSubtotal + $taxAmount;
                $rowGrandTotalInclTax = $rowSubTotalInclTax + $shippingPrice;
                $grandTotalInclTax = $rowGrandTotalInclTax;
                $surchargeAmount = round(($surchargePercentage / 100 * $rowGrandTotalInclTax), 2);
                $orderItem->setPrice($itemPrice);
                $orderItem->setBasePrice($itemPrice);
                $orderItem->setPriceInclTax($itemPriceInclTax);
                $orderItem->setBasePriceInclTax($itemPriceInclTax);

                $orderItem->setRowTotalInclTax($grandTotalInclTax);
                $orderItem->setBaseRowTotalInclTax($grandTotalInclTax);
                $orderItem->setRowTotal($rowSubTotalInclTax);
                $orderItem->setBaseRowTotal($rowSubTotalInclTax);
                $orderItem->setEcSurchargeAmount($surchargeAmount);
                $orderItem->setTaxAmount($taxAmount);
                $orderItem->setBaseTaxAmount($taxAmount);
                $orderItem->setEcShippingAmount($shippingPrice);
            }

            $rowTotal = $orderItem->getRowTotalInclTax() ?? 0;
            $subTotal = $orderItem->getRowTotal() ?? 0;
            $productType = $orderItem->getProductType() ?? "simple";
            $isfreeShipping = $orderItem->getFreeShipping() ?? false;
            $itemSku = $orderItem->getSku() ?? "";

            $paymentParams = [
                "ebiz_option" => $ebizOption,
                "ebzc_option_type" => $ebizOptionType,
                "card_option_type" => $cardOptionType,
                "bank_option_type" => $bankOptionType,
                "saved_cards_method_id" => $paymentMethodId,
                "method_id" => $paymentMethodId,
                "payment_method_name" => $paymentMethodName,
            ];
            if (strtolower($ebizOption) === strtolower(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_NEW)) {
                $paymentParams = $additionalInformation;
            }
            /** @var  $recurringParams */
            $recurringParams = [
                "payment_method_name" => $paymentMethodName,
                "product_id" => $orderItem->getProductId(),
                "qty" => $orderedQty,
                "order_id" => $order->getIncrementId() ?? $order->getEntityId(),
                "increment_id" => $order->getIncrementId() ?? $order->getEntityId(),
                "customer_select" => $customer->getEmail() ?? "",
                "customer_id" => $customerId,
                "addressBill" => $billingAddressId,
                "addressShip" => $shippingAddressId,
                "schedule" => $infoBuyRequest["rec_frequency"] ?? RecurringInterface::RECURRING_FREQUENCIES_DAILY,
                "start_date" => $recurringStartDate,
                "expire_date" => $recurringEndDate,
                "discount" => (float)$orderItem->getDiscountAmount(),
                "rec_indefinitely" => $isRecurredIndefinite,
                "coupon_code" => $couponCode,
                "shipping_method" => $shippingMethod,
                "shipping_amount" => (float)$shippingPrice,
                "price" => (float)$itemPrice,
                "tax_amount" => (float)$taxAmount,
                "item_id" => $itemId,
                "surcharge_amount" => (float)$surchargeAmount,
                "subtotal" => (float)$subTotal,
                "quote_id" => $quoteId,
                "tax_percent" => (float)$taxPercent,
                "product_type" => $productType,
                "is_free_shipping" => $isfreeShipping,
                "item_sku" => $itemSku,
                "row_total" => (float)$grandTotalInclTax,
                "payment" => $paymentParams,
                "method_id" => $paymentMethodId,
                "saved_cards_method_id" => $paymentMethodId
            ];
        }

        return $recurringParams;
    }

    /**
     * Get Qty Ordered
     *
     * @return string
     */
    public function getQtyOrdered(): string
    {
        return $this->getData(RecurringInterface::QTY_ORDERED);
    }

    /**
     * Get Shipping Method
     *
     * @return array|mixed|null
     */
    public function getShippingMethod()
    {
        return $this->getData(RecurringInterface::SHIPPING_METHOD);
    }

    /**
     * @param string $startDate
     * @param $limitDays
     * @return string
     * @throws Exception
     */
    public function getDefaultIndefinitRecurringDate(string $startDate = '', $limitDays = null): string
    {
        return $this->prepareIndefiniteRecurringDate($startDate, $limitDays);
    }

    /**
     * @param string|null $startDate
     * @param $limitInDays
     * @return string
     * @throws Exception
     */
    public function prepareIndefiniteRecurringDate(string $startDate = null, $limitInDays = null): string
    {
        $defaultIndefiniteLimit = (float)RecurringInterface::DEFAULT_INDEFINITE_RECURRING_LIMIT;
        $startDate = $startDate ?? $this->_soapApiModel->getCurrentDateTime("Y-m-d H:i:s");
        $dateObject = new DateTime($startDate);
        $endLimitInDays = $limitInDays ?? $defaultIndefiniteLimit * 365;
        $limitInDays = (float)$endLimitInDays;
        $dateObject->modify("+" . $limitInDays . " days");
        return $dateObject->format("Y-m-d");
    }

    /**
     * @param string $format
     * @return string
     * @throws Exception
     */
    public function getCurrentDateTime(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->_soapApiModel->getCurrentDateTime($format);
    }

    /**
     * Get Shipping Amount
     *
     * @return float
     */
    public function getShippingAmount(): float
    {
        return (float)$this->getData(RecurringInterface::SHIPPING_AMOUNT);
    }

    /**
     * Get Shipping Amount
     *
     * @return string
     */
    public function getTaxAmount(): float
    {
        return (float)$this->getData(RecurringInterface::TAX_AMOUNT);
    }

    /**
     * @return float
     */
    public function getQuoteId(): float
    {
        return (float)$this->getData(RecurringInterface::EB_REC_QUOTE_ID);
    }

    /**
     * Get Store Id
     *
     * @return int
     */
    public function getStoreId(): int
    {
        return $this->getData(RecurringInterface::EB_REC_STORE_ID);
    }

    /**
     * Set Tax Amount
     *
     * @param string $taxAmount
     * @return RecurringInterface
     */
    public function setTaxAmount(string $taxAmount): RecurringInterface
    {
        return $this->setData(RecurringInterface::TAX_AMOUNT, $taxAmount);
    }

    /**
     * Add Recurring Order
     *
     * @param array $recurringOrderParams
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addRecurringOrder(array $recurringOrderParams = []): array
    {
        /** @var $recurringOrderResponse */
        $recurringOrderResponse = [
            'error' => true,
            'message' => '',
            'response' => [
                'recurring_id' => 0
            ]
        ];

        try {
            $store = $this->_configModel->getStore();
            $storeId = $store->getId();
            $paymentMethodParams = isset($recurringOrderParams["payment"]) ? $recurringOrderParams["payment"] : [];

            $surchargeSettings = $this->_customerFactory->create()->getSurchargeSettings($storeId);
            $customerId = $recurringOrderParams['customer_id'] ?? null;
            $ebizRecurringId = $recurringOrderParams['recurring_id'] ?? 0;
            $schedule = isset($recurringOrderParams['schedule']) ? $recurringOrderParams['schedule'] : '';
            $recIndefinitely = isset($recurringOrderParams['rec_indefinitely']) &&
            $recurringOrderParams['rec_indefinitely'] !== 0 ? 1 : 0;
            $shippingAmount = isset($recurringOrderParams["shipping_amount"]) ? (float)$recurringOrderParams["shipping_amount"] : 0;
            $surchargeAmount = isset($recurringOrderParams["surcharge_amount"]) ? (float)$recurringOrderParams["surcharge_amount"] : 0;
            $surchargePercentage = isset($recurringOrderParams["surcharge_percentage"]) ? (float)$recurringOrderParams["surcharge_percentage"] : 0;
            $taxAmount = isset($recurringOrderParams["tax_amount"]) ? (float)$recurringOrderParams["tax_amount"] : 0;
            $grandTotal = isset($recurringOrderParams["grand_total"]) ? (float)$recurringOrderParams["grand_total"] : 0;

            $recurringStartDateParam = $this->_soapApiModel->formateDateTime(
                $recurringOrderParams['start_date'],
                'Y-m-d H:i:s'
            ) ?? '';
            $recurringStartDate = $this->_soapApiModel->formatTZDateTime($recurringStartDateParam);
            $recurringNextDate = $this->prepareNextRecurringDate($recurringStartDate, $schedule);
            $expireDate = isset($recurringOrderParams['expire_date']) ? $recurringOrderParams['expire_date'] : "";
            $recurringEndDate = $this->_soapApiModel->formateDateTime($expireDate, 'Y-m-d H:i:s') ?? "";
            $createdAt = $this->_soapApiModel->formateDateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s');

            /** recurring Indefinitely */
            if ($recIndefinitely) {
                $recurringEndDate = $this->getDefaultIndefinitRecurringDate($recurringStartDateParam);
            }

            /** @var  $recurringEndDate */
            $recurringEndDate = $this->_soapApiModel->formatTZDateTime($recurringEndDate);

            /** @var  $customer */
            $customer = $this->_customerFactory->create()->load($customerId);
            $customerStoreId = $customer->getStoreId();
            $customerToken = $customer->getEcCustToken();
            $customerInternalId = $customer->getEcCustInternalId();

            $orderStatus = '';
            $recurringRemarks = 'Recurring Item has been added ';
            $incrementId = $recurringOrderParams["increment_id"] ?? isset($recurringOrderParams["quote_id"]) ? $recurringOrderParams["quote_id"] : 0;

            if (empty($incrementId)) {
                $incrementId = $recurringOrderParams["increment_id"] ?? '';
                $order = $this->_orderFactory->create()->loadByIncrementId($incrementId);
                if ($order) {
                    $orderStatus = $order->getStatus();
                }
            }

            $shippingMethod = $recurringOrderParams['shipping_method'] ?? null;
            $billingAddressId = $recurringOrderParams['addressBill'] ?? 0;
            $shippingAddressId = $recurringOrderParams['addressShip'] ?? $billingAddressId;
            $productId = $recurringOrderParams['product_id'] ?? null;
            $discount = $recurringOrderParams['discount'] ?? 0;
            $couponCode = $recurringOrderParams['coupon_code'] ?? 0;

            /** @var  $productFactory */
            $productModel = $this->_productFactory->create()->load($productId);

            $productPrice = $productModel->getPriceInfo()->getPrice('final_price');
            $productPrice = $productPrice->getAmount()->getBaseAmount();

            /** @var apply rules $productPrice */
            $productPrice = $productModel->getPriceAfterApplyRules($productModel, $productPrice);
            $ebOrderedProductFinalPrice = $productPrice;
            $productName = $productModel->getName() ?? "";

            $orderedQty = isset($recurringOrderParams['qty']) ? $recurringOrderParams['qty'] : 1;
            $itemPrice = isset($recurringOrderParams['price']) ? $recurringOrderParams['price'] : $productPrice;
            $rowSubtotal = (float)$orderedQty * (float)$itemPrice;

            $taxAmount = $this->getTaxAmountByProductId($productId, $customerId, $orderedQty);
            $taxAmount = number_format((float)$taxAmount, 2, '.', '');
            $rowSubtotal = $rowSubtotal + (float)$taxAmount;
            $rowGrandTotal = $rowSubtotal + $shippingAmount;

            if (isset($recurringOrderParams["tax_amount"])) {
                $taxAmount = isset($recurringOrderParams["tax_amount"]) ? (float)$recurringOrderParams["tax_amount"] : 0;
            } else {

                if ($grandTotal > 0) {
                    $rowGrandTotal = (float)$grandTotal + (float)$taxAmount;
                }
            }

            if (isset($surchargeSettings["surchargeEnabled"])
                && $surchargeSettings["surchargeEnabled"] === true
                && $surchargeAmount === 0
            ) {
                $surchargePercentage = isset($surchargeSettings["surchargePercentage"]) && $surchargeSettings["surchargePercentage"];
                $surchargeAmount = (float)$surchargePercentage / 100 * $rowGrandTotal;
                $surchargeAmount = number_format((float)$surchargeAmount, 2, '.', '');
            }

            $recurringQuoteId = isset($recurringOrderParams['quote_id']) ? $recurringOrderParams['quote_id'] : 0;
            $rowSubtotal = isset($recurringOrderParams['subtotal']) ? $recurringOrderParams['subtotal'] : $rowSubtotal;
            $orderAmount = isset($recurringOrderParams['row_total']) ? $recurringOrderParams['row_total'] : $rowGrandTotal;
            $taxAmount = isset($recurringOrderParams['tax_amount']) ? $recurringOrderParams['tax_amount'] : $taxAmount;
            $shippingAmount = isset($recurringOrderParams['shipping_amount']) ? $recurringOrderParams['shipping_amount'] : $shippingAmount;
            $surchargeAmount = isset($recurringOrderParams['surcharge_amount']) ? $recurringOrderParams['surcharge_amount'] : $surchargeAmount;
            $grandTotal = $orderAmount;
            $recurringOrderParams['payment']['save_card_anyway'] = $recurringOrderParams['save_card_anyway'] ?? 0;

            /** if Payment Method Id is given */
            /** @var  $paymentMethodId */
            $paymentMethodId = $paymentMethodParams['method_id'] ?? '';
            $paymentMethodName = $paymentMethodParams['payment_method_name'] ?? '';

            if (!empty($paymentMethodId)) {
                $methodData = explode("||", $paymentMethodId);
                if (isset($methodData[0])) {
                    $paymentMethodId = $methodData[0];
                    $paymentMethodParams["method_id"] = $paymentMethodId;
                }
                if (isset($methodData[1])) {
                    $paymentMethodName = $methodData[1];
                    $paymentMethodParams["payment_method_name"] = $paymentMethodName;
                }
            }

            $paymentMethodParams['payment'] = $paymentMethodParams;
            $paymentMethodParams["avs_street"] = isset($paymentMethodParams["ebzc_avs_street"]) ? $paymentMethodParams["ebzc_avs_street"] : $paymentMethodParams["avs_street"] ?? "";
            $paymentMethodParams["avs_zip"] = isset($paymentMethodParams["ebzc_avs_zip"]) ? $paymentMethodParams["ebzc_avs_zip"] : $paymentMethodParams["avs_zip"] ?? "";
            $paymentMethodParams['payment']["avs_street"] = $paymentMethodParams["avs_street"] ?? "";
            $paymentMethodParams['payment']["avs_zip"] = $paymentMethodParams["avs_zip"] ?? "";
            $paymentOptionType = isset($paymentMethodParams["ebzc_option_type"]) ? $paymentMethodParams["ebzc_option_type"] : "";
            $paymentOption = isset($paymentMethodParams["ebzc_option"]) ? $paymentMethodParams["ebzc_option"] : "";


            /** if payment Method id is given */
            if (empty($paymentMethodName) || empty($paymentMethodId)) {

                /** Add new Form Payment Method */
                if (!empty($paymentMethodParams['payment']['ebzc_option']) || !empty($paymentMethodParams['payment']['ebiz_option'])) {
                    $newCardOptions = [
                        PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_NEW,
                        PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_CARD_NEW
                    ];
                    /** in case of Credit Cards */
                    if (strtolower($paymentOptionType) === strtolower(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_CREDIT_CARD)
                        && in_array($paymentOption, $newCardOptions, true)
                    ) {
                        $paymentMethodFormParams = $this->_customerFactory->create()
                            ->prepareParamsForPaymentMethod($customerId, $paymentMethodParams);

                        $creditCardPaymentMethodResponse = $this->_customerFactory->create()
                            ->addNewPaymentMethod($customerId, $paymentMethodFormParams);

                        list($paymentMethodId, $paymentMethodName) = [
                            $creditCardPaymentMethodResponse['payment_method_id'],
                            $creditCardPaymentMethodResponse['response']['method_name']
                        ];
                    } /** in case of ACH */
                    elseif (strtolower($paymentOptionType) === strtolower(PaymentInterface::EBIZCHARGE_METHOD_TYPE_ACH) &&
                        in_array($paymentOption, $newCardOptions, true)) {
                        $customerBankAccountParams = $this->_customerFactory->create()
                            ->prepareParamsForBankAccount($customerId, $paymentMethodParams);
                        $paymentMethodResponse = $this->_customerFactory->create()
                            ->addCustomerBankAccount($customer, $customerBankAccountParams);

                        list($paymentMethodId, $paymentMethodName) = [
                            $paymentMethodResponse['response']['bank_method_id'],
                            $paymentMethodResponse['response']['bank_method_name']
                        ];
                    }
                }
            }

            /**
             * Payment Method ID and Payment Method Name
             */
            if ($paymentMethodId && $paymentMethodName) {

                /** @var  $scheduleName */
                $scheduleName = $productId . '-' . $customerId . '-' . $schedule . '-' . $paymentMethodId;

                /** @var  $recurringGatewayParams */
                $recurringGatewayParams = [
                    'Amount' => $grandTotal,
                    'Enabled' => true,
                    'Start' => $recurringStartDate,
                    'Expire' => $recurringEndDate,
                    'Next' => $recurringNextDate,
                    'Schedule' => $schedule,
                    'ScheduleName' => $scheduleName,
                    'ReceiptNote' => 'Ordered Item [' . $productId . '-' . $productName . '] recurring payment added.',
                    'ReceiptTemplateName' => false,
                    'SendCustomerReceipt' => true
                ];

                /** @var  $addRecurrParameters */
                $addRecurrParameters = [
                    'securityToken' => $this->_soapApiModel->getUeSecurityToken(),
                    'customerInternalId' => $customerInternalId,
                    'paymentMethodProfileId' => $paymentMethodId,
                    'recurringBilling' => $recurringGatewayParams
                ];

                /** @var  $transaction to Ebizcharge Gateway $transaction */
                $transaction = $this->_soapApiModel->getClient()->ScheduleRecurringPayment($addRecurrParameters);
                $scheduledPaymentInternalId = $transaction->ScheduleRecurringPaymentResult;
                $recurringId = 0;

                /** Scheduled Payment Internal Id */
                if (!empty($scheduledPaymentInternalId)) {
                    $futureRecurringDates = $this->_soapApiModel->getRecurringScheduledDates($scheduledPaymentInternalId);
                    // phpcs:ignore
                    $recurringDatesSerialize = serialize($futureRecurringDates);
                    $ebzRecurringTotal = count($futureRecurringDates);

                    $paymentMethodName = $this->_customerFactory->create()
                        ->formatPaymentMethodName($paymentMethodName);

                    $recurringStartDate = str_replace(["T00:00:00"], [""], $recurringStartDate);
                    $recurringEndDate = str_replace(["T00:00:00"], [""], $recurringEndDate);
                    $recurringNextDate = str_replace(["T00:00:00"], [""], $recurringNextDate);

                    /** @var $recurringOrderParams */
                    $recurringOrderParams = [
                        RecurringInterface::REC_STATUS => 0,
                        RecurringInterface::REC_INDEFINITELY => $recIndefinitely,
                        RecurringInterface::MAGE_CUST_ID => $customerId,
                        RecurringInterface::MAGE_ORDER_ID => $incrementId,
                        RecurringInterface::MAGE_ITEM_ID => $productId,
                        RecurringInterface::MAGE_PARENT_ITEM_ID => $productId,
                        RecurringInterface::MAGE_ITEM_NAME => $productName,
                        RecurringInterface::QTY_ORDERED => (float)$orderedQty,
                        RecurringInterface::EB_ORDERED_PRODUCT_FINAL_PRICE => $itemPrice,
                        RecurringInterface::EB_REC_STORE_ID => $customerStoreId,
                        RecurringInterface::EB_REC_QUOTE_ID => $recurringQuoteId,
                        RecurringInterface::EB_REC_START_DATE => $recurringStartDate,
                        RecurringInterface::EB_REC_END_DATE => $recurringEndDate,
                        RecurringInterface::EB_REC_FREQUENCY => $schedule,
                        RecurringInterface::EB_REC_METHOD_ID => $paymentMethodId,
                        RecurringInterface::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID => $scheduledPaymentInternalId,
                        RecurringInterface::EB_REC_TOTAL => $grandTotal,
                        RecurringInterface::EB_REC_PROCESSED => 0,
                        RecurringInterface::EB_REC_NEXT => $recurringNextDate,
                        RecurringInterface::EB_REC_REMAINING => $ebzRecurringTotal,
                        RecurringInterface::EB_REC_DUE_DATES => $recurringDatesSerialize,
                        RecurringInterface::BILLING_ADDRESS_ID => $billingAddressId,
                        RecurringInterface::SHIPPING_ADDRESS_ID => $shippingAddressId,
                        RecurringInterface::AMOUNT => $grandTotal,
                        RecurringInterface::SHIPPING_AMOUNT => $shippingAmount,
                        RecurringInterface::TAX_AMOUNT => $taxAmount,
                        RecurringInterface::ITEM_PRICE => $itemPrice,
                        RecurringInterface::SURCHARGE_AMOUNT => $surchargeAmount,
                        RecurringInterface::SUBTOTAL => $rowSubtotal,
                        RecurringInterface::GRAND_TOTAL => $grandTotal,
                        RecurringInterface::PAYMENT_METHOD_NAME => $paymentMethodName,
                        RecurringInterface::SHIPPING_METHOD => $shippingMethod,
                        RecurringInterface::CREATED_AT => $createdAt
                    ];

//dump($recurringOrderParams);throw new LocalizedException(__("localized exception"));
                    /** If recurring Id is not given */
                    if ($ebizRecurringId === 0) {
                        /** @var  $recurringModel */
                        $recurringModel = $this->setData($recurringOrderParams)
                            ->save();
                        /** @var $recurringId */
                        $recurringId = $recurringModel->getId();
                        $recurringModel->setRecId($recurringId)->save();
                    } else {
                        /** update Recurring Id */
                        $this->load($ebizRecurringId)->setData($recurringOrderParams)->save();
                        $this->setRecId($ebizRecurringId)->save();
                    }

                    /** Recurring Order Params */
                    $recurringOrderParams['recurring_id'] = $recurringId;

                    $createdAt = $this->_soapApiModel->formateDateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s');
                    /** Future Recurring Dates */
                    if (count($futureRecurringDates) > 0) {
                        foreach ($futureRecurringDates as $recurringDate) {
                            if ($recurringId) {
                                $recurringFutureSubscriptionParams = [
                                    FutureSubscriptionInterface::RECURRING_ID => $recurringId,
                                    FutureSubscriptionInterface::RECURRING_DATE => $recurringDate,
                                    FutureSubscriptionInterface::CUSTOMER_ID => $customerId,
                                    FutureSubscriptionInterface::STORE_ID => $customerStoreId,
                                    FutureSubscriptionInterface::ORDERED_QTY => $orderedQty,
                                    FutureSubscriptionInterface::ORDERED_PRODUCT_ID => $productId,
                                    FutureSubscriptionInterface::ORDERED_PRODUCT_FINAL_PRICE => $grandTotal,
                                    FutureSubscriptionInterface::AMOUNT => $grandTotal,
                                    FutureSubscriptionInterface::SHIPPING_AMOUNT => $shippingAmount,
                                    FutureSubscriptionInterface::TAX_AMOUNT => $taxAmount,
                                    FutureSubscriptionInterface::ITEM_PRICE => $itemPrice,
                                    FutureSubscriptionInterface::SURCHARGE_AMOUNT => $surchargeAmount,
                                    FutureSubscriptionInterface::SUBTOTAL => $rowSubtotal,
                                    FutureSubscriptionInterface::GRAND_TOTAL => $grandTotal,
                                    FutureSubscriptionInterface::COUPON_CODE => $couponCode,
                                    FutureSubscriptionInterface::DISCOUNT => $discount,
                                    FutureSubscriptionInterface::ORDERED_STATUS => $orderStatus,
                                    FutureSubscriptionInterface::REMARKS => $recurringRemarks,
                                    FutureSubscriptionInterface::CREATED_AT => $createdAt
                                ];

                                /** @var $subscriptionDates */
                                $subscriptionDates =
                                    $this->_futureSubscriptionsFactory
                                        ->create()
                                        ->addFutureSubscriptions($recurringFutureSubscriptionParams);


                                if (is_array($subscriptionDates) && isset($subscriptionDates["future_subscription_id"])) {
                                    $this->_ebizchargeLogger->addInfo(__("Success future dates for this subscribed items has been added."));
                                }

                            }
                        }
                    }
                    $recurringOrderResponse = [
                        'error' => false,
                        'message' => __('Success your recurring has been added successfully.'),
                        'response' => [
                            'recurring_id' => $recurringId
                        ]
                    ];

                    $this->_ebizchargeLogger->addInfo(__("Success your recurring has been added successfully."));
                } else {
                    $this->_ebizchargeLogger->addError(__("Error occurred during adding subscriptions."));

                    $recurringOrderResponse = [
                        'error' => true,
                        'message' => __('Error occurred during adding subscriptions .'),
                        'response' => [
                            'recurring_id' => 0
                        ]
                    ];
                }
            } else {
                $this->_ebizchargeLogger->addError(__("Error occurred during adding subscriptions no payment method found at gateway."));
                $recurringOrderResponse = [
                    'error' => true,
                    'message' => __('Error occurred during adding subscriptions no payment method found at gateway.'),
                    'response' => [
                        'recurring_id' => 0
                    ]
                ];
            }
        } catch (Exception $ex) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during adding recurring Error: " . $ex->getMessage()
            ));

            $recurringOrderResponse = [
                'error' => true,
                'message' => 'Exception occurred during adding subscription. Error: ' . $ex->getMessage(),
                'response' => [
                    'recurring_id' => 0
                ]
            ];

        }

        return $recurringOrderResponse;
    }

    /**
     * @param $startDate
     * @param $schedule
     * @return string
     * @throws Exception
     */
    public function prepareNextRecurringDate($startDate = '', $schedule = '')
    {
        /** @var $startDate */
        $startDate = date_create($startDate);

        /** @var $nextRecurringDate */
        $nextRecurringDate = date('Y-m-d');
        // $schedule = RecurringInterface::RECURRING_FREQUENCIES_SIX_MONTH;

        switch ($schedule) {
            case RecurringInterface::RECURRING_FREQUENCIES_DAILY:
                date_add($startDate, date_interval_create_from_date_string("01 days"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_WEEKLY:
                date_add($startDate, date_interval_create_from_date_string("07 days"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_BI_WEEKLY:
                date_add($startDate, date_interval_create_from_date_string("03 days"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_BI_MONTHLY:
                date_add($startDate, date_interval_create_from_date_string("02 week"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_FOUR_WEEK:
                date_add($startDate, date_interval_create_from_date_string("04 week"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_MONTHLY:
                date_add($startDate, date_interval_create_from_date_string("01 month"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_TWO_MONTH:
                date_add($startDate, date_interval_create_from_date_string("02 month"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_QUARTERLY:
                date_add($startDate, date_interval_create_from_date_string("04 month"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_THREE_MONTH:
                date_add($startDate, date_interval_create_from_date_string("03 month"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_90_DAYS:
                date_add($startDate, date_interval_create_from_date_string("90 days"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_FOUR_MONTH:
                date_add($startDate, date_interval_create_from_date_string("04 month"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_FIVE_MONTH:
                date_add($startDate, date_interval_create_from_date_string("05 month"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_SIX_MONTH:
                date_add($startDate, date_interval_create_from_date_string("06 month"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_180_DAYS:
                date_add($startDate, date_interval_create_from_date_string("180 days"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_BI_ANNUALLY:
                date_add($startDate, date_interval_create_from_date_string("181 days"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
            case RecurringInterface::RECURRING_FREQUENCIES_ANNUALLY:
                date_add($startDate, date_interval_create_from_date_string("01 year"));
                $nextRecurringDate = date_format($startDate, "Y-m-d");
                break;
        }
        /** @var $nextRecurringDate */
        $nextRecurringDate = $this->_soapApiModel->formateDateTime($nextRecurringDate, "Y-m-d H:i:s");
        /** @var  $nextRecurringDate */
        $nextRecurringDate = $this->_soapApiModel->formatTZDateTime($nextRecurringDate);

        return $nextRecurringDate;
    }

    /**
     * Get Amount
     *
     * @return float
     */
    public function getAmount(): float
    {
        return (float)$this->getData(RecurringInterface::AMOUNT);
    }

    /**
     * @param $productId
     * @param $orderQty
     * @return float|int
     */
    public function getTaxAmountByProductId($productId = 0, $customerId = 1, $orderQty = 1, $storeId = null)
    {
        $taxAmount = 0;
        $productModel = $this->_productFactory->create();
        $product = $productModel->load($productId);
        $productPrice = $product->getFinalPrice();
        $taxClassId = $product->getTaxClassId() ?? 0;
        $storeId = $storeId ? $storeId : $this->_configModel->getStore()->getId();

        if ($taxClassId) {
            $taxRate = $this->taxCalculation->getCalculatedRate($taxClassId, $customerId, $storeId);
            $taxAmount = $orderQty * $productPrice * $taxRate / 100;
        }
        return $taxAmount;
    }

    /**
     * Set Recurring Id
     *
     * @param string $rec_id
     * @return RecurringInterface
     */
    public function setRecId(string $rec_id): RecurringInterface
    {
        return $this->setData(RecurringInterface::REC_ID, $rec_id);
    }

    /**
     * Get billing Address Id
     *
     * @return int
     */
    public function getBillingAddressId(): int
    {
        return (int)$this->getData(RecurringInterface::BILLING_ADDRESS_ID);
    }

    /**
     * Search Recurring Payment
     *
     * @param string $customerId
     * @param string $scheduledPaymentInternalId
     * @param string $fromDate
     * @param string $toDate
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function searchRecurringPayment(
        string $customerId = '',
        string $scheduledPaymentInternalId = '',
        string $fromDate = '',
        string $toDate = '',
        int    $start = 0,
        int    $limit = 1000
    )
    {
        /** @var $recurringPaymentsResp */
        $recurringPaymentsResp = [
            "error" => true,
            "message" => __("Error! Could not found recurring payments from GW."),
            "status" => "error",
            "payments" => []
        ];
        /**
         * Recurring Payments Collection
         */
        $recurringPaymentsCollection = [];

        try {
            /** @var $customerId */
            $customerId = $customerId != '' ? $customerId : 0;
            $customer = $this->_customerFactory->create()->load($customerId);

            /** @var  $ebizCustomerId */
            $ebizCustomerId = $customer->getEcCustId() ? $customer->getEcCustId() : '';
            $ebCustomerInternalId = $customer->getEcCustInternalId() ? $customer->getEcCustInternalId() : '';
            $scheduledPaymentInternalId = $scheduledPaymentInternalId != '' ? $scheduledPaymentInternalId : '';

            /** @var $fromDate */
            $fromDate = $fromDate != '' ? $fromDate : $this->_soapApiModel->getDefaultFromDate(
                "-" . SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_PREVIOUSE_YEAR_RANGE . " Years"
            );
            $fromDate = $this->_soapApiModel->formatTZDateTime($fromDate);

            /** @var $toDate */
            $toDate = $toDate != '' ? $toDate : $this->_soapApiModel->getDefaultFromDate();
            $toDate = $this->_soapApiModel->formatTZDateTime($toDate);

            /** @var  $start */
            $start = $start !== 0 ? $start : SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_START_LIMIT;
            $limit = $limit !== '' ? $limit : SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT;
            $maxSize = 0;

            do {
                /* Get full schedule Payments by internal Id */
                $paymentParams = [
                    'securityToken' => $this->_soapApiModel->getUeSecurityToken(),
                    'scheduledPaymentInternalId' => $scheduledPaymentInternalId,
                    'customerId' => $ebizCustomerId,
                    'customerInternalId' => $ebCustomerInternalId,
                    'fromDateTime' => $fromDate,
                    'toDateTime' => $toDate,
                    'start' => $start,
                    'limit' => $limit,
                    'sort' => ''
                ];

                // phpcs:ignore
                echo "\n" . "\r" . "Fetching payments: start=" . $start, " maxsize=" . $maxSize, " limit=" . $limit;

                if (!$this->_soapApiModel->getClient()) {
                    $this->_ebizchargeLogger->addCritical(__(
                        "No Soap client found, kindly check your SOAP URL."
                    ));
                    continue;
                }

                $this->_ebizchargeLogger->addInfo(__("Fetching payments from (" . $start . "-" .
                    ((int)$start + (int)$limit) . ")"));

                /** @var $searchRecurringPayments */
                $searchRecurringPayments = $this->_soapApiModel->getClient()->SearchRecurringPayments($paymentParams);

                /** @var $recurringPaymentsResult */
                $recurringPaymentsResult = $searchRecurringPayments->SearchRecurringPaymentsResult;

                /** if search Payments at Ebizcharge API Gateway */
                if (!isset($recurringPaymentsResult->Payment)) {
                    $recurringPaymentsCollection = [];
                    $resultCount = 0;

                } elseif ((is_array($recurringPaymentsResult->Payment)) &&
                    (count((array)$recurringPaymentsResult->Payment)) > 1) {

                    $recurringPayments = $recurringPaymentsResult->Payment;
                    $resultCount = count($recurringPaymentsResult->Payment);
                    $recurringPaymentsCollection = array_merge($recurringPaymentsCollection, $recurringPayments);

                } else {
                    /** @var $ordersObj */
                    $recurringPayments[] = (array)$recurringPaymentsResult->Payment;
                    $recurringPaymentsCollection = array_merge($recurringPaymentsCollection, $recurringPayments);
                    $resultCount = 1;
                    $maxSize = 1;
                }

                /** result count */
                if ($resultCount < $limit) {
                    $maxSize = 1;
                }
                $start = $start + $limit;
                // echo "\n" . "start=" . $start, "maxsize=" . $maxSize, "limit=" . $limit;

            } while ($maxSize === 0);

            if (count($recurringPaymentsCollection) > 0) {
                $recurringPaymentsResp["payments"] = $recurringPaymentsCollection;
                $recurringPaymentsResp["error"] = false;
                $recurringPaymentsResp["status"] = "success";
                $recurringPaymentsResp["message"] = __("Success payments found from EbizCharge Gate Way ");
            }


        } catch (Exception $ex) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during fetching the Recurring Payment " . __METHOD__ . $ex->getMessage()
            ));

            $recurringPaymentsResp["message"] = __("Exception occurred during fetching payments error:" . $ex->getMessage());
        }
        return $recurringPaymentsResp;
    }

    /**
     * Prepare Front Subscription Params
     *
     * @param array $requestParams
     * @return array
     */
    public function prepareFrontSubscriptionParams($requestParams = [])
    {
        $subscriptionParams = [];

        $customerId = $requestParams['customer_id'] ?? '';
        $customer = $this->_customerFactory->create()->load($customerId);
        $customerInternalId = $customer->getEcCustInternalId();
        $customerToken = $customer->getEcCustToken();
        /** @var  $paymentParams */
        $paymentParams = isset($requestParams["payment"]) ? $requestParams["payment"] : [];

        /** @var  $requestItems */
        $requestItems = $requestParams["items"] ?? [];

        /** @var  $subscriptionParams */
        $subscriptionParams = $requestParams["subscription"] ?? [];
        $newPaymentMethodDetail = $subscriptionParams['recurring_new_payment_method_id'] ?? '';
        $paymentMethodType = PaymentInterface::EBIZCHARGE_METHOD_TYPE_CREDIT_CARD;
        $newPaymentMethodParams = explode("|", $newPaymentMethodDetail);
        $newPaymentMethodId = isset($newPaymentMethodParams[0]) ? $newPaymentMethodParams[0] : '';
        $newPaymentMethodName = isset($newPaymentMethodParams[1]) ? $newPaymentMethodParams[1] : '';
        $newPaymentMethodType = isset($newPaymentMethodParams[2]) ? $newPaymentMethodParams[2] : '';

        $paymentMethodOption = strtolower($paymentParams['ebiz_option']) ?? strtolower(SoapApiModelInterface::EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD);
        $paymentMethodOptionType = $paymentParams['payment_option_type'] ?? 'saved';


        $ebizCardOptionType = "card_saved";
        $ebizBankOptionType = "card_saved";

        if ($newPaymentMethodType === "") {
            $newPaymentMethodType = $paymentMethodOption;
        }

        if (strtolower($newPaymentMethodType) === strtolower(PaymentInterface::ACH)) {
            $paymentMethodType = $paymentMethodOption;
            $ebizCardOptionType = "bank_saved";
            $ebizBankOptionType = "bank_saved";
        }

        if (count($requestParams) > 0) {
            $ebizPaymentOption = $paymentMethodType;
            $ebizOptionType = $paymentMethodType;

            $requestParams["requestCC"] = 1;
            $paymentParams["ebiz_option"] = $paymentMethodOption;
            $paymentParams["ebzc_option_type"] = $paymentMethodType;
            $paymentParams["card_option_type"] = $ebizCardOptionType;
            $paymentParams["bank_option_type"] = $ebizBankOptionType;
            $requestParams["receiptnote"] = "";
            $requestParams["schedulename"] = "";

            $paymentRecurringParams = [
                "new_payment_method_name" => $newPaymentMethodName,
                "current_payment_method_name" => $paymentParams["current_payment_method_name"],
                "saved_cards_method_id" => $subscriptionParams["recurring_new_payment_method_id"] ?? '',
                "payment_method_name" => $newPaymentMethodName,
                "payment_method_id_used" => $paymentParams["payment_method_id_used"],
                "saved_cards_cc_cid" => $paymentParams["recurring_cvv2"] ?? '',
                "ebzc_new_payment_method" => $newPaymentMethodId,
                "ebiz_option" => $paymentMethodOption,
                "ebzc_option_type" => $paymentMethodOptionType,
                "card_option_type" => $paymentParams["card_option_type"],
                "bank_option_type" => $paymentParams["bank_option_type"],
            ];

            if (strtolower($newPaymentMethodType) === strtolower(PaymentInterface::ACH)) {
                $paymentRecurringParams["payment_method_name"] =
                    $subscriptionParams["recurring_new_payment_method_id"] ?? '';
            }

            if (strtolower($paymentMethodOption) ===
                strtolower(SoapApiModelInterface::EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD) &&
                strtolower($paymentMethodOptionType) === strtolower("new-cc")
            ) {

                $paymentRecurringParams['cc_exp_year'] = $paymentParams['cc_exp_year'] ?? '';
                $paymentRecurringParams['cc_exp_month'] = $paymentParams['cc_exp_month'] ?? '';
                $paymentRecurringParams['cc_number'] = $paymentParams['cc_number'] ?? '';
                $paymentRecurringParams['cc_owner'] = $paymentParams['cc_holder'] ?? ($paymentParams['cc_owner'] ?? '');
                $paymentRecurringParams['cc_type'] = $paymentParams['cc_type'] ?? '';
                $paymentRecurringParams['cc_cid'] = $paymentParams['cc_cid'] ?? '';
                $paymentRecurringParams['avs_zip'] = $paymentParams['avs_zip'] ?? '';
                $paymentRecurringParams['is_default'] = $paymentParams['is_default'] ?? 0;
                $paymentRecurringParams['avs_street'] = $paymentParams['avs_street'] ?? 0;
                $paymentRecurringParams['card_option_type'] = "card_new";
                $paymentRecurringParams['ebzc_new_payment_method'] = "credit_card";
                $paymentRecurringParams['bank_option_type'] = "";
            }

            if (strtolower($paymentMethodOption) === strtolower(PaymentInterface::ACH)
                && strtolower($paymentMethodOptionType) === strtolower("new-ach")
            ) {
                $paymentRecurringParams['cc_number_ach'] = $paymentParams['ach_number'] ?? '';
                $paymentRecurringParams['cc_owner_ach'] = $paymentParams['ach_holder'] ?? '';
                $paymentRecurringParams['cc_type_ach'] = $paymentParams['ach_type'] ?? '';
                $paymentRecurringParams['cc_routing_ach'] = $paymentParams['ach_route'] ?? '';
                $paymentRecurringParams['is_default'] = $paymentParams['is_default'] ?? 0;
                $paymentRecurringParams['bank_option_type'] = "bank_new";
                $paymentRecurringParams['ebzc_new_payment_method'] = "ACH";
                $paymentRecurringParams['card_option_type'] = "";
            }

            /** Items Params */
            $itemsParams = [];

            /** Request Items  */
            if (count($requestItems) > 0) {
                foreach ($requestItems as $itemId => $requestItem) {
                    $itemsParams[$itemId] = [
                        "product_id" => $requestItem["product_id"],
                        "product_amount" => $requestItem["product_amount"],
                        "product_sku" => $requestItem["product_sku"],
                        "product_name" => $requestItem["product_name"],
                        "product_price" => $requestItem["item_price"],
                        "item_price" => $requestItem["item_price"],
                        "tax_amount" => $requestItem["tax_amount"],
                        "surcharge_amount" => $requestItem["surcharge_amount"],
                        "shipping_amount" => $requestItem["shipping_amount"],
                        "sub_total" => $requestItem["sub_total"],
                        "grand_total" => $requestItem["grand_total"],
                        "coupon_code" => $requestItem["coupon_code"],
                        "discount_amount" => $requestItem["discount_amount"],
                        "cart_rule_id" => $requestItem["cart_rule_id"],
                        "product_rule_id" => $requestItem["product_rule_id"],
                        "order_qty" => $requestItem["order_qty"]
                    ];
                }
            }

            $requestParams["addresBill"] = $requestParams["addresBill"] ?? $customer->getDefaultBilling();
            $requestParams["addressShip"] = $requestParams["addressShip"] ?? $customer->getDefaultShipping();

            /** @var  $subscriptionParams */
            $subscriptionParams = [
                "key" => $requestParams["form_key"],
                "customer_id" => $requestParams["customer_id"],
                "requestCC" => $requestParams["requestCC"],
                "payment" => $paymentRecurringParams,
                "items" => $itemsParams,
                "form_key" => $requestParams["form_key"],
                "eb_rec_method_id" => $newPaymentMethodId,
                "mid" => $newPaymentMethodId,
                "mageCustId" => $customerId,
                "custIntId" => $customerInternalId,
                "schedulename" => $requestParams["schedulename"],
                "receiptnote" => $requestParams["receiptnote"],
                "payment_method_name" => $newPaymentMethodName,
                "recurring_status" => $requestParams['recurring_status'],
                "current_billing_method" => $requestParams["addresBill"],
                "current_shipping_method" => $requestParams["addressShip"],
                "schedule" => $requestParams["schedule"],
                "start_date" => $requestParams["start_date"],
                "end_date" => $requestParams["end_date"],
                "expire_date" => $requestParams["end_date"],
                "rec_indefinitely" => $requestParams["rec_indefinitely"] ?? 0,
                "recurring_id" => $requestParams["recurring_id"],
                "addresBill" => $requestParams["addresBill"],
                "addressShip" => $requestParams["addressShip"],
                "shipping_method" => $requestParams["shipping_method"],
                "method_id" => $newPaymentMethodId . "|" . $newPaymentMethodName
            ];
        }

        return $subscriptionParams;
    }

    /**
     * Get Recurring By Order Id
     *
     * @param mixed $mageOrderId
     * @return mixed
     */
    public function getRecurringByOrderId($mageOrderId)
    {
        return $this->getResource()->loadRecurringByOrderId($mageOrderId);
    }

    /**
     * Get Config Recurring Frequencies
     *
     * @param mixed $storeId
     * @return string
     */
    public function getConfigRecurringFrequencies($storeId = 0)
    {
        return $this->_configModel->getRecurringFrequencies($storeId);
    }

    /**
     * Get Frequencies
     *
     * @return array
     */
    public function getRecurringFrequencies()
    {
        $ebizchargeFrequencies = $this->_soapApiModel->getRecurringFrequenciesAtEbizCharge();
        $recurringFrequencies = [];
        if (count($ebizchargeFrequencies) > 0) {
            foreach ($ebizchargeFrequencies as $ebizchargeFrequency) {
                $recurringFrequencies[$ebizchargeFrequency["FrequencyId"]] =
                    ucwords($ebizchargeFrequency["FrequencyDescription"]);
            }
        }
        return $recurringFrequencies;
    }

    /**
     * Get Recurring Frequencies At Ebizcharge
     *
     * @return array
     */
    public function getRecurringFrequenciesAtEbizcharge()
    {
        return $this->_soapApiModel->getRecurringFrequenciesAtEbizCharge();
    }

    /**
     * Recurring Params
     *
     * @param array $recurringParams
     * @return array
     * @throws Exception
     * @phpcs:disable
     */
    public function suspendUnsubscribeRecurrings(array $recurringParams = []): array
    {
        $recurringResponse[] = [
            'error' => true,
            'message' => 'Could not modify the selected subscriptions. Please select others and try again.',
            'recurring_id' => 0,
            'recurring_products' => ""

        ];
        /** if in case of recurring params then do recurring */
        if (!is_array($recurringParams) && count($recurringParams) === 0) {
            return $recurringResponse;
        }

        $selectedRecurringParams = isset($recurringParams['selected']) ? $recurringParams['selected'] : [];
        $actionName = isset($recurringParams['actionName']) ? $recurringParams['actionName'] : '';

        if (is_array($selectedRecurringParams) && count($selectedRecurringParams) > 0) {
            foreach ($selectedRecurringParams as $selectedRecurringId) {

                try {
                    /** @var $recurring */
                    $recurring = $this->load($selectedRecurringId);
                    $recurringStatus = $recurring->getRecStatus() ?? "";
                    $recurringProduct = $recurring->getMageItemName() ?? "";

                    if ($recurringStatus === RecurringInterface::EBIZCHARGE_RECURRING_STATUS_CANCELED) {
                        $recurringResponse[$selectedRecurringId] = [
                            'error' => true,
                            'recurring_id' => $selectedRecurringId,
                            'recurring_products' => $recurringProduct,
                            'message' => 'Error occurred during updating subscriptions product :' .
                                $recurringProduct
                        ];
                        continue;
                    }

                    /** @var $paymentInternalId */
                    $paymentInternalId = $recurring->getEbRecScheduledPaymentInternalId();

                    /** @var  $ebizRecurringParams */
                    $ebizRecurringParams = [
                        'securityToken' => $this->_soapApiModel->getUeSecurityToken(),
                        'scheduledPaymentInternalId' => $paymentInternalId,
                        'statusId' => $this->_recurringStatuses[$actionName]
                    ];

                    /** @var  $ebizResponse */
                    $ebizResponse = $this->_soapApiModel->getClient()
                        ->ModifyScheduledRecurringPaymentStatus($ebizRecurringParams);
                    $ModifyScheduledRecurringPaymentStatusResult =
                        $ebizResponse->ModifyScheduledRecurringPaymentStatusResult;

                    if (!empty($ModifyScheduledRecurringPaymentStatusResult)) {

                        /** check status code  */
                        if ($ModifyScheduledRecurringPaymentStatusResult->StatusCode === 1) {
                            $updatedStatus = $recurring->setRecStatus($ebizRecurringParams['statusId'])
                                ->save();
                            if ($updatedStatus->getId()) {
                                $recurringResponse[$selectedRecurringId] = [
                                    'error' => false,
                                    'recurring_id' => $selectedRecurringId,
                                    'recurring_products' => $recurringProduct,
                                    'message' => __('Success the selected subscriptions has been updated.'),
                                ];
                                $this->_ebizchargeLogger->addInfo(__(
                                    "Success, the subscription status has been updated. recurring_id: " .
                                    $selectedRecurringId
                                ));
                            }
                        } else {
                            $this->_ebizchargeLogger->addError(__(
                                "Error occurred during update of the status of recurring product: " . $recurringProduct
                            ));
                            $recurringResponse[$selectedRecurringId] = [
                                'error' => true,
                                'recurring_id' => $selectedRecurringId,
                                'recurring_products' => $recurringProduct,
                                'message' => 'Error occurred during updating subscriptions product :' .
                                    $recurringProduct
                            ];
                        }
                    }

                } catch (SoapFault $soapFault) {
                    $this->_ebizchargeLogger->addCritical(__(
                        'Exception occurred during change the status of recurring subscriptions. ' .
                        $soapFault->getMessage()
                    ));
                    $recurringResponse[$selectedRecurringId] = [
                        'error' => true,
                        'message' => __('Error occurred during modification of the selected subscription. ' . $soapFault->getMessage()),
                        'recurring_id' => $selectedRecurringId,
                        'recurring_products' => $recurringProduct,

                    ];
                }
            }
        }

        return $recurringResponse;
    }

    /**
     * Get Recurring Status
     *
     * @return int
     */
    public function getRecStatus(): int
    {
        return (int)$this->getData(RecurringInterface::REC_STATUS);
    }
    // phpcs:enable

    /**
     * Get Mage Item Name
     *
     * @return string
     */
    public function getMageItemName(): string
    {
        return $this->getData(RecurringInterface::MAGE_ITEM_NAME);
    }

    /**
     * Get Recurring Scheduled Payment Internal Id
     *
     * @return string
     */
    public function getEbRecScheduledPaymentInternalId(): string
    {
        return $this->getData(RecurringInterface::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID);
    }
    // phpcs:enable

    /**
     * Update Recurrings
     *
     * @param array $recurringOrderParams
     * @return array
     * @phpcs:disable
     */
    public function updateRecurrings(array $recurringOrderParams = []): array
    {
        $recurringResponse = [
            'error' => true,
            'message' => 'Error occurred during updating your subscriptions.',
            'response' => [
                'recurring_id' => 0
            ]
        ];
        $paymentMethodResponse = [];

        /** temp working yet */
        if (!is_array($recurringOrderParams) && count($recurringOrderParams) === 0) {
            return $recurringResponse;
        }

        try {
            $schedule = $recurringOrderParams['schedule'];
            $recIndefinitely = isset($recurringOrderParams['rec_indefinitely']) &&
            (int)$recurringOrderParams['rec_indefinitely'] === 1 ? 1 : 0;
            $recurringStartDateParam = $this->_soapApiModel->formateDateTime(
                $recurringOrderParams['start_date'],
                'Y-m-d H:i:s'
            ) ?? '';
            $recurringStartDate = $this->_soapApiModel->formatTZDateTime($recurringStartDateParam);

            /** recurring Indefinitely */
            if ($recIndefinitely) {
                $recurringEndDate = $this->getDefaultIndefinitRecurringDate($recurringStartDateParam);
                $recurringOrderParams['expire_date'] = $recurringEndDate;
            } else {
                $recurringEndDate = $this->_soapApiModel->formateDateTime(
                    $recurringOrderParams['expire_date'] = isset($recurringOrderParams['expire_date']) ? $recurringOrderParams['expire_date'] : "",
                    'Y-m-d H:i:s'
                ) ?? '';
            }
            $recurringEndDate = $this->_soapApiModel->formatTZDateTime($recurringEndDate);


            if ($recurringEndDate < $recurringStartDate) {
                // phpcs:ignore
                $recurringResponse["message"] = __("Error occurred as expiry date is lesser than the start recurring date, please correct and try again.");
                return $recurringResponse;
            }

            $customerId = $recurringOrderParams['customer_id'];
            $ebizRecurringId = isset($recurringOrderParams['recurring_id']) ? $recurringOrderParams['recurring_id'] : 0;

            if (!$customerId || !$ebizRecurringId) {
                $recurringResponse["message"] = __("Some thing wrong happened, please correct and try again.");
                return $recurringResponse;
            }

            $currentRecurrings = $this->load($ebizRecurringId);
            $paymentMethodId = $currentRecurrings->getEbRecMethodId();
            $paymentMethodName = $currentRecurrings->getPaymentMethodName();

            $recurringStatus = 0;

            /** @var  $customer */
            $customer = $this->_customerFactory->create()->load($customerId);
            $customerStoreId = $customer->getStoreId();
            $customerToken = $customer->getEcCustToken();
            $customerInternalId = $customer->getEcCustInternalId();

            $orderId = isset($recurringOrderParams["mageOrderId"]) ? $recurringOrderParams["mageOrderId"] : "0";
            $orderStatus = '';
            $recurringRemarks = 'Recurring Item has been added ';

            if ($orderId) {
                $order = $this->_orderFactory->create()->loadByIncrementId($orderId);
                $orderStatus = $order->getStatus();
            }

            $shippingMethod = isset($recurringOrderParams['shipping_method']) ? $recurringOrderParams['shipping_method'] : "";
            $billingAddressId = $recurringOrderParams['addresBill'] ?? 0;
            $shippingAddressId = $recurringOrderParams['addressShip'] ?? 0;

            $recurringOrderParams['payment']['save_card_anyway'] = $recurringOrderParams['save_card_anyway'] ?? 0;

            /** @var $paymentFormParams */
            $paymentFormParams = isset($recurringOrderParams['payment']) ? $recurringOrderParams['payment'] : [];


            /** Add new Form Payment Method */
            if (!empty($recurringOrderParams['payment']['ebzc_option']) &&
                isset($recurringOrderParams['payment']['ebzc_new_payment_method'])) {
                $cardOptions = [
                    "card_new", "new", "new-cc", "credit_card"
                ];

                /** in case of Credit Cards */
                if (in_array($recurringOrderParams['payment']['ebzc_option'], $cardOptions, true) &&
                    in_array($recurringOrderParams['payment']['card_option_type'], $cardOptions, true) &&
                    in_array($recurringOrderParams['payment']['ebzc_option_type'], $cardOptions, true)
                ) {

                    $paymentMethodParams = $this->_customerFactory->create()
                        ->prepareParamsForPaymentMethod($customerId, $paymentFormParams);

                    if ($this->_customerSession->getPciAddNewMethodResponse()) {
                        $creditCardPaymentMethodResponse = $this->_customerSession->getPciAddNewMethodResponse();
                        $this->_customerSession->unsPciAddNewMethodResponse();
                    } else {
                        $creditCardPaymentMethodResponse = $this->_customerFactory->create()
                            ->addNewPaymentMethod($customerId, $paymentMethodParams);
                    }

                    if ($creditCardPaymentMethodResponse['error'] === false) {
                        list($paymentMethodId, $paymentMethodName) = [
                            $creditCardPaymentMethodResponse['payment_method_id'],
                            $creditCardPaymentMethodResponse['response']['method_name']
                        ];
                    } else {
                        //$recurringResponse['message'] = true;
                        $recurringResponse['message'] = isset($creditCardPaymentMethodResponse["message"]) ? $creditCardPaymentMethodResponse["message"] : "Error occurred during editing recurrings.";
                        return $recurringResponse;
                    }
                }

                $cardSavedOptions = ["saved", "card_saved"];

                if ($recurringOrderParams['payment']['ebzc_option_type'] === 'credit_card'
                    && in_array($recurringOrderParams['payment']['card_option_type'], $cardSavedOptions, true)) {
                    $paymentMethodStr = isset($recurringOrderParams['payment']['saved_cards_method_id']) ? $recurringOrderParams['payment']['saved_cards_method_id'] : $recurringOrderParams['payment']['method_id'];
                    $paymentMethodIds = explode(
                        '|',
                        $paymentMethodStr ?? ''
                    );
                    $paymentMethodId = isset($paymentMethodIds[0]) ? $paymentMethodIds[0] : "0";
                    $savedCardCc = $recurringOrderParams['payment']['saved_cards_cc_cid'] ?? '';
                    $paymentMethodName = isset($paymentMethodIds[1]) ? $paymentMethodIds[1] : "";
                    $paymentFormParams['payment_method_name'] = isset($paymentFormParams['payment_method_name']) && !empty($paymentFormParams['payment_method_name']) ? $paymentFormParams['payment_method_name'] : $paymentMethodName;
                }

                /** in case of ACH */
                if ((strtolower($recurringOrderParams['payment']['ebzc_option_type']) === strtolower('ACH')) &&
                    ($recurringOrderParams['payment']['bank_option_type'] === 'bank_new')) {
                    if ($this->_customerSession->getPciAddNewMethodResponse()) {
                        $paymentMethodResponse = $this->_customerSession->getPciAddNewMethodResponse();
                        $paymentMethodResponse['response']['bank_method_id'] =
                            $paymentMethodResponse['payment_method_id'] ?? '';
                        $paymentMethodResponse['response']['bank_method_name'] =
                            $paymentMethodResponse['response']['method_name'] ?? '';

                        if (isset($paymentMethodResponse['payment_method_id'])) {
                            unset($paymentMethodResponse['payment_method_id']);
                        }

                        $this->_customerSession->unsPciAddNewMethodResponse();
                    } else {
                        $customerBankAccountParams = $this->_customerFactory->create()
                            ->prepareParamsForBankAccount($customerId, $paymentFormParams);
                        $paymentMethodResponse = $this->_customerFactory->create()
                            ->addCustomerBankAccount($customer, $customerBankAccountParams);
                    }

                    if ($paymentMethodResponse['error'] === false) {
                        list($paymentMethodId, $paymentMethodName) = [
                            $paymentMethodResponse['response']['bank_method_id'], $paymentMethodResponse['response']['bank_method_name']
                        ];
                    } else {
                        $recurringResponse['message'] = isset($paymentMethodResponse["message"]) ? $paymentMethodResponse["message"] : "Could not edit as no payment method found.";
                        return $recurringResponse;
                    }
                }

                if (($recurringOrderParams['payment']['ebzc_option_type'] === 'ACH') && ($recurringOrderParams['payment']['bank_option_type'] === 'bank_saved')) {
                    $paymentMethodIds = explode(
                        '|',
                        $recurringOrderParams['payment']['payment_method_name'] ?? ''
                    );
                    $paymentMethodId = $paymentMethodIds[0] ?? 0;
                    $paymentMethodName = $paymentMethodIds[1] ?? "";
                }
            }

            if (!$paymentMethodId || !$paymentMethodName) {
                $recurringResponse['true'] = true;
                $recurringResponse['message'] = isset($paymentMethodResponse["message"]) ? $paymentMethodResponse["message"] : "Could not edit as no payment method found.";
                return $recurringResponse;
            }

            /** Looping through the Products */
            $orderedItems = $recurringOrderParams['items'] ?? [];
            $createdAt = $this->_soapApiModel->formateDateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s');

            /**
             * Order Items
             */
            if (count($orderedItems) > 0) {
                foreach ($orderedItems as $orderedItem) {

                    /** @var  $productId */
                    $productId = $orderedItem['product_id'];
                    $productModel = $this->_productFactory->create()->load($productId);

                    /** @var  $productFactory */
                    $productName = isset($orderedItem['product_name']) ? $orderedItem['product_name'] : '';
                    $productSku = isset($orderedItem['product_sku']) ? $orderedItem['product_sku'] : '';
                    $discount = isset($orderedItem['discount_amount']) ? $orderedItem['discount_amount'] : 0;
                    $couponCode = isset($orderedItem['coupon_code']) ? $orderedItem['coupon_code'] : 0;
                    $productPrice = isset($orderedItem['product_price']) ? $orderedItem['product_price'] : 0;
                    $rowSubtotal = isset($orderedItem['sub_total']) ? $orderedItem['sub_total'] : 0;
                    $grandTotal = isset($orderedItem['grand_total']) ? $orderedItem['grand_total'] : 0;
                    $rowTotal = isset($orderedItem['row_total']) ? $orderedItem['row_total'] : $rowSubtotal;
                    $taxAmount = isset($orderedItem['tax_amount']) ? $orderedItem['tax_amount'] : 0;
                    $shippingAmount = isset($orderedItem['shipping_amount']) ? $orderedItem['shipping_amount'] : 0;
                    $itemPrice = isset($orderedItem['item_price']) ? $orderedItem['item_price'] : 0;
                    $surchargeAmount = isset($orderedItem['surcharge_amount']) ? $orderedItem['surcharge_amount'] : 0;
                    $cartRuleId = isset($orderedItem['cart_rule_id']) ? $orderedItem['cart_rule_id'] : 0;
                    $productRuleId = isset($orderedItem['product_rule_id']) ? $orderedItem['product_rule_id'] : 0;
                    $orderedQty = isset($orderedItem['order_qty']) ? $orderedItem['order_qty'] : 0;

                    /** @var  $scheduleName */
                    $scheduleName = $productId . '-' . $customerId . '-' . $schedule . '-' . $paymentMethodId;

                    $schedulePaymentInternalId = $currentRecurrings->getEbRecScheduledPaymentInternalId();
                    $paymentMethodProfileStatus = $currentRecurrings->getRecStatus();

                    /** @var  $recurringGatewayParams */
                    $recurringGatewayParams = [
                        'Amount' => $rowTotal,
                        'Enabled' => true,
                        'Start' => $recurringStartDate,
                        'Expire' => $recurringEndDate,
                        'Next' => $recurringEndDate,
                        'Schedule' => $schedule,
                        'ScheduleName' => $scheduleName,
                        'ReceiptNote' => 'Ordered Item [' . $productId . '-' . $productName .
                            '] recurring payment Schedule has been modified.',
                        'ReceiptTemplateName' => false,
                        'SendCustomerReceipt' => true
                    ];

                    /** @var  $modifyRecurrParameters */
                    $modifyRecurrParameters = [
                        'securityToken' => $this->_soapApiModel->getUeSecurityToken(),
                        'scheduledPaymentInternalId' => trim($schedulePaymentInternalId),
                        'recurringBilling' => $recurringGatewayParams
                    ];

                    /** @var pushing $modifyScheduled to Ebizcharge Gateway $transaction */
                    $modifyScheduled = $this->_soapApiModel->getClient()
                        ->ModifyScheduledRecurringPayment_RecurringBilling($modifyRecurrParameters);

                    if (isset($modifyScheduled->ModifyScheduledRecurringPayment_RecurringBillingResult) &&
                        $modifyScheduled->ModifyScheduledRecurringPayment_RecurringBillingResult->StatusCode == 1) {
                        $futureRecurringDates = $this->_soapApiModel->getRecurringScheduledDates(
                            $schedulePaymentInternalId
                        );
                        $recurringDatesSerialize = serialize($futureRecurringDates);
                        $ebzRecurringTotal = count($futureRecurringDates);

                        /** @var $recurringOrderParams */
                        $recurringOrderParams = [
                            RecurringInterface::ENTITY_ID => $ebizRecurringId,
                            RecurringInterface::REC_STATUS => $recurringStatus,
                            RecurringInterface::REC_INDEFINITELY => $recIndefinitely,
                            RecurringInterface::MAGE_CUST_ID => $customerId,
                            RecurringInterface::MAGE_ORDER_ID => $orderId,
                            RecurringInterface::MAGE_ITEM_ID => $productId,
                            RecurringInterface::MAGE_PARENT_ITEM_ID => $productId,
                            RecurringInterface::MAGE_ITEM_NAME => $productName,
                            RecurringInterface::EB_REC_COUPON_CODE => $couponCode,
                            RecurringInterface::EB_REC_DISCOUNT => $discount,
                            RecurringInterface::EB_REC_CART_RULE_ID => $cartRuleId,
                            RecurringInterface::EB_REC_PRODUCT_RULE_ID => $productRuleId,
                            RecurringInterface::QTY_ORDERED => $orderedQty,
                            RecurringInterface::EB_ORDERED_PRODUCT_FINAL_PRICE => $productPrice,
                            RecurringInterface::EB_REC_STORE_ID => $customerStoreId,
                            RecurringInterface::EB_REC_START_DATE => $recurringStartDate,
                            RecurringInterface::EB_REC_END_DATE => $recurringEndDate,
                            RecurringInterface::EB_REC_FREQUENCY => $schedule,
                            RecurringInterface::EB_REC_METHOD_ID => $paymentMethodId,
                            RecurringInterface::ITEM_PRICE => $itemPrice,
                            RecurringInterface::SURCHARGE_AMOUNT => $surchargeAmount,
                            RecurringInterface::TAX_AMOUNT => $taxAmount,
                            RecurringInterface::SHIPPING_AMOUNT => $shippingAmount,
                            RecurringInterface::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID => $schedulePaymentInternalId,
                            RecurringInterface::EB_REC_TOTAL => $ebzRecurringTotal,
                            RecurringInterface::EB_REC_PROCESSED => 0,
                            RecurringInterface::EB_REC_NEXT => $this->getNextRecurring($futureRecurringDates),
                            RecurringInterface::EB_REC_REMAINING => $ebzRecurringTotal,
                            RecurringInterface::EB_REC_DUE_DATES => $recurringDatesSerialize,
                            RecurringInterface::BILLING_ADDRESS_ID => $billingAddressId,
                            RecurringInterface::SHIPPING_ADDRESS_ID => $shippingAddressId,
                            RecurringInterface::AMOUNT => $rowSubtotal,
                            RecurringInterface::PAYMENT_METHOD_NAME => $paymentMethodName,
                            RecurringInterface::SHIPPING_METHOD => $shippingMethod,
                            RecurringInterface::CREATED_AT => $createdAt
                        ];

                        /** If recurring Id is not given   update Recurring Id */
                        $recurringModel = $this->load($ebizRecurringId)
                            ->setData($recurringOrderParams)
                            ->save();

                        /** Recurring Order Params */
                        $recurringOrderParams['recurring_id'] = $ebizRecurringId;

                        /** Future Recurring Dates */
                        if (count($futureRecurringDates) > 0) {
                            if ($ebizRecurringId) {
                                /** deleting the subscriptions from future subscriptions */
                                $this->_futureSubscriptionsFactory->create()->deleteByRecurringId($ebizRecurringId);
                            }

                            foreach ($futureRecurringDates as $recurringDate) {
                                if ($ebizRecurringId) {
                                    $recurringFutureSubscriptionParams = [
                                        FutureSubscriptionInterface::RECURRING_ID => $ebizRecurringId,
                                        FutureSubscriptionInterface::RECURRING_DATE => $recurringDate,
                                        FutureSubscriptionInterface::CUSTOMER_ID => $customerId,
                                        FutureSubscriptionInterface::STORE_ID => $customerStoreId,
                                        FutureSubscriptionInterface::ORDERED_QTY => $orderedQty,
                                        FutureSubscriptionInterface::ORDERED_PRODUCT_ID => $productId,
                                        FutureSubscriptionInterface::ORDERED_PRODUCT_FINAL_PRICE => $ebzRecurringTotal,
                                        FutureSubscriptionInterface::AMOUNT => $rowSubtotal,
                                        FutureSubscriptionInterface::SHIPPING_AMOUNT => $shippingAmount,
                                        FutureSubscriptionInterface::TAX_AMOUNT => $taxAmount,
                                        FutureSubscriptionInterface::ITEM_PRICE => $itemPrice,
                                        FutureSubscriptionInterface::SURCHARGE_AMOUNT => $surchargeAmount,
                                        FutureSubscriptionInterface::SUBTOTAL => $rowSubtotal,
                                        FutureSubscriptionInterface::GRAND_TOTAL => $grandTotal,
                                        FutureSubscriptionInterface::COUPON_CODE => $couponCode,
                                        FutureSubscriptionInterface::DISCOUNT => $discount,
                                        FutureSubscriptionInterface::ORDERED_STATUS => $recurringStatus,
                                        FutureSubscriptionInterface::REMARKS => $recurringRemarks,
                                        FutureSubscriptionInterface::CREATED_AT => $createdAt
                                    ];

                                    /** @var $subscriptionDates */
                                    $subscriptionDates = $this->_futureSubscriptionsFactory->create()
                                        ->addFutureSubscriptions($recurringFutureSubscriptionParams);
                                }
                            }
                        }
                        $recurringResponse = [
                            'error' => false,
                            'message' => __("Success! your subscribed item \"" . $productName .
                                "\" with ID \"" . $ebizRecurringId . "\" has been updated successfully."),
                            'response' => [
                                'recurring_id' => $ebizRecurringId
                            ]
                        ];
                        $this->_ebizchargeLogger->addInfo(__("Success! your subscribed product :\"" .
                            $productName . "\" has been updated successfully recurring ID: " . $ebizRecurringId));
                    } else {
                        $this->_ebizchargeLogger->addError(__(
                            "Unable to update and save your subscribed product: \"" . $productName .
                            "\" with recurring ID: " . $ebizRecurringId));

                        $recurringResponse = [
                            'error' => true,
                            'message' => __("Unable to update and save your subscribed product: \"" .
                                $productName . "\"  with recurring ID: " . $ebizRecurringId),
                            'response' => [
                                'recurring_id' => 0
                            ]
                        ];
                    }

                }
            }
        } catch (Exception $exception) {
            $recurringResponse = [
                'error' => true,
                'message' => "Exception occurred during updating your subscription: Exception: " .
                    $exception->getMessage(),
                'response' => [
                    'recurring_id' => 0
                ]
            ];
            $this->_ebizchargeLogger->addCritical(__("Error occurred during updating Subscriptions Error: " .
                $exception->getMessage()));

        }

        return $recurringResponse;
    }

    /**
     * Get Recurring Method Id
     *
     * @return string
     */
    public function getEbRecMethodId(): string
    {
        return $this->getData(RecurringInterface::EB_REC_METHOD_ID);
    }

    /**
     * Get Payment Method Name
     *
     * @return string
     */
    public function getPaymentMethodName(): string
    {
        return $this->getData(RecurringInterface::PAYMENT_METHOD_NAME);
    }

    /**
     * Get Next Recurring Date
     *
     * @param mixed $recurringDates
     * @return mixed|null
     */
    public function getNextRecurring($recurringDates): mixed
    {
        if (isset($recurringDates[0]) && $recurringDates[0] ==
            $this->_timezoneInterface->date()->format('Y-m-d')) {
            return $recurringDates[1] ?? null;
        } else {
            return $recurringDates[0] ?? null;
        }
    }

    /**
     * Modify Recurrings
     *
     * @param array $recurrings
     * @param int $status
     * @return array
     */
    public function modifyRecurrings($recurrings = [], $status = 0)
    {
        $recurringRespRows = [];

        if (count($recurrings) > 0) {
            foreach ($recurrings as $recurring) {
                $recurringScheduledPaymentInternalId = $recurring->getEbRecScheduledPaymentInternalId();
                $recurringResponse = $this->modifyRecurringByScheduledPaymentInternalId(
                    $recurringScheduledPaymentInternalId,
                    $status
                );

                $recurringRespRows[] = '';
            }
        }
        return $recurringRespRows;
    }


    /**
     * Modify Recurring By Scheduled Payment InternalId
     *
     * @param $scheduledPaymentInternalId
     * @param $recStatus
     * @return array
     * @throws NoSuchEntityException
     */
    public function modifyRecurringByScheduledPaymentInternalId($scheduledPaymentInternalId, $recStatus = 0): array
    {
        /** @var $recurringResponse */
        $recurringResponse = [
            'status' => 'error',
            'error' => true,
            'paymentInternalId' => $scheduledPaymentInternalId,
            'message' => __('')
        ];

        try {
            $recurringParams = [
                'securityToken' => $this->_soapApiModel->getUeSecurityToken(),
                'scheduledPaymentInternalId' => $scheduledPaymentInternalId,
                'statusId' => $recStatus,
            ];

            $recurringResult = $this->_soapApiModel->getClient()
                ->ModifyScheduledRecurringPaymentStatus($recurringParams);
            $modifyScheduledRecurringPaymentStatusResult =
                $recurringResult->ModifyScheduledRecurringPaymentStatusResult;

            if ($modifyScheduledRecurringPaymentStatusResult->StatusCode == 1) {
                try {
                    $recordToUpdate = $this->_recurringRepository->getById(
                        $scheduledPaymentInternalId,
                        RecurringInterface::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID
                    )->setRecStatus($recStatus);
                    $this->_recurringRepository->save($recordToUpdate);
                    $this->_ebizchargeLogger->addInfo(__("Recurring modifyied successfully "));

                    $recurringResponse['error'] = false;
                    $recurringResponse['status'] = 'success';
                    $recurringResponse['message'] = __('Success, the recurring has been modified successfully');
                } catch (Exception $exception) {
                    // phpcs:ignore
                    $this->_ebizchargeLogger->addCritical(__('Exception occurred during modifying the scheduled Recurring Payment Status Exception: ' . $exception->getMessage()));
                    $recurringResponse['message'] = __('Error occurred during modifying the recurrings');
                }
            }
        } catch (SoapFault $soapFault) {
            $this->_ebizchargeLogger->addCritical(__("Soap error occurred during modifying the Recurrings"));
            $recurringResponse['message'] = __('Error occurred during modifying the recurrings');
        }

        return $recurringResponse;
    }

    /**
     * @param $quote
     * @param float $surchargeAmount
     * @param float $surchargePercentage
     * @return void
     */
    public function setRecurringAndSurchargeToQuoteItem($quote = null, float $surchargeAmount = 0, float $surchargePercentage = 1): void
    {

        try {

            if ($quote) {
                $quoteGrandTotal = $quote->getGrandTotal();
                $surchargeRatio = 0;

                if ((float)$quoteGrandTotal > 0) {
                    $surchargeRatio = $surchargeAmount / $quoteGrandTotal;
                }
                if ($surchargeAmount > 0) {
                    $quote->setEcSurchargeAmount($surchargeAmount);
                    $quote->setEcSurchargePercentage($surchargePercentage);
                    $quote->setEcSurchargeIneligible($surchargeSessionData[SurchargeInterface::EBIZ_SURCHARGE_INELIGIBLE] ?? 0);
                    $quote->save();
                }
                $quoteAllItems = $quote->getAllVisibleItems() ?? [];
                $totalQuoteItems = count($quoteAllItems);
                $shippingAmount = (float)$quote->getShippingAddress()->getShippingInclTax();
                $quoteTaxAmount = (float)$quote->getShippingAddress()->getTaxAmount();
                // dump($quoteTaxAmount);

                if (count($quoteAllItems) > 0) {
                    foreach ($quoteAllItems as $quoteItem) {
                        $buyRequest = $quoteItem->getBuyRequest() ?? [];
                        $recurring = $buyRequest->getRecurring() ?? [];
                        $grandTotalWithoutSurcharge = $quoteItem->getRowTotalInclTax();
                        $taxAmount = $quoteItem->getTaxAmount() ?? 0;
                        $grandTotalWithoutSurcharge = (float)$grandTotalWithoutSurcharge + (float)$taxAmount + (float)$shippingAmount;

                        if (isset($recurring["rec_frequency"]) && !empty($recurring["rec_frequency"])) {

                            $taxPercentage = $quoteItem->getTaxPercent();
                            $qtyOrdered = $quoteItem->getQty();
                            $productPrice = $quoteItem->getProduct()->getFinalPrice();

                            $quoteItem->setPrice((string)$productPrice);
                            $quoteItem->setBasePrice((string)$productPrice);
                            //  $quoteItem->setCustomPrice((string)$productPrice);
                            // $quoteItem->setOriginalCustomPrice((string)$productPrice);

                            $productSubTotal = (float)$qtyOrdered * (float)$productPrice;
                            $taxAmount = 0;
                            $quoteTaxAmount = $quote->getShippingAddress()->getTaxAmount();

                            if ((float)$quoteTaxAmount > 0) {
                                $taxAmount = round(($productSubTotal * $taxPercentage / 100), 2);
                            }
                            $grandTotalIncludeTax = (float)$productSubTotal + (float)$taxAmount;
                            $grandTotalWithoutSurcharge = (float)$productSubTotal + (float)$taxAmount + (float)$shippingAmount;

                            $productPriceInclTax = (float)$productPrice + round(((float)$productPrice * (float)$taxPercentage / 100), 2);
                            $quoteItem->setPriceInclTax((string)$productPriceInclTax);
                            $quoteItem->setBasePriceInclTax((string)$productPriceInclTax);
                            $quoteItem->setTaxAmount((string)$taxAmount);
                            $quoteItem->setBaseTaxAmount((string)$taxAmount);
                            $quoteItem->setRowTotal((string)$productSubTotal);
                            $quoteItem->setBaseRowTotal((string)$productSubTotal);
                            $quoteItem->setRowTotal((string)$productSubTotal);
                            $quoteItem->setRowTotalInclTax((string)$grandTotalIncludeTax);
                            $quoteItem->setBaseRowTotalInclTax((string)$grandTotalIncludeTax);
                        }
                        if ((float)$surchargeAmount > 0) {
                            $surchargeAmount = round((float)$surchargeRatio * (float)$grandTotalWithoutSurcharge, 2);
                            $quoteItem->setEcSurchargeAmount((string)$surchargeAmount);
                        }

                        if ((float)$shippingAmount > 0) {
                            $quoteItem->setEcShippingAmount((string)$shippingAmount);
                        }
                        $quoteItem->save();

                    }
                }
            }

        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during setting recurring detail to quote item. error:" . $exception->getMessage()));

        }

    }

    /**
     * Get Grand Total
     *
     * @return float
     */
    public function getGrandTotal(): float
    {
        return (float)$this->getData(RecurringInterface::GRAND_TOTAL);
    }

    /**
     * Get Ordered Date
     *
     * @return array|mixed|null
     */
    public function getOrderedDate()
    {
        return $this->getData(RecurringInterface::ORDERED_DATE);
    }

    /**
     * Set Ordered Date
     *
     * @param mixed $orderedDate
     * @return Recurring
     */
    public function setOrderedDate($orderedDate)
    {
        return $this->setData(RecurringInterface::ORDERED_DATE, $orderedDate);
    }

    /**
     * Get Value
     *
     * @param string $path
     * @param string $scopeType
     * @param null|mixed $scopeCode
     * @return mixed
     */
    public function getValue($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->_configModel->getConfig($path, $scopeType, $scopeCode);
    }

    /**
     * Is set Flag
     *
     * @param string $path
     * @param string $scopeType
     * @param null|mixed $scopeCode
     * @return bool|mixed
     */
    public function isSetFlag($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->_configModel->getConfig($path, $scopeType, $scopeCode);
    }

    /**
     * Get Created At
     *
     * @return array|mixed|null
     */
    public function getCreatedAt()
    {
        return $this->getData(RecurringInterface::CREATED_AT);
    }

    /**
     * Set Created At
     *
     * @param mixed $createdAt
     * @return RecurringInterface
     */
    public function setCreatedAt($createdAt): RecurringInterface
    {
        return $this->setData(RecurringInterface::CREATED_AT, $createdAt);
    }

    /**
     * Set Updated At
     *
     * @param mixed $updatedAt
     * @return RecurringInterface
     */
    public function setUpdatedAt($updatedAt): RecurringInterface
    {
        return $this->setData(RecurringInterface::UPDATED_AT, $updatedAt);
    }

    /**
     * Get Updated At
     *
     * @return array|mixed|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(RecurringInterface::UPDATED_AT);
    }

    /**
     * @param string $surchargeAmount
     * @return RecurringInterface
     */
    public function setSurchargeAmount(string $surchargeAmount): RecurringInterface
    {
        return $this->setData(RecurringInterface::SURCHARGE_AMOUNT, $surchargeAmount);
    }

    /**
     * @param string $itemPrice
     * @return RecurringInterface
     */
    public function setItemPrice(string $itemPrice): RecurringInterface
    {
        return $this->setData(RecurringInterface::ITEM_PRICE, $itemPrice);
    }

    /**
     * @return float
     */
    public function getSurchargeAmount(): float
    {
        return (float)$this->getData(RecurringInterface::SURCHARGE_AMOUNT);
    }

    /**
     * @return float
     */
    public function getItemPrice(): float
    {
        return (float)$this->getData(RecurringInterface::ITEM_PRICE);
    }

    /**
     * @param $quoteId
     * @return RecurringInterface
     */
    public function setQuoteId($quoteId): RecurringInterface
    {
        return $this->setData(RecurringInterface::EB_REC_QUOTE_ID, $quoteId);
    }

    /**
     * @param $quoteItem
     * @return bool
     */
    public function isRecurredItem($quoteItem = null): bool
    {
        $isRecurredItem = false;
        if (is_object($quoteItem)) {
            $buyRequest = $quoteItem->getBuyRequest();
            $recurringData = $buyRequest->getRecurring() ?? [];
            if (is_array($recurringData) && count($recurringData) > 0) {
                if (isset($recurringData["rec_frequency"]) && !empty($recurringData["rec_frequency"])) {
                    $isRecurredItem = true;
                }
            }
        }

        return $isRecurredItem;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(RecurringResourceModel::class);
    }

}
