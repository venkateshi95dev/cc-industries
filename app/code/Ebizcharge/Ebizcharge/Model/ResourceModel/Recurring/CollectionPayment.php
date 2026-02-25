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

namespace Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
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
class CollectionPayment extends CoreCollection
{
    /**
     * @var DataObject
     */
    private DataObject $dataObject;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $ebizchargeLogger;

    /**
     * CollectionPayment constructor.
     *
     * @param DateTime $dateTime
     * @param CustomerRepositoryInterface $customerRepository
     * @param DataObject $dataObject
     * @param EntityFactoryInterface $entityFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        DateTime $dateTime,
        CustomerRepositoryInterface $customerRepository,
        DataObject $dataObject,
        EntityFactoryInterface $entityFactory,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct($entityFactory);

        /** @var  dataObject */
        $this->dataObject = $dataObject;
        /** @var  customerRepository */
        $this->customerRepository = $customerRepository;
        /** @var  dateTime */
        $this->dateTime = $dateTime;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Add Field to Filter
     *
     * @param mixed $field
     * @param mixed $condition
     * @return CoreCollection
     */
    public function addFieldToFilter($field, $condition): CoreCollection
    {
        return $this;
    }

    /**
     * Add Data to Collection
     *
     * @param CoreCollection $collection
     * @param array $recurringPayments
     * @param int $totalCount
     * @return CoreCollection
     * @throws Exception
     */
    public function addDataToCollection(
        CoreCollection $collection,
        array          $recurringPayments,
        int            $totalCount = 0
    ): CoreCollection {

        foreach ($recurringPayments as $recurringPaymentKey => $recurringPayment) {

            if (!is_object($recurringPayment)) {
                continue;
            }
            if ($recurringPayment->Response->ResultCode == 'A') {
                $status = 'Approved';
            } elseif ($recurringPayment->Response->ResultCode == 'D') {
                $status = 'Declined';
            } else {
                $status = 'Rejected';
            }
            $cardInfo = '';

            if (isset($recurringPayment->CreditCardData->CardType)) {
                $ccType = $recurringPayment->CreditCardData->CardType;
                if ($ccType == 'A') {
                    $cardType = 'American Express';
                } elseif ($ccType == 'M') {
                    $cardType = 'Master';
                } elseif ($ccType == 'V') {
                    $cardType = 'Visa';
                } elseif ($ccType == 'DS') {
                    $cardType = 'Discover';
                } else {
                    $cardType = $ccType;
                }
                $cardInfo = $recurringPayment->CreditCardData->CardNumber . ' - ' . $cardType;

            } elseif (isset($recurringPayment->CheckData->AccountType)) {
                $accountType = $recurringPayment->CheckData->AccountType;
                $accountNumber = $recurringPayment->CheckData->Account;
                $cardInfo = $accountNumber . ' - ' . $accountType;
            }
            $customerName = '';
            $customerEmail = '';

            if ($customer = $this->getCustomerById((int)$recurringPayment->CustomerID)) {
                $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
                $customerEmail = $customer->getEmail();
            }

            /** @var $transactionData */
            $transactionData =  [
                'customerId' => $recurringPayment->CustomerID,
                'customerName' => $customerName,
                'customerEmail' => $customerEmail,
                'paymentDate' => $this->dateTime->date('Y-m-d', $recurringPayment->DateTime),
                'authCode' => $recurringPayment->Response->AuthCode ?? '',
                'paymentAmount' => $recurringPayment->Details->Amount,
                'cardInfo' => $cardInfo,
                'refNum' => $recurringPayment->Response->RefNum,
                'resultStatus' => $status,
                'created_at' => $recurringPayment->DateTime??'',
                'transactionSource' => $recurringPayment->Source??'',
                'serverIp' => $recurringPayment->ServerIP,
                'status' => $recurringPayment->Status,
                'transactionType' => $recurringPayment->TransactionType,
                'accountHolder' => $recurringPayment->AccountHolder,
                'shippingAddress' => $recurringPayment->ShippingAddress,
                'billingAddress' => $recurringPayment->BillingAddress,
                'transaction_response' => $recurringPayment->Response,
                'lineItems' => $recurringPayment->LineItems,
                'transactionDetails' => $recurringPayment->Details,
                'transaction_user' => $recurringPayment->User,
                'customFields' => $recurringPayment->CustomFields,
                'creditCardData' => $recurringPayment->CreditCardData,
                'clientIp' => $recurringPayment->ClientIP,
                'transactionTrace' => $recurringPayment->CheckTrace,
                'checkData' => $recurringPayment->CheckData

            ];

            /** @var  $dataObject */
            $dataObject = $this->dataObject->create()
                ->setData($transactionData);
            try {
                $collection = $collection->addItem($dataObject);
            } catch (\SoapFault $soapFault) {
                $this->ebizchargeLogger->addCritical(__(
                    'Exception occurred during fetching Transactions : Soap Error: '.$soapFault->getMessage()
                ));
            }
        }

        $collection->_totalRecords = $totalCount;

        return $collection;
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
            return $this->customerRepository->getById($customerId);
        } catch (Exception $e) {
            $this->ebizchargeLogger->addCritical($e->getMessage());
        }
        return false;
    }
}
