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

use Ebizcharge\Ebizcharge\Api\Data\PaymentHistoryInterface;
use Ebizcharge\Ebizcharge\Api\Data\SoapApiModelInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\PaymentHistory\Collection as EbizchargePaymentsHistoryCollection;
use Exception;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Payment History Model Class
 *
 * Class PaymentHistory
 */
class PaymentHistory extends AbstractModel implements PaymentHistoryInterface
{
    /**
     * @const CACHE_TAG
     */
    public const CACHE_TAG = 'ebizcharge_ebizcharge_payment_history';

    /**
     * Payment History table Name
     *
     * @const PAYMENT_HISTORY_TABLE_NAME
     */
    public const PAYMENT_HISTORY_TABLE_NAME = 'ebizcharge_recurring_payment_history';

    /**
     * Cache Tag var
     *
     * @var string
     */
    protected $_cacheTag = 'ebizcharge_ebizcharge_payment_history';

    /**
     * Event Prefix var
     *
     * @var string
     */
    protected $_eventPrefix = 'ebizcharge_ebizcharge_payment_history';

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var PaymentHistory\Collection
     */
    protected EbizchargePaymentsHistoryCollection $_ebizchargeGatewayPaymentsCollections;

    /**
     * @var TranApi
     */
    protected TranApi $_soapApiModel;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManager;

    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $_recurringFactory;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * PaymentHistory constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param EbizchargeLogger $ebizchargeLogger
     * @param EbizchargePaymentsHistoryCollection $ebizchargeGatewayPaymentsCollections
     * @param TranApi $soapApiModel
     * @param StoreManagerInterface $storeManager
     * @param RecurringFactory $recurringFactory
     * @param CustomerFactory $customerFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context                             $context,
        Registry                            $registry,
        EbizchargeLogger                    $ebizchargeLogger,
        EbizchargePaymentsHistoryCollection $ebizchargeGatewayPaymentsCollections,
        TranApi                             $soapApiModel,
        StoreManagerInterface               $storeManager,
        RecurringFactory                    $recurringFactory,
        CustomerFactory                     $customerFactory,
        AbstractResource                    $resource = null,
        AbstractDb                          $resourceCollection = null,
        array                               $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _ebizchargeGatewayPaymentsCollections */
        $this->_ebizchargeGatewayPaymentsCollections = $ebizchargeGatewayPaymentsCollections;
        /** @var _soapApiModel */
        $this->_soapApiModel = $soapApiModel;
        /** @var _storeManager */
        $this->_storeManager = $storeManager;
        /** @var _recurringFactory */
        $this->_recurringFactory = $recurringFactory;
        /** @var  _customerFactory */
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Save Ebizcharge Payment History To Local
     *
     * @return int
     * @throws Exception
     */
    public function saveEbizchargePaymentsHistoryToLocal()
    {
        /** @var $saveCollection */
        $saveCollection = 0;

        /** @var payment History Items $paymentHistoryItems */
        $paymentHistoryItems = [];

        /** @var  $paymentsHistoryCollection */
        $paymentsHistoryCollection = $this->prepareRecurringPaymentsHistory();

        /** @var  $totalTransactions */
        $totalTransactions = count($paymentsHistoryCollection);
        $transactionCounter = 0;

        if (count($paymentsHistoryCollection) > 0) {

            foreach ($paymentsHistoryCollection as $paymentItem) {
                try {
                    $transactionCounter++;

                    $recurringPaymentDetail = (array)$paymentItem->getData('rec_payment');
                    $shippingAddress = (array)$paymentItem->getData('ShippingAddress');
                    $shippingAddress = json_encode($shippingAddress);
                    $billingAddress = (array)$paymentItem->getData('BillingAddress');
                    $billingAddress = json_encode($billingAddress);

                    $checkTrace = (array)$paymentItem->getData('CheckTrace');
                    $checkTrace = json_encode($checkTrace);
                    $checkData = (array)$paymentItem->getData('CheckData');
                    $checkData = json_encode($checkData);
                    $lineItems = (array)$paymentItem->getData('LineItems');
                    $lineItems = json_encode($lineItems);
                    $paymentDetail = (array)$paymentItem->getData('Details');
                    $paymentDetail = json_encode($paymentDetail);

                    $paymentResponse = (array)$paymentItem->getData('Response');
                    $paymentResponse = json_encode($paymentResponse);
                    $cardData = (array)$paymentItem->getData('CreditCardData');
                    $cardData = json_encode($cardData);
                    $paymentResult = $paymentItem->getData('PaymentResult');
                    $avsResult = $paymentItem->getData('AvsResult');
                    $cardCodeResult = $paymentItem->getData('CardCodeResult');
                    $paymentError = $paymentItem->getData('PaymentError');
                    $paymentErrorCode = $paymentItem->getData('PaymentErrorCode');
                    $paymentStatusCode = $paymentItem->getData('StatusCode');
                    $recurringPaymentInfo = $paymentItem->getData('RecurringPaymentInfo');

                    $paymentHistoryParams = [
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_REFERENCE_NUMBER => $paymentItem->getData('refNum'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_ID => $paymentItem->getData('customerId'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_EMAIL => $paymentItem->getData('customerEmail'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_NAME => $paymentItem->getData('customerName'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_SOURCE => $paymentItem->getData('source'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_AMOUNT => $paymentItem->getData('paymentAmount'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CARD_INFO => $paymentItem->getData('cardInfo'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DATETIME => $paymentItem->getData('DateTime'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_STORE_ID => $this->getStore()->getId(),
                        self::EBIZCHARGE_PAYMENT_HISTORY_SERVER_IP => $paymentItem->getData('ServerIP'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESPONSE => $paymentResponse,
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_LINE_ITEMS => $lineItems,
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL => $paymentDetail,
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_USER => $paymentItem->getData('refNum'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CUSTOMER_ID =>
                            $paymentItem->getData('customerId'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CLIENT_IP => $paymentItem->getData('ClientIP'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CARD_DATA => $cardData,
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CHECK_TRACE => $checkTrace,
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CHECK_DATA => $checkData,
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_SHIPPING_ADDRESS => $shippingAddress,
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_BILLING_ADDRESS => $billingAddress,
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT => $paymentResult,
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_ACCOUNT_HOLDER =>
                            $paymentItem->getData('AccountHolder'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_STATUS => $paymentItem->getData('Status'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT_STATUS =>
                            $paymentItem->getData('ResultStatus'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT_CARD_INFO =>
                            $paymentItem->getData('ResultCardInfo'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_STATUS_CODE => $paymentStatusCode,
                        self::EBIZCHARGE_PAYMENT_HISTORY_AVS_RESULT => $avsResult,
                        self::EBIZCHARGE_PAYMENT_HISTORY_CARD_CODE_RESULT => $cardCodeResult,
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_ERROR => $paymentError,
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_ERROR_CODE => $paymentErrorCode,
                        self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_TRANSACTION_TYPE =>
                            $paymentItem->getData('TransactionType'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_CREATED_AT => $paymentItem->getData('DateTime'),
                        self::EBIZCHARGE_PAYMENT_HISTORY_MODIFIED_AT => $this->_soapApiModel->formateDateTime(
                            date('Y-m-d H:i:s')
                        ),
                        self::EBIZCHARGE_PAYMENT_HISTORY_RECURRING_SCHEDULED_PAYMENT_INTERNAL_ID =>
                            $recurringPaymentInfo['ScheduledPaymentInternalId'] ?? '',
                        self::EBIZCHARGE_PAYMENT_HISTORY_RECURRING_PAYMENT_INTERNAL_ID =>
                            $recurringPaymentInfo['PaymentInternalId'] ?? '',
                        self::EBIZCHARGE_PAYMENT_HISTORY_RECURRING_PAYMENT_CUSTOMER_NUMBER =>
                            $recurringPaymentInfo['CustNum'] ?? '',
                        self::EBIZCHARGE_PAYMENT_HISTORY_RECURRING_PAYMENT_METHOD_ID =>
                            $recurringPaymentInfo['PaymentMethodId'] ?? '',
                        self::EBIZCHARGE_PAYMENT_HISTORY_LAST_SYNCED_DATE_TIME =>
                            $this->_soapApiModel->formateDateTime(date('Y-m-d H:i:s')),
                        self::EBIZCHARGE_PAYMENT_HISTORY_LAST_DOWNLOADED_PAYMENTS => (int)$totalTransactions,
                        self::EBIZCHARGE_PAYMENT_HISTORY_LAST_DOWNLOADED_COUNTER => (int)$transactionCounter
                    ];

                    /** @var $paymentHistoryItem */
                    $paymentHistoryItem = $this->loadByPaymentRefNumber(
                        $paymentHistoryParams[self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_REFERENCE_NUMBER]
                    );
                    /**
                     * payment History Items
                     */
                    if (!$paymentHistoryItem->getId()) {
                        $paymentHistoryItems[] = $paymentHistoryParams;
                    } /*else {
                        printf("Already Found: %s "."\n",$paymentHistoryItem->getId());
                    }*/

                } catch (Exception $exception) {
                    $this->_ebizchargeLogger->addCritical("Exception occurred adding payment to array :" .
                        $exception->getMessage());
                    // phpcs:ignore
                   // var_dump($exception->getMessage());
                    continue;
                }
            }

            try {
                /** Saving the Payment History
                 * Items
                 */
                if (count($paymentHistoryItems) > 0) {
                    $this->getResource()->saveMultipleItems($paymentHistoryItems);
                    $saveCollection = 1;
                }

                /**
                 * Total History Items added
                 */
                // phpcs:ignore
                printf("\r" . "\n" . "Total Payment History Items To Save : %s " . "\n", count($paymentHistoryItems));

            } catch (LocalizedException $exception) {
                $this->_ebizchargeLogger->addCritical(__("Exception occurred during saving payments " .
                    $exception->getMessage()));
                // phpcs:ignore
                printf("\r" . "\n" . "Exception occurred during saving %s ", $exception->getMessage() . "\n");
            }
        }

        return $saveCollection;
    }

    /**
     * Prepare Recurring Payments History
     *
     * @return array
     * @throws Exception
     */
    public function prepareRecurringPaymentsHistory(): array
    {
        $paymentsHistoryCollection = [];
        $recPaymentCollection = [];
        /**
         * Fetching Current Date Time
         */
        $defaultBackDays = SoapApiModelInterface::DEFAULT_DAYS_PAYMENT_HISTORY_BEFORE_LISTINGS;

        $currentDateTime = $this->_soapApiModel->getCurrentDateTime('Y-m-d');
        $currentDateTimeObj = date_create($currentDateTime);
        $fromDateTime = $this->_soapApiModel->formateDateTime(date_format(date_sub(
            $currentDateTimeObj,
            date_interval_create_from_date_string($defaultBackDays . ' days')
        ), 'Y-m-d'), 'Y-m-d');
        //  $fromDateTime = "2024-04-01";
        $limit = SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT;

        /**
         * Payment Params
         */
        $paymentParams = [
            'customerId' => '',
            'scheduledPaymentInternalId' => '',
            'fromDate' => $fromDateTime,
            'toDate' => $currentDateTime,
            'start' => SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_START_LIMIT,
            'limit' => $limit
        ];

        /**
         * Fetching the Recurring Payments Only
         */
        $recurringPaymentsCollection = $this->_recurringFactory->create()->searchRecurringPayment(
            $paymentParams['customerId'],
            $paymentParams['scheduledPaymentInternalId'],
            $paymentParams['fromDate'],
            $paymentParams['toDate'],
            $paymentParams['start'],
            $paymentParams['limit']
        );

        if (count($recurringPaymentsCollection["payments"]) > 0) {
            foreach ($recurringPaymentsCollection["payments"] as $recPayment) {
                $recPayment = (array)$recPayment;
                $customerId = isset($recPayment["CustomerId"]) ? $recPayment["CustomerId"] : "";
                $paymentRefID = isset($recPayment["RefNum"]) ? $recPayment["RefNum"] : "";
                $customer = $this->_customerFactory->create()->loadByEbizCustomerId($customerId);
                if (!$customer->getId()) {
                    continue;
                }
                $recPaymentCollection[$paymentRefID] = $recPayment;
            }
        }

        if (count((array)$recPaymentCollection) === 0) {
            return $paymentsHistoryCollection;
        }

        if (count($recPaymentCollection) > 0) {
            $transactionsCollection = $this->getEbizchargePaymentsHistoryCollection(
                $paymentParams['customerId'],
                $paymentParams['fromDate'],
                $paymentParams['toDate'],
                $paymentParams['start'],
                $paymentParams['limit']
            );

            if (count($transactionsCollection) > 0) {
                foreach ($transactionsCollection as $key => $transaction) {
                    $transactionRefNo = $transaction->getData("refNum") ?? "";

                    if (array_key_exists($transactionRefNo, $recPaymentCollection)) {
                        $transaction->setData("RecurringPaymentInfo", (array)$recPaymentCollection[$transactionRefNo]);
                        $paymentsHistoryCollection[$transactionRefNo] = $transaction;
                    }
                }
            }
        }

        return $paymentsHistoryCollection;
    }

    /**
     * Get Ebizchareg Payments History Collection
     *
     * @param string $customerId
     * @param string $fromDate
     * @param string $toDate
     * @param int $startPosition
     * @param int $limit
     * @return EbizchargePaymentsHistoryCollection
     * @throws Exception
     */
    public function getEbizchargePaymentsHistoryCollection(
        $customerId = '',
        $fromDate = '',
        $toDate = '',
        $startPosition = 0,
        $limit = 1000
    )
    {
        /** @var  $ebizchargePaymentsCollection */
        $ebizchargePaymentsCollection = $this->_ebizchargeGatewayPaymentsCollections->getCollection(
            $customerId,
            $fromDate,
            $toDate,
            $startPosition,
            $limit
        );
        return $ebizchargePaymentsCollection;
    }

    /**
     * Get Store
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Load By Payment Ref Number
     *
     * @param string $paymentRefNumber
     * @return PaymentHistory
     */
    public function loadByPaymentRefNumber($paymentRefNumber = '')
    {
        $paymentHistoryItemId = $this->getResource()->loadByPaymentRefNumber($paymentRefNumber);
        return $this->load($paymentHistoryItemId);
    }

    /**
     * Get Payment Ref Number
     *
     * @return string
     */
    public function getPaymentRefNumber(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_REFERENCE_NUMBER);
    }

    /**
     * Get Customer Id
     *
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_ID);
    }

    /**
     * Get Customer Email
     *
     * @return string
     */
    public function getCustomerEmail(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_EMAIL);
    }

    /**
     * Get Customer Name
     *
     * @return string
     */
    public function getCustomerName(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_NAME);
    }

    /**
     * Get Payment Source
     *
     * @return string
     */
    public function getPaymentSource(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_SOURCE);
    }

    /**
     * Get Payment Amount
     *
     * @return string
     */
    public function getPaymentAmount(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_AMOUNT);
    }

    /**
     * Get Payment Card Info
     *
     * @return string
     */
    public function getPaymentCardInfo(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CARD_INFO);
    }

    /**
     * Get Payment Date Time
     *
     * @return string
     */
    public function getPaymentDatetime(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DATETIME);
    }

    /**
     * Get Store Id
     *
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_STORE_ID);
    }

    /**
     * Get Server Ip
     *
     * @return string
     */
    public function getServerIp(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_SERVER_IP);
    }

    /**
     * Get Payment Response
     *
     * @return string
     */
    public function getPaymentResponse(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESPONSE);
    }

    /**
     * Get Payment Line Items
     *
     * @return string
     */
    public function getPaymentLineItems(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_LINE_ITEMS);
    }

    /**
     * Get Payment Detail
     *
     * @return string
     */
    public function getPaymentDetail(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL);
    }

    /**
     * Get Payment Detail User
     *
     * @return string
     */
    public function getPaymentDetailUser(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_USER);
    }

    /**
     * Get Payment Detail Customer Id
     *
     * @return string
     */
    public function getPaymentDetailCustomerId(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CUSTOMER_ID);
    }

    /**
     * Get Payment Detail Client Ip
     *
     * @return string
     */
    public function getPaymentDetailClientIp(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CLIENT_IP);
    }

    /**
     * Get Payment Detail Card Data
     *
     * @return string
     */
    public function getPaymentDetailCardData(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CARD_DATA);
    }

    /**
     * Get Payment Check Trace
     *
     * @return string
     */
    public function getPaymentCheckTrace(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CHECK_TRACE);
    }

    /**
     * Get Payment Check Data
     *
     * @return string
     */
    public function getPaymentCheckData(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CHECK_DATA);
    }

    /**
     * Get Payment Shipping Address
     *
     * @return string
     */
    public function getPaymentShippingAddress(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_SHIPPING_ADDRESS);
    }

    /**
     * Get Payment Billing Address
     *
     * @return string
     */
    public function getPaymentBillingAddress(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_BILLING_ADDRESS);
    }

    /**
     * Get Payment Account Holder
     *
     * @return string
     */
    public function getPaymentAccountHolder(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_ACCOUNT_HOLDER);
    }

    /**
     * Get Payment Status
     *
     * @return string
     */
    public function getPaymentStatus(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_STATUS);
    }

    /**
     * Get Payment Transaction Type
     *
     * @return string
     */
    public function getPaymentTransactionType(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_TRANSACTION_TYPE);
    }

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_CREATED_AT);
    }

    /**
     * Get Modified At
     *
     * @return string
     */
    public function getModifiedAt(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_MODIFIED_AT);
    }

    /**
     * Set Payment Reference Number
     *
     * @param string $paymentRefNumber
     * @return PaymentHistoryInterface
     */
    public function setPaymentRefNumber(string $paymentRefNumber): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_REFERENCE_NUMBER, $paymentRefNumber);
    }

    /**
     * Set Customer Id
     *
     * @param string $customerId
     * @return PaymentHistoryInterface
     */
    public function setCustomerId(string $customerId): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_ID, $customerId);
    }

    /**
     * Set Customer Email
     *
     * @param string $customerEmail
     * @return PaymentHistoryInterface
     */
    public function setCustomerEmail(string $customerEmail): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_EMAIL, $customerEmail);
    }

    /**
     * Set Customer Name
     *
     * @param string $customerName
     * @return PaymentHistoryInterface
     */
    public function setCustomerName(string $customerName): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_CUSTOMER_NAME, $customerName);
    }

    /**
     * Set Payment Source
     *
     * @param string $paymentSource
     * @return PaymentHistoryInterface
     */
    public function setPaymentSource(string $paymentSource): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_SOURCE, $paymentSource);
    }

    /**
     * Set Payment Amount
     *
     * @param string $paymentAmount
     * @return PaymentHistoryInterface
     */
    public function setPaymentAmount(string $paymentAmount): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_AMOUNT, $paymentAmount);
    }

    /**
     * Set Payment Card Info
     *
     * @param string $paymentCardInfo
     * @return PaymentHistoryInterface
     */
    public function setPaymentCardInfo(string $paymentCardInfo): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CARD_INFO, $paymentCardInfo);
    }

    /**
     * Set Payment DateTime
     *
     * @param string $paymentDatetime
     * @return PaymentHistoryInterface
     */
    public function setPaymentDatetime(string $paymentDatetime): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_MODIFIED_AT, $paymentDatetime);
    }

    /**
     * Set Store Id
     *
     * @param string $storeId
     * @return PaymentHistoryInterface
     */
    public function setStoreId(string $storeId): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_STORE_ID, $storeId);
    }

    /**
     * Set Server Ip
     *
     * @param string $serverIp
     * @return PaymentHistoryInterface
     */
    public function setServerIp(string $serverIp): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_SERVER_IP, $serverIp);
    }

    /**
     * Set Payment Response
     *
     * @param string $paymentResponse
     * @return PaymentHistoryInterface
     */
    public function setPaymentResponse(string $paymentResponse): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESPONSE, $paymentResponse);
    }

    /**
     * Set Payment Line Items
     *
     * @param string $paymentLineItems
     * @return PaymentHistoryInterface
     */
    public function setPaymentLineItems(string $paymentLineItems): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_LINE_ITEMS, $paymentLineItems);
    }

    /**
     * Set Payment Detail
     *
     * @param string $paymentDetail
     * @return PaymentHistoryInterface
     */
    public function setPaymentDetail(string $paymentDetail): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL, $paymentDetail);
    }

    /**
     * Set Payment Detail User
     *
     * @param string $paymentDetailUser
     * @return PaymentHistoryInterface
     */
    public function setPaymentDetailUser(string $paymentDetailUser): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_USER, $paymentDetailUser);
    }

    /**
     * Set Payment Detail Customer Id
     *
     * @param string $detailCustomerId
     * @return PaymentHistoryInterface
     */
    public function setPaymentDetailCustomerId(string $detailCustomerId): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CUSTOMER_ID, $detailCustomerId);
    }

    /**
     * Set Payment Detail Client Ip
     *
     * @param string $paymentDetailClientIp
     * @return PaymentHistoryInterface
     */
    public function setPaymentDetailClientIp(string $paymentDetailClientIp): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CLIENT_IP, $paymentDetailClientIp);
    }

    /**
     * Set Payment Detail Card Data
     *
     * @param string $paymentDetailCardData
     * @return PaymentHistoryInterface
     */
    public function setPaymentDetailCardData(string $paymentDetailCardData): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_DETAIL_CARD_DATA, $paymentDetailCardData);
    }

    /**
     * Set Payment Check Trace
     *
     * @param string $paymentCheckTrace
     * @return PaymentHistoryInterface
     */
    public function setPaymentCheckTrace(string $paymentCheckTrace): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CHECK_TRACE, $paymentCheckTrace);
    }

    /**
     * Set Payment Check Data
     *
     * @param string $paymentCheckData
     * @return PaymentHistoryInterface
     */
    public function setPaymentCheckData(string $paymentCheckData): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_CHECK_DATA, $paymentCheckData);
    }

    /**
     * Set Payment Shipping Address
     *
     * @param string $paymentShippingAddress
     * @return PaymentHistoryInterface
     */
    public function setPaymentShippingAddress(string $paymentShippingAddress): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_SHIPPING_ADDRESS, $paymentShippingAddress);
    }

    /**
     * Set Payment Billing Address
     *
     * @param string $paymentBillingAddress
     * @return PaymentHistoryInterface
     */
    public function setPaymentBillingAddress(string $paymentBillingAddress): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_BILLING_ADDRESS, $paymentBillingAddress);
    }

    /**
     * Set Payment Account Holder
     *
     * @param string $paymentAccountHolder
     * @return PaymentHistoryInterface
     */
    public function setPaymentAccountHolder(string $paymentAccountHolder): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_ACCOUNT_HOLDER, $paymentAccountHolder);
    }

    /**
     * Set Payment Status
     *
     * @param string $paymentStatus
     * @return PaymentHistoryInterface
     */
    public function setPaymentStatus(string $paymentStatus): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_STATUS, $paymentStatus);
    }

    /**
     * Set Payment Transaction Type
     *
     * @param string $paymentTransactionType
     * @return PaymentHistoryInterface
     */
    public function setPaymentTransactionType(string $paymentTransactionType): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_TRANSACTION_TYPE, $paymentTransactionType);
    }

    /**
     * Set Created At
     *
     * @param mixed $createdAt
     * @return PaymentHistoryInterface
     */
    public function setCreatedAt($createdAt): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_CREATED_AT, $createdAt);
    }

    /**
     * Set Modified AT
     *
     * @param mixed $modifiedAt
     * @return PaymentHistoryInterface
     */
    public function setModifiedAt($modifiedAt): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_MODIFIED_AT, $modifiedAt);
    }

    /**
     * Get Payment Result
     *
     * @return string
     */
    public function getPaymentResult(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT);
    }

    /**
     * Set Payment Result
     *
     * @param string $paymentResult
     * @return PaymentHistoryInterface
     */
    public function setPaymentResult(string $paymentResult): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT, $paymentResult);
    }

    /**
     * Get Avs Result
     *
     * @return string
     */
    public function getAvsResult(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_AVS_RESULT);
    }

    /**
     * Get Card Code Result
     *
     * @return string
     */
    public function getCardCodeResult(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_CARD_CODE_RESULT);
    }

    /**
     * Get Payment Error
     *
     * @return string
     */
    public function getPaymentError(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_ERROR);
    }

    /**
     * Get Payment Error Code
     *
     * @return string
     */
    public function getPaymentErrorCode(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_ERROR_CODE);
    }

    /**
     * Set Avs Result
     *
     * @param string $avsResult
     * @return PaymentHistoryInterface
     */
    public function setAvsResult(string $avsResult): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_AVS_RESULT, $avsResult);
    }

    /**
     * Set Card Code Result
     *
     * @param string $cardCodeResult
     * @return PaymentHistoryInterface
     */
    public function setCardCodeResult(string $cardCodeResult): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_CARD_CODE_RESULT, $cardCodeResult);
    }

    /**
     * Set Payment Error
     *
     * @param string $paymentError
     * @return PaymentHistoryInterface
     */
    public function setPaymentError(string $paymentError): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_ERROR, $paymentError);
    }

    /**
     * Set Payment Error Code
     *
     * @param string $paymentErrorCode
     * @return PaymentHistoryInterface
     */
    public function setPaymentErrorCode(string $paymentErrorCode): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_ERROR_CODE, $paymentErrorCode);
    }

    /**
     * Get Payment Status Code
     *
     * @return string
     */
    public function getPaymentStatusCode(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_STATUS_CODE);
    }

    /**
     * Set Payment Status Code
     *
     * @param mixed $paymentStatusCode
     * @return PaymentHistoryInterface
     */
    public function setPaymentStatusCode($paymentStatusCode): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_STATUS_CODE, $paymentStatusCode);
    }

    /**
     * Get Result Card Info
     *
     * @return string
     */
    public function getResultCardInfo(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT_CARD_INFO);
    }

    /**
     * Set Result Card Info
     *
     * @param mixed $resultCardInfo
     * @return PaymentHistoryInterface
     */
    public function setResultCardInfo($resultCardInfo): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT_CARD_INFO, $resultCardInfo);
    }

    /**
     * Get Result Status
     *
     * @return string
     */
    public function getResultStatus(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT_STATUS);
    }

    /**
     * Set Result Status
     *
     * @param mixed $resultStatus
     * @return PaymentHistoryInterface
     */
    public function setResultStatus($resultStatus): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_PAYMENT_RESULT_STATUS, $resultStatus);
    }

    /**
     * Get Ebiz Payment Scheduled Internal ID
     *
     * @return string
     */
    public function getEbizScheduledPaymentInternalId(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_RECURRING_SCHEDULED_PAYMENT_INTERNAL_ID);
    }

    /**
     * Set EbizPayment Internal ID
     *
     * @param mixed $ebizPaymentsScheduledInternalId
     * @return PaymentHistoryInterface
     */
    public function setEbizScheduledPaymentInternalId(
        $ebizPaymentsScheduledInternalId
    ): PaymentHistoryInterface
    {
        return $this->setData(
            self::EBIZCHARGE_PAYMENT_HISTORY_RECURRING_SCHEDULED_PAYMENT_INTERNAL_ID,
            $ebizPaymentsScheduledInternalId
        );
    }

    /**
     * Get Ebiz Payment ID
     *
     * @return string
     */
    public function getEbizPaymentInternalId(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_RECURRING_PAYMENT_INTERNAL_ID);
    }

    /**
     * Set Ebiz Payment ID
     *
     * @param mixed $ebizPaymentInternalId
     * @return PaymentHistoryInterface
     */
    public function setEbizPaymentInternalId($ebizPaymentInternalId): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_RECURRING_PAYMENT_INTERNAL_ID, $ebizPaymentInternalId);
    }

    /**
     * Get Ebizcharge Customer Number
     *
     * @return string
     */
    public function getEbizPaymentCustNumber(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_RECURRING_PAYMENT_CUSTOMER_NUMBER);
    }

    /**
     * Set Ebizcharge Payment Customer Number
     *
     * @param mixed $ebizPaymentCustNumber
     * @return PaymentHistoryInterface
     */
    public function setEbizPaymentCustNumber($ebizPaymentCustNumber): PaymentHistoryInterface
    {
        return $this->setData(
            self::EBIZCHARGE_PAYMENT_HISTORY_RECURRING_PAYMENT_CUSTOMER_NUMBER,
            $ebizPaymentCustNumber
        );
    }

    /**
     * Get Last Downlaoded Counter
     *
     * @return string
     */
    public function getLastDownloadedCounter(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_LAST_DOWNLOADED_COUNTER);
    }

    /**
     * Get Last Downlaoded Payments
     *
     * @return string
     */
    public function getLastDownloadedPayments(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_LAST_DOWNLOADED_PAYMENTS);
    }

    /**
     * Get Last Synced Date Time
     *
     * @return string
     */
    public function getLastSyncedDateTime(): string
    {
        return $this->getData(self::EBIZCHARGE_PAYMENT_HISTORY_LAST_SYNCED_DATE_TIME);
    }

    /**
     * Set Last Downloaded Counter
     *
     * @param mixed $lastDownloadedCounter
     * @return PaymentHistoryInterface
     */
    public function setLastDownloadedCounter($lastDownloadedCounter): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_LAST_DOWNLOADED_COUNTER, $lastDownloadedCounter);
    }

    /**
     * Set Last Downloaded Payments
     *
     * @param mixed $lastDownloadedPayments
     * @return PaymentHistoryInterface
     */
    public function setLastDownloadedPayments($lastDownloadedPayments): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_LAST_DOWNLOADED_PAYMENTS, $lastDownloadedPayments);
    }

    /**
     * Set Last Synced Date Time
     *
     * @param mixed $lastSyncedDateTime
     * @return PaymentHistoryInterface
     */
    public function setLastSyncedDateTime($lastSyncedDateTime): PaymentHistoryInterface
    {
        return $this->setData(self::EBIZCHARGE_PAYMENT_HISTORY_LAST_SYNCED_DATE_TIME, $lastSyncedDateTime);
    }

    /**
     * Model Constructor
     *
     * @resource Model Construct
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\PaymentHistory::class);
    }
}
