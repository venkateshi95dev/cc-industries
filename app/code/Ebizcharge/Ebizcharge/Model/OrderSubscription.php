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

use Ebizcharge\Ebizcharge\Api\Data\OrderSubscriptionInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription as OrderSubscriptionResourceModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Ebizcharge Order Model
 *
 * Class Recurring
 */
class OrderSubscription extends AbstractModel implements OrderSubscriptionInterface, IdentityInterface
{
    /**
     * @const SUBSCRIPTION_ORDERS_STATUS_SUCCESS
     */
    public const SUBSCRIPTION_ORDERS_STATUS_SUCCESS = 0;

    /**
     * Subscription Orders Failed
     *
     * @const SUBSCRIPTION_ORDERS_STATUS_FAILED
     */
    public const SUBSCRIPTION_ORDERS_STATUS_FAILED = 1;

    /**
     * Cache Tag var
     *
     * @const CACHE_TAG
     */
    public const CACHE_TAG = 'ebizcharge_Order';

    /**
     * Cache Tag var
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

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
     * OrderSubscription constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param EbizchargeLogger $ebizchargeLogger
     * @param ProductFactory $productFactory
     * @param CustomerFactory $customerFactory
     * @param OrderFactory $orderFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        EbizchargeLogger $ebizchargeLogger,
        ProductFactory $productFactory,
        CustomerFactory $customerFactory,
        OrderFactory $orderFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        /** Parent Constructor */
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;

        /** @var  _productFactory */
        $this->_productFactory = $productFactory;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _orderFactory */
        $this->_orderFactory = $orderFactory;
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->getData(self::ENTITY_ID) == null ? null : (int)$this->getData(self::ENTITY_ID);
    }

    /**
     * Add Order Subscription Logs
     *
     * @param array $orderSubscriptionParams
     * @return array
     * @throws \Exception
     */
    public function addOrderSubscriptionLogs($orderSubscriptionParams = [])
    {
        /** @var $orderParamsLogsResp */
        $orderParamsLogsResp = [
            'error' => true,
            'status' => false,
            'message' => __('Error occurred during adding subscriptions.')
        ];

        /** @var  $recurring_order_params */
        $futureOrderSubscriptionParams = $orderSubscriptionParams['recurring_order_params'];
        $futureOrderSubscriptionParams['message'] = $orderSubscriptionParams['message'];
        $futureOrderSubscriptionParams['error'] = $orderSubscriptionParams['error'];
        $futureOrderSubscriptionParams['order_number'] = $orderSubscriptionParams['new_increment_id'] ??
            $futureOrderSubscriptionParams['order_number'];

        // var_dump($orderSubscriptionParams);
        /** Order Subscription Params */
        if (!is_array($orderSubscriptionParams) && count($orderSubscriptionParams) == 0) {
            return $orderParamsLogsResp;
        }
        try {

            /** @var  $recurringOrderStatus */
            $recurringOrderStatus = self::SUBSCRIPTION_ORDERS_STATUS_FAILED;

            $orderNumber =  "*";
            if (isset($futureOrderSubscriptionParams['error']) && $futureOrderSubscriptionParams['error'] === false) {
                $recurringOrderStatus = self::SUBSCRIPTION_ORDERS_STATUS_SUCCESS;
                $orderNumber = $futureOrderSubscriptionParams['order_number'] ?? '';
            }

            $recurringOrderParams = [
                self::RECURRING_ID => $futureOrderSubscriptionParams['recurring_id'] ?? '',
                self::RECURRING_DATE => $futureOrderSubscriptionParams['recurring_date'] ?? '',
                self::REC_ORDER_ID => $orderNumber,
                self::STORE_ID => $futureOrderSubscriptionParams['store_id'] ?? '',
                self::COUPON_CODE => $futureOrderSubscriptionParams['coupon_code'] ?? '',
                self::DISCOUNT => $futureOrderSubscriptionParams['discount_amount'] ?? '',
                self::CREATED_AT => $futureOrderSubscriptionParams['created_at'] ?? date("Y-m-d H:i:s"),
                self::ORDER_DATE => $futureOrderSubscriptionParams['recurring_date'] ?? '',
                self::STATUS => $recurringOrderStatus,
                self::MESSAGE => $futureOrderSubscriptionParams['message'] ?? '',
                self::PAYMENT_METHOD_NAME => isset($futureOrderSubscriptionParams['payment_method_name']) ?
                    $futureOrderSubscriptionParams['message'] : '',
                self::SCHEDULED_PAYMENT_INTERNAL_ID =>
                    $futureOrderSubscriptionParams['ebiz_scheduled_payment_reference_id'] ?? '',
                self::SHIPPING_METHOD => $futureOrderSubscriptionParams['shipping_method'] ?? '',
                self::EBIZ_METHOD_ID => $futureOrderSubscriptionParams['payment_method_id'] ?? '',
                self::EBIZ_RECURRING_PAYMENT_ID => $futureOrderSubscriptionParams['ebiz_recurring_payment_id'] ?? '',
                self::ORDER_ENTITY_ID => $futureOrderSubscriptionParams['order_number'] ?? '',
            ];

            /** @var $orderlogsSaved */
            $orderlogsSaved = $this->setData($recurringOrderParams)->save();

            if ($orderlogsSaved->getId()) {
                $orderParamsLogsResp['error'] = false;
                $orderParamsLogsResp['message'] = __('Saved recurring order to recurring logs');
                $this->_ebizchargeLogger->addInfo(__("Saved EBizCharge Payment Gateway recurring order logs "));
            }

        } catch (\Exception $exception) {
            $orderParamsLogsResp['message'] = __("Exception occurred during adding logs Exception:" .
                $exception->getMessage());
            $this->_ebizchargeLogger->addCritical($orderParamsLogsResp['message']);
        }

        return $orderParamsLogsResp;
    }

    /**
     * Get Created At
     *
     * @return array|mixed|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set Created At
     *
     * @param mixed $createdAt
     * @return OrderSubscriptionInterface
     */
    public function setCreatedAt($createdAt):OrderSubscriptionInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get Discount
     *
     * @return array|mixed|null
     */
    public function getDiscount()
    {
        return $this->getData(self::DISCOUNT);
    }

    /**
     * Set Discount
     *
     * @param mixed $discount
     * @return OrderSubscription
     */
    public function setDiscount($discount)
    {
        return $this->setData(self::DISCOUNT, $discount);
    }

    /**
     * Get Coupon Code
     *
     * @return OrderSubscription|mixed
     */
    public function getCouponCode()
    {
        return $this->getData(self::COUPON_CODE);
    }

    /**
     * Set Coupon Code
     *
     * @param mixed $couponCode
     * @return OrderSubscription
     */
    public function setCouponCode($couponCode)
    {
        return $this->setData(self::COUPON_CODE, $couponCode);
    }

    /**
     * Get Store Id
     *
     * @return array|mixed|null
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set Store Id
     *
     * @param mixed $storeId
     * @return OrderSubscription
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get Entity Id
     *
     * @return int
     */
    public function getEntityId()
    {
        return (int)$this->getData(self::ENTITY_ID);
    }

    /**
     * Set Entity Id
     *
     * @param int $entityId
     * @return OrderSubscriptionInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get Recurring Date
     *
     * @return array|mixed|null
     */
    public function getReccurrigDate()
    {
        return $this->getData(self::RECURRING_DATE);
    }

    /**
     * Recurring Date
     *
     * @param mixed $recurringDate
     * @return OrderSubscription|mixed
     */
    public function setRecurringdate($recurringDate)
    {
        return $this->setData(self::RECURRING_DATE, $recurringDate);
    }

    /**
     * Set Recurring Id
     *
     * @param int $recurring_id
     * @return OrderSubscriptionInterface
     */
    public function setRecurringId(int $recurring_id): OrderSubscriptionInterface
    {
        return $this->setData(self::RECURRING_ID, $recurring_id);
    }

    /**
     * Get Recurring Id
     *
     * @return int
     */
    public function getRecurringId(): int
    {
        return (int)$this->getData(self::RECURRING_ID);
    }

    /**
     * Set Recurring Order Id
     *
     * @param int $rec_order_id
     * @return OrderSubscriptionInterface
     */
    public function setRecurringOrderId(int $rec_order_id): OrderSubscriptionInterface
    {
        return $this->setData(self::REC_ORDER_ID, $rec_order_id);
    }

    /**
     * Get Recurring Order Id
     *
     * @return int
     */
    public function getRecurringOrderId(): int
    {
        return (int)$this->getData(self::REC_ORDER_ID);
    }

    /**
     * Set Order Created Date
     *
     * @param mixed $created_date
     * @return OrderSubscriptionInterface
     */
    public function setOrderCreatedDate($created_date): OrderSubscriptionInterface
    {
        return $this->setData(self::CREATED_DATE, $created_date);
    }

    /**
     * Get Order Created Date
     *
     * @return array|mixed|null
     */
    public function getOrderCreatedDate()
    {
        return $this->getData(self::CREATED_DATE);
    }

    /**
     * Set Order Message
     *
     * @param mixed $message
     * @return OrderSubscriptionInterface
     */
    public function setOrderMessage($message): OrderSubscriptionInterface
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * Get Order Message
     *
     * @return string
     */
    public function getOrderMessage(): string
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * Set Order Status
     *
     * @param int $status
     * @return OrderSubscriptionInterface
     */
    public function setOrderStatus(int $status): OrderSubscriptionInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Order Status
     *
     * @return int
     */
    public function getOrderStatus(): int
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Recurring Order Date
     *
     * @param mixed $recurring_date
     * @return OrderSubscriptionInterface
     */
    public function setRecurringOrderDate($recurring_date): OrderSubscriptionInterface
    {
        return $this->setData(self::ORDER_DATE, $recurring_date);
    }

    /**
     * Get Recurring Order Date
     *
     * @return array|mixed|null
     */
    public function getRecurringOrderDate()
    {
        return $this->getData(self::ORDER_DATE);
    }

    /**
     * Set Order entity Id
     *
     * @param int $order_entity_id
     * @return OrderSubscriptionInterface
     */
    public function setOrderEntityId(int $order_entity_id): OrderSubscriptionInterface
    {
        return $this->setData(self::ORDER_ENTITY_ID, $order_entity_id);
    }

    /**
     * Get Order Entity Id
     *
     * @return int
     */
    public function getOrderEntityId(): int
    {
        return (int)$this->getData(self::ORDER_ENTITY_ID);
    }

    /**
     * Get Payment Method Name
     *
     * @return array|mixed|null
     */
    public function getPaymentMethodName()
    {
        return $this->getData(self::PAYMENT_METHOD_NAME);
    }

    /**
     * Set Payment Method Name
     *
     * @param mixed $paymentMethodName
     * @return OrderSubscription
     */
    public function setPaymentMethodName($paymentMethodName)
    {
        return $this->setData(self::PAYMENT_METHOD_NAME, $paymentMethodName);
    }

    /**
     * Get Shipping Method
     *
     * @return array|mixed|null
     */
    public function getShippingMethod()
    {
        return $this->getData(self::SHIPPING_METHOD);
    }

    /**
     * Set Shipping Method
     *
     * @param mixed $shippingMethod
     * @return OrderSubscription
     */
    public function setShippingMethod($shippingMethod)
    {
        return $this->setData(self::SHIPPING_METHOD, $shippingMethod);
    }

    /**
     * Get Scheduled Payment Internal Id
     *
     * @return array|mixed|null
     */
    public function getScheduledPaymentInternalId()
    {
        return $this->getData(self::SCHEDULED_PAYMENT_INTERNAL_ID);
    }

    /**
     * Set Schedued Payment Internal Id
     *
     * @param mixed $scheduledPaymentInternalId
     * @return OrderSubscription
     */
    public function setScheduledPaymentInternalId($scheduledPaymentInternalId)
    {
        return $this->setData(self::SCHEDULED_PAYMENT_INTERNAL_ID, $scheduledPaymentInternalId);
    }

    /**
     * Get Ebizcharge Recurring Payment Id
     *
     * @return array|mixed|null
     */
    public function getEbizRecurringPaymentId()
    {
        return $this->getData(self::EBIZ_RECURRING_PAYMENT_ID);
    }

    /**
     * Set Ebizcharge Recurring Payment Id
     *
     * @param mixed $ebizRecurringPaymentId
     * @return OrderSubscription
     */
    public function setEbizRecurringPaymentId($ebizRecurringPaymentId)
    {
        return $this->setData(self::EBIZ_RECURRING_PAYMENT_ID, $ebizRecurringPaymentId);
    }

    /**
     * Get Ebizcharge Method Id
     *
     * @return array|mixed|null
     */
    public function getEbizMethodId()
    {
        return $this->getData(self::EBIZ_METHOD_ID);
    }

    /**
     * Set Ebizcharge Method Id
     *
     * @param mixed $ebizMethodId
     * @return OrderSubscription
     */
    public function setEbizMethodId($ebizMethodId)
    {
        return $this->setData(self::EBIZ_METHOD_ID, $ebizMethodId);
    }

    /**
     * Set Updated At
     *
     * @param mixed $updatedAt
     * @return OrderSubscriptionInterface
     */
    public function setUpdatedAt($updatedAt): OrderSubscriptionInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get Updated At
     *
     * @return array|mixed|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(OrderSubscriptionResourceModel::class);
    }
}
