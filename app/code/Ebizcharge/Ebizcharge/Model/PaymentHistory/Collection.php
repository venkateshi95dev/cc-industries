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

namespace Ebizcharge\Ebizcharge\Model\PaymentHistory;

use Ebizcharge\Ebizcharge\Api\Data\ConfigModelInterface;
use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Api\Data\SoapApiModelInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Data\Collection as CoreCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DataObjectFactory as DataObject;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Payment history collection using third party api
 *
 * Class Collection
 */
class Collection extends CoreCollection
{
    /**
     * @var DataObject
     */
    protected DataObject $_dataObject;

    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $_customerRepository;

    /**
     * @var DateTime
     */
    protected DateTime $_dateTime;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var Config
     */
    protected Config $_ebizchargeConfigModel;

    /**
     * @var TranApi
     */
    protected TranApi $_soapApiModel;

    /**
     * Collection constructor.
     *
     * @param DateTime $dateTime
     * @param CustomerRepositoryInterface $customerRepository
     * @param DataObject $dataObject
     * @param EntityFactoryInterface $entityFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param Config $ebizchargeConfigModel
     * @param TranApi $soapApiModel
     */
    public function __construct(
        DateTime                    $dateTime,
        CustomerRepositoryInterface $customerRepository,
        DataObject                  $dataObject,
        EntityFactoryInterface      $entityFactory,
        EbizchargeLogger            $ebizchargeLogger,
        Config                      $ebizchargeConfigModel,
        TranApi                     $soapApiModel
    ) {
        parent::__construct($entityFactory);

        /** @var  dataObject */
        $this->_dataObject = $dataObject;
        /** @var  customerRepository */
        $this->_customerRepository = $customerRepository;
        /** @var  dateTime */
        $this->_dateTime = $dateTime;
        /** @var  ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _ebizchargeConfigModel */
        $this->_ebizchargeConfigModel = $ebizchargeConfigModel;
        /** @var _soapApiModel */
        $this->_soapApiModel = $soapApiModel;
    }

    /**
     * Add Field to Filter
     *
     * @param mixed $field
     * @param mixed $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition): Collection
    {
        return $this;
    }

    /**
     * Get Collection
     *
     * @param string $customerId
     * @param string $fromDate
     * @param string $toDate
     * @param int $startPosition
     * @param int $limit
     * @param string $sort
     * @param int $countOnly
     * @return $this
     * @throws Exception
     */
    public function getCollection(
        $customerId = '',
        $fromDate = '',
        $toDate = '',
        $startPosition = 0,
        $limit = 0,
        $sort = '',
        $countOnly = 0
    ) {
        /** @var $recurringPaymentTransactions */
        $recurringPaymentTransactions = $this->_prepareEbizchargeRecurringCollection(
            $customerId,
            $fromDate,
            $toDate,
            $startPosition,
            $limit,
            $sort,
            $countOnly
        );

        /**
         * Adding Data to Collection
         */
        $this->addDataToCollection($recurringPaymentTransactions);

        return $this;
    }

    /**
     * Prepare Ebizcharge Recurring Collection
     *
     * @param string $customerId
     * @param string $fromDate
     * @param string $toDate
     * @param int $startPosition
     * @param int $limit
     * @param string $sort
     * @param bool $countOnly
     * @return mixed
     */
    protected function _prepareEbizchargeRecurringCollection(
        $customerId = '',
        $fromDate = '',
        $toDate = '',
        $startPosition = 0,
        $limit = 1000,
        $sort = '',
        $countOnly = false
    ) {
        /** @var  $searchParams */
        $searchParams = [
            'customerId' => $customerId,
            'startDate' => $fromDate,
            'endDate' => $toDate,
            'start' => $startPosition,
            'limit' => $limit,
            'sort' => $sort,
            'countOnly' => $countOnly
        ];

        /** @var $ebizchargeRecurringPaymentCollection */
        $ebizchargeRecurringPaymentCollection = $this->getSearchTransactions($searchParams);

        /**
         * Fetching the Payments Collection
         */
        $recurringPaymentCollection = $ebizchargeRecurringPaymentCollection['items'];

        /**
         * Setting Total Records
         */
        $this->_totalRecords = $ebizchargeRecurringPaymentCollection['total_items'];

        return $recurringPaymentCollection;
    }

    /**
     * Get Search Transactions
     *
     * @param array $filterParams
     * @return array
     * @phpcs:disable
     */
    public function getSearchTransactions($filterParams = [])
    {
        /** @var $transactionCollections */
        $transactionCollections = [
            'items' => [],
            'total_items' => 0
        ];
        $totalTransactions = 0;

        try {
            $sortOrder = isset($filterParams['sort']) ? $filterParams['sort'] : ' DESC';
            $countOnly = isset($filterParams['countOnly']) ? $filterParams['countOnly'] : false;
            $start = $filterParams['start'] ?? SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_START_LIMIT;
            $limit = $filterParams['limit'] ?? SoapApiModelInterface::DEFAULT_PAGE_LISTING_LIMIT;
            $matchOnly = isset($filterParams['match_all']) ? $filterParams['match_all'] : 1;
            $startDate = isset($filterParams['startDate']) ?
                date('Y-m-d', strtotime($filterParams['startDate'])) : date('Y-m-d');

            if ($countOnly) {
                $start = $start ? $start : SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_START_LIMIT;
                $limit = $limit ? $limit : SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_MAX_PAYMENTS_LIMIT;
            }

            /** @var  $filterStartAndlimits */
            $filterStartAndlimits =
                [
                    [
                        'FieldName' => 'DateTime',
                        'ComparisonOperator' => 'gt',
                        'FieldValue' => $startDate
                    ]
                ];
            $maxSize = 0;
            $resultCount = 0;
            /**
             * Define Transaction Items Collection
             */
            /** @var $transactionItemsCollection */
            $transactionItemsCollection = [];
            $searchFilters['SearchFilter'] = $filterStartAndlimits;

            do {
                /** @var  $searchTransactionsReqParams */
                $searchTransactionsReqParams = [
                    'securityToken' => $this->_soapApiModel->getUeSecurityToken(),
                    'filters' => $searchFilters,
                    'matchAll' => $matchOnly,
                    'countOnly' => $countOnly,
                    'start' => $start,
                    'limit' => $limit,
                    'sort' => 'DateTime ' . $sortOrder
                ];
                echo "\n" . "Fetching transactions: start=" . $start, " maxsize=" . $maxSize, " limit=" . $limit;

                if (!$this->_soapApiModel->getClient()) {
                    $this->_ebizchargeLogger->addCritical(__("No Soap client found, kindly check your SOAP URL."));
                    continue;
                }

                $this->_ebizchargeLogger->addInfo(__("Fetching Transactions from (" . $start . "-" . (int)$start + (int)$limit . ")"));

                /** @var  $transactionItemsResults */
                $transactionItemsResults = $this->_soapApiModel->getClient()->SearchTransactions($searchTransactionsReqParams);


                /** if search Transactions at Ebizcharge API Gateway */
                if (!isset($transactionItemsResults->SearchTransactionsResult)) {
                    $resultCount = 0;

                } elseif ((is_array($transactionItemsResults->SearchTransactionsResult->Transactions->TransactionObject)) &&
                    (count((array)$transactionItemsResults->SearchTransactionsResult->Transactions->TransactionObject)) > 1) {

                    $transactionItems = (array)$transactionItemsResults->SearchTransactionsResult->Transactions->TransactionObject;
                    $resultCount = count($transactionItemsResults->SearchTransactionsResult->Transactions->TransactionObject);
                    $transactionItemsCollection = array_merge($transactionItemsCollection, $transactionItems);

                } else {
                    /** @var $ordersObj */
                    $transactionItem[] = (array)$transactionItemsResults->SearchTransactionsResult->Transactions->TransactionObject;
                    $transactionItemsCollection = array_merge($transactionItemsCollection, $transactionItem);
                    $resultCount = 1;
                    $maxSize = 1;
                }

                /** result count */
                if ($resultCount < $limit) {
                    $maxSize = 1;
                }
                $start = (int)$start + (int)$limit;

            } while ($maxSize === 0);

            /** @var  $totalTransactions */
            $totalTransactions = count($transactionItemsCollection) ?? 0;

        } catch (Exception $ex) {
            $this->_ebizchargeLogger->addError(__(
                "Exception occurred during fetching transactions. Error: " . __METHOD__ . $ex->getMessage()
            ));
            printf("Error fetching transactions : " . $ex->getMessage());
        }

        /** @var  $recurringCollection */
        $transactionCollections['items'] = $transactionItemsCollection;
        $transactionCollections['total_items'] = $totalTransactions;


        return $transactionCollections;
    }
    // phpcs:enable

    /**
     * Add Transactions History to Data Collection
     *
     * @param array $recurringPayments
     * @return array
     * @throws Exception
     */
    public function addDataToCollection(array $recurringPayments = [])
    {
        $counter = 0;
        $rowData = [];
        $searchKeywords = $this->getSearchKeywords();

        /** @var $rowDataObject */
        $rowDataObject = $this->_dataObject->create();

        /** @var $totalRecurringPayments */
        $totalRecurringPayments = count((array)$recurringPayments) > 0 ? count($recurringPayments) : 0;
        $collection = $rowDataObject = $this->_dataObject->create();

        $searchColumns = [
            'customerName',
            'customerEmail',
            'ShippingAddress',
            'Status',
            'cardInfo',
            'TransactionType',
            'AccountHolder',
            'cardInfo'
        ];
        $transCollection = [];

        /** Total Recurring Payments */
        if ($totalRecurringPayments > 0) {

            /**
             * @var  $key
             * @var  $value
             */
            foreach ($recurringPayments as $key => $recurringPayment) {
                $foundRecord = false;
                $recurringPayment = (array)$recurringPayment;

                /** @var $rowDataObject */
                $rowDataObject = $this->_dataObject->create();

                $refNumber = $this->getReferenceNumber($recurringPayment);
                $customerId = isset($recurringPayment["CustomerID"]) ? $recurringPayment["CustomerID"] : "";
                $customerEmail = $this->getCustomerEmail($recurringPayment);
                $customerName = $this->prepareCustomerName($recurringPayment);
                $accountHolder = isset($recurringPayment["AccountHolder"]) ? $recurringPayment["AccountHolder"] : "";

                /** @var  $paymentResponse */
                $paymentResponse = isset($recurringPayment["Response"]) ? (array)$recurringPayment["Response"] : [];

                /** @var $paymentResult */
                $paymentResult = isset($paymentResponse["Status"]) ? $paymentResponse["Status"] : '';
                $statusCode = isset($paymentResponse["StatusCode"]) ? $paymentResponse["StatusCode"] : '';
                $avsResult = isset($paymentResponse["AvsResult"]) ? $paymentResponse["AvsResult"] : '';
                $cardCodeResult = isset($paymentResponse["CardCodeResult"]) ? $paymentResponse["CardCodeResult"] : '';
                $paymentError = isset($paymentResponse["Error"]) ? $paymentResponse["Error"] : '';
                $paymentErrorCode = isset($paymentResponse["ErrorCode"]) ? $paymentResponse["ErrorCode"] : '';
                $paymentResultCode = isset($paymentResponse["ResultCode"]) ? $paymentResponse["ResultCode"] : '';
                $lineItems = isset($recurringPayment["LineItems"]) ? (array)$recurringPayment["LineItems"] : [];
                $paymentDetail = isset($recurringPayment["Details"]) ? (array)$recurringPayment["Details"] : [];
                $customFields = isset($recurringPayment["CustomFields"]) ?
                    (array)$recurringPayment["CustomFields"] : [];
                $creditCardData = isset($recurringPayment["CreditCardData"]) ?
                    (array)$recurringPayment["CreditCardData"] : [];
                $checkData = isset($recurringPayment["CheckData"]) ? (array)$recurringPayment["CheckData"] : [];
                $checkTrace = isset($recurringPayment["CheckTrace"]) ? (array)$recurringPayment["CheckTrace"] : [];
                $billingAddress = isset($recurringPayment["BillingAddress"]) ?
                    (array)$recurringPayment["BillingAddress"] : [];
                $shippingAddress = isset($recurringPayment["ShippingAddress"]) ?
                    (array)$recurringPayment["ShippingAddress"] : [];

                /** @var  $resultStatus */
                $resultStatus = 'Pending';

                if ($paymentResultCode === PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED) {
                    $resultStatus = 'Approved';
                } elseif ($paymentResultCode === PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_DECLINED) {
                    $resultStatus = 'Declined';
                } elseif ($paymentResultCode === PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR) {
                    $resultStatus = 'Error';
                } else {
                    $resultStatus = 'Rejected';
                }

                $cardInfo = '';
                if (count($creditCardData) > 0) {
                    $ccType = isset($creditCardData["CardType"]) ? $creditCardData["CardType"] : "OT";
                    $cardNumber = isset($creditCardData["CardNumber"]) ? $creditCardData["CardNumber"] : "";

                    if ($ccType === ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_PREFIX) {
                        $cardType = ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS;
                    } elseif ($ccType === ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_PREFIX) {
                        $cardType = ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD;
                    } elseif ($ccType === ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_PREFIX) {
                        $cardType = ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD;
                    } elseif ($ccType === ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_PREFIX) {
                        $cardType = ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD;
                    } else {
                        $cardType = $ccType;
                    }
                    $cardInfo = $cardNumber . ' - ' . $cardType;
                }

                if (count($checkData) > 0) {
                    $accountType = isset($checkData["AccountType"]) ? $checkData["AccountType"] : "";
                    $accountNumber = isset($checkData["Account"]) ? $checkData["Account"] : "";
                    $cardInfo = $accountNumber . ' - ' . $accountType;
                }

                // phpcs:disable
                $rowData = [
                    'counterField' => $counter++,
                    'massActionField' => @$refNumber . '#' . $customerEmail . '#' . $accountHolder,
                    'customerId' => @$customerId,
                    'customerEmail' => @$customerEmail,
                    'customerName' => @$customerName,
                    'source' => isset($recurringPayment["Source"]) ? $recurringPayment["Source"] : "",
                    'paymentAmount' => @$this->getAuthAmount($recurringPayment),
                    'refNum' => @$refNumber,
                    'cardInfo' => @$this->getPaymentMethodName($recurringPayment),
                    'DateTime' => isset($recurringPayment["DateTime"]) ? $recurringPayment["DateTime"] : "",
                    'Source' => isset($recurringPayment["Source"]) ? $recurringPayment["Source"] : "",
                    'ShippingAddress' => $shippingAddress,
                    'ServerIP' => isset($paymentResponse["ServerIP"]) ? $paymentResponse["ServerIP"] : "",
                    'Response' => @$paymentResponse,
                    'LineItems' => @$lineItems,
                    'Details' => @$paymentDetail,
                    'User' => isset($recurringPayment["User"]) ? $recurringPayment["User"] : "",
                    'CustomerID' => isset($recurringPayment["CustomerID"]) ? $recurringPayment["CustomerID"] : "",
                    'CreditCardData' => $creditCardData,
                    'ClientIP' => isset($recurringPayment["ClientIP"]) ? $recurringPayment["ClientIP"] : "",
                    'CheckTrace' => @$checkTrace,
                    'CheckData' => @$checkData,
                    'BillingAddress' => @$billingAddress,
                    'AccountHolder' => @$accountHolder,
                    'Status' => isset($recurringPayment["Status"]) ? $recurringPayment["Status"] : "",
                    'StatusCode' => @$statusCode,
                    'ResultStatus' => @$resultStatus,
                    'ResultCardInfo' => @$cardInfo,
                    'AvsResult' => @$avsResult,
                    'CardCodeResult' => @$cardCodeResult,
                    'PaymentResult' => @$paymentResult,
                    'PaymentError' => @$paymentError,
                    'PaymentErrorCode' => @$paymentErrorCode,
                    'TransactionType' => isset($recurringPayment["TransactionType"]) ? $recurringPayment["TransactionType"] : ""
                ];
                // phpcs:enable

                if ($searchKeywords !== '') {
                    /** @var  $foundRecord */
                    $foundRecord = $this->searchItems($searchKeywords, $searchColumns, $rowData);

                    if (!$foundRecord) {
                        continue;
                    }
                }

                $rowDataObject->setData($rowData);
                $transCollection[] = $this->addItem($rowDataObject);
            }

            /** @var _totalRecords */
            $this->_totalRecords = $this->getTotalRecurrings($collection);

        } else {
            $this->_ebizchargeLogger->addError(__("Exception occurred fetching Subscriptions Payments"));
            /** @var $rowDataObject */
            $rowDataObject = $this->_dataObject->create();
            $rowDataObject->setData([]);
            $transCollection[] = $this->addItem($rowDataObject);
            $this->_totalRecords = 0;
        }

        return $transCollection;
    }

    /**
     * Get Search Keywords
     *
     * @return mixed|string
     */
    public function getSearchKeywords()
    {
        $params = $this->getSearchParams();
        return isset($params['search_keywords']) ? $params['search_keywords'] : '';
    }

    /**
     * Get Search Params
     *
     * @return array
     */
    public function getSearchParams()
    {
        // phpcs:ignore
        return $_REQUEST;
    }

    /**
     * Get Reference Number
     *
     * @param null|mixed $recurringPayment
     * @return int|mixed
     */
    public function getReferenceNumber($recurringPayment = null)
    {
        $recurringPayment = (array)$recurringPayment;
        $transactionResponse = isset($recurringPayment['Response']) ? (array)$recurringPayment['Response'] : [];
        return isset($transactionResponse['RefNum']) ? $transactionResponse['RefNum'] : 0;
    }

    /**
     * Get Customer Email
     *
     * @param null|mixed $recurringPayment
     * @return mixed|string
     */
    public function getCustomerEmail($recurringPayment = null)
    {
        $recurringPayment = (array)$recurringPayment;
        $billingAddress = isset($recurringPayment['BillingAddress']) ? (array)$recurringPayment['BillingAddress'] : [];
        return isset($billingAddress['Email']) && $billingAddress['Email'] !== '' ? $billingAddress['Email'] : '*';
    }

    /**
     * Prepare Customer Name
     *
     * @param null|mixed $recurringPayment
     * @return string
     */
    public function prepareCustomerName($recurringPayment = null)
    {
        $recurringPayment = (array)$recurringPayment;
        $billingAddress = isset($recurringPayment['BillingAddress']) ? (array)$recurringPayment['BillingAddress'] :
            ['FirstName' => '', 'LastName' => ''];
        $firstName = isset($billingAddress['FirstName']) ? $billingAddress['FirstName'] : '';
        $lastName = isset($billingAddress['LastName']) ? $billingAddress['LastName'] : '';

        $customerName = $firstName . ' ' . $lastName;
        $customerName = (trim($customerName) !== '') ? $customerName : '';

        return $customerName;
    }

    /**
     * Get Auth Amount
     *
     * @param null|mixed $recurringPayment
     * @return mixed
     */
    public function getAuthAmount($recurringPayment = null)
    {
        $recurringPayment = (array)$recurringPayment;
        $authAmount = isset($recurringPayment['Details']) ? (array)$recurringPayment['Details'] : 0;
        return $authAmount['Amount'];
    }

    /**
     * Get Payment Method Name
     *
     * @param null|mixed $recurringPayment
     * @return string
     */
    public function getPaymentMethodName($recurringPayment = null)
    {
        $recurringPayment = (array)$recurringPayment;
        $creditCardData = isset($recurringPayment['CreditCardData']) ? (array)$recurringPayment['CreditCardData'] : [];
        $cardType = isset($creditCardData['CardType']) ? $creditCardData['CardType'] : 'Bank Account';

        if ($cardType !== 'Bank Account') {
            $cardType = $this->_ebizchargeConfigModel->getCardType($cardType);
        }
        $cardType = (string)$cardType;

        return $cardType;
    }

    /**
     * Search Items
     *
     * @param string $searchKeywords
     * @param array $searchColumns
     * @param array $rowData
     * @return bool
     */
    public function searchItems($searchKeywords = '', $searchColumns = [], $rowData = [])
    {
        $searchResp = false;
        $searchKeywords = $searchKeywords ? strtolower($searchKeywords) : '';

        foreach ($searchColumns as $column) {
            $columnText = '';
            if (!is_object($rowData[$column])) {
                $columnText = $rowData[$column] ? strtolower($rowData[$column]) : '';
            }

            if (strpos($columnText, $searchKeywords) !== false) {
                return $searchResp = true;
            }
        }
        return $searchResp;
    }

    /**
     * Get Total Recurrings
     *
     * @param array $subscriptionPayments
     * @return int
     */
    public function getTotalRecurrings($subscriptionPayments = [])
    {
        return $this->_totalRecords;
    }

    /**
     * Add Like
     *
     * @param array $collection
     * @param string $pattren
     * @return $this
     */
    public function addLike(array $collection, string $pattren)
    {
        return $this;
    }

    /**
     * Get customer details
     *
     * @param int $customerId
     * @return false|CustomerInterface
     */
    public function getCustomerById(int $customerId)
    {
        try {
            return $this->_customerRepository->getById($customerId);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
        return false;
    }
}
