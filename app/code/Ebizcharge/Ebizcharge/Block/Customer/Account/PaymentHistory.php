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

namespace Ebizcharge\Ebizcharge\Block\Customer\Account;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Customer;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\ResourceModel\PaymentHistory\Collection as PaymentHistoryCollection;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\CollectionPayment;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;

/**
 * Payment History block class
 *
 * Class PaymentHistory
 */
class PaymentHistory extends Template
{
    /**
     * Payment History Recurring History URL
     *
     * @const: PAYMENT_HISTORY_RECURRING_HISTORY_URL
     */
    public const PAYMENT_HISTORY_RECURRING_HISTORY_URL = 'ebizcharge/recurrings/history/';

    /**
     * Payment History Delete Action Url
     *
     * @const: PAYMENT_HISTORY_DELETE_ACTION_URL
     */
    public const PAYMENT_HISTORY_DELETE_ACTION_URL = 'ebizcharge/recurrings/deleteaction';

    /**
     * Payment History Recurring URL
     *
     * @const: PAYMENT_HISTORY_RECURRING_URL
     */
    public const PAYMENT_HISTORY_RECURRING_URL = 'ebizcharge/recurrings/';

    /**
     * @var CollectionPayment
     */
    protected CollectionPayment $recurringCollectionFactory;

    /**
     * @var SessionFactory
     */
    protected SessionFactory $customerSession;

    /**
     * @var Collection
     */
    protected Collection $recurrings;

    /**
     * @var TranApi
     */
    protected TranApi $tranApi;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var PaymentHistoryCollection
     */
    protected PaymentHistoryCollection $paymentHistoryCollection;

    /**
     * @var PriceHelper
     */
    protected PriceHelper $priceHelper;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * PaymentHistory constructor.
     * @param Context $context
     * @param CollectionPayment $recurringCollectionFactory
     * @param PaymentHistoryCollection $paymentHistoryCollection
     * @param SessionFactory $customerSession
     * @param EbizchargeLogger $ebizchargeLogger
     * @param PriceHelper $priceHelper
     * @param CustomerFactory $customerFactory
     * @param TranApi $tranApi
     * @param array $data
     */
    public function __construct(
        Context                  $context,
        CollectionPayment        $recurringCollectionFactory,
        PaymentHistoryCollection $paymentHistoryCollection,
        SessionFactory           $customerSession,
        EbizchargeLogger         $ebizchargeLogger,
        PriceHelper              $priceHelper,
        CustomerFactory          $customerFactory,
        TranApi                  $tranApi,
        array                    $data = []
    )
    {
        parent::__construct($context, $data);

        /** @var recurringCollectionFactory */
        $this->recurringCollectionFactory = $recurringCollectionFactory;
        /** @var customerSession */
        $this->customerSession = $customerSession;
        /** @var tranApi */
        $this->tranApi = $tranApi;
        /** @var ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var paymentHistoryCollection */
        $this->paymentHistoryCollection = $paymentHistoryCollection;
        /** @var priceHelper */
        $this->priceHelper = $priceHelper;
        /** @var  _customerFactory */
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Get receipt Ref Number
     *
     * @return string
     */
    public function getReceiptRefNumber()
    {
        return $this->tranApi->getReceiptRefNumber();
    }

    /**
     * Get Pager child block output
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Format Currency
     *
     * @param mixed $amountPrice
     * @return float|string
     */
    public function formatCurrency($amountPrice)
    {
        return $this->priceHelper->currency($amountPrice, true, false);
    }

    /**
     * Get Transaction By Id
     *
     * @param null|mixed $referenceNumber
     * @return bool|mixed
     */
    public function getTransactionByReferenceId($referenceNumber = null)
    {
        $transaction = false;
        $customerTransactions = $this->getRecurrings();
        if (count($customerTransactions) > 0) {
            foreach ($customerTransactions as $customerTransaction) {
                if ($referenceNumber == $customerTransaction->getData('refNum')) {
                    $transaction = $customerTransaction;
                }
            }
        }
        return $transaction;
    }

    /**
     * Get Recurrings
     *
     * @return array|Collection
     */
    public function getRecurrings()
    {
        if (!($this->getCustomerId())) {
            return [];
        }
        $customer = $this->getCustomer();
        $customerEmail = $customer->getEmail();

        /** @var  $paymentHistoryCollection */
        $this->recurrings =
            $this->paymentHistoryCollection
                ->addFieldToSelect('*')
                ->addFieldToFilter('customer_email', $customerEmail);

        return $this->recurrings;
    }

    /**
     * Get Customer Id
     *
     * @return null|mixed
     */
    public function getCustomerId()
    {
        $customerId = null;
        $customer = $this->getCustomer();
        if ($customer) {
            $customerId = $customer->getEntityId();
        }
        return $customerId;
    }

    /**
     * Get Customer
     *
     * @return Customer
     */
    public function getCustomer()
    {
        $customerId = $this->customerSession->create()->getCustomerId();
        return $this->_customerFactory->create()->load($customerId);
    }

    /**
     * @return array|PaymentHistoryCollection
     */
    public function getPaymentHistoryCollection()
    {
        $paymentsHistoryCollection = [];
        if ($this->getCustomer()) {
            $customer = $this->getCustomer() ?? null;
            $customerEmail = $customer->getEmail() ?? "";
            /** @var  $paymentHistoryCollection */
            $paymentsHistoryCollection = $this->recurrings =
                $this->paymentHistoryCollection
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('customer_email', $customerEmail);
        }

        return $paymentsHistoryCollection;

    }

    /**
     * Get Mage Customer ID
     *
     * @return int|null
     */
    public function getMageCustId()
    {
        return $this->customerSession->create()->getCustomerId();
    }

    /**
     * Get payments count from gateway
     *
     * @param null|mixed $customerId
     * @return int
     */
    public function getPaymentsCount($customerId = null)
    {
        return $this->tranApi->getSearchTransactions(
            $customerId,
            false,
            false,
            false,
            true
        );
    }

    /**
     * Get All Search Recurring Payments
     *
     * @param mixed $customerId
     * @param mixed $paymentDate
     * @return array
     */
    public function getAllSearchRecurringPayments(
        $customerId = null,
        $paymentDate = false
    ): array
    {
        $pageNumber = $this->getPageNumber();
        $start = (--$pageNumber) * $this->getPageSize();
        $limit = $this->getPageSize();

        return $this->tranApi->getSearchTransactions($customerId, $start, $limit, $paymentDate);
    }

    /**
     * Get Page Number
     *
     * @return int
     */
    public function getPageNumber()
    {
        return (int)$this->getRequest()->getParam('p', 1);
    }

    /**
     * Get Page Size
     *
     * @return int
     */
    public function getPageSize()
    {
        return (int)$this->getRequest()->getParam('limit', 10);
    }

    /**
     * Prepare Layout
     *
     * @return $this|PaymentHistory
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var $pager */
        $pager = $this->getLayout()->createBlock(
            Pager::class,
            'ebizcharge.history.list.pager'
        )->setCollection($this->getPaymentHistoryCollection())
        ;

        $this->setChild('pager', $pager);

        return $this;
    }
}
