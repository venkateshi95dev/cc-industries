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

namespace Ebizcharge\Ebizcharge\Block\Order\MultiShipping;

use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\Customer;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\Payment;
use Ebizcharge\Ebizcharge\Model\ProductFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSessionModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data as DataHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Payment\Model\Config\Source\Allmethods;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;


/**
 * Multi Shipping Checkout Payment Form
 *
 * Class PaymentForm
 */
class PaymentForm extends Cc
{

    /**
     * Order Edit
     */
    public const ORDER_EDIT_PAGE = "order_edit";

    /**
     * Order create
     */
    public const ORDER_CREATE_PAGE = "order_create";


    /**
     * Main Template
     *
     * @var string
     */
    protected $_template = 'Ebizcharge_Ebizcharge::checkout/multipshipping/ebizpaymentform.phtml';

    /**
     * @var Payment
     */
    protected Payment $_ebizchargePaymentsCard;

    /**
     * @var Config
     */
    protected Config $_config;

    /**
     * @var Allmethods
     */
    protected Allmethods $_allPaymentMethod;

    /**
     * @var Session
     */
    protected Session $_checkoutSessionModel;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productFactory;

    /**
     * @var QuoteFactory
     */
    protected QuoteFactory $_quoteFactory;

    /**
     * @var CustomerSessionModel
     */
    protected CustomerSessionModel $_customerSessionModel;

    /**
     * @var TranApi
     */
    protected TranApi $_soapApiModel;

    /**
     * @var UrlInterface
     */
    protected  $_urlBuilder;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManagerInterface;

    /**
     * @var array|array[]
     */
    public array $paymentTypeOptions;


    /**
     * @param Context $context
     * @param Config $config
     * @param Payment $payment
     * @param Allmethods $allPaymentMethod
     * @param Session $checkoutSessionModel
     * @param CustomerFactory $customerFactory
     * @param CustomerSessionModel $customerSessionModel
     * @param ProductFactory $productFactory
     * @param QuoteFactory $quoteFactory
     * @param PaymentConfig $paymentConfig
     * @param TranApi $soapApiModel
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManagerInterface
     * @param EbizchargeLogger $ebizchargeLogger
     * @param array $data
     */
    public function __construct(
        Context               $context,
        Config                $config,
        Payment               $payment,
        Allmethods            $allPaymentMethod,
        Session               $checkoutSessionModel,
        CustomerFactory       $customerFactory,
        CustomerSessionModel  $customerSessionModel,
        ProductFactory        $productFactory,
        QuoteFactory          $quoteFactory,
        PaymentConfig         $paymentConfig,
        TranApi               $soapApiModel,
        UrlInterface          $urlBuilder,
        StoreManagerInterface $storeManagerInterface,
        EbizchargeLogger      $ebizchargeLogger,
        array                 $data = []
    )
    {
        parent::__construct($context, $paymentConfig, $data);

        /** @var config */
        $this->_config = $config;
        /** @var  ebizchargePaymentsCard */
        $this->_ebizchargePaymentsCard = $payment;
        /** @var _allPaymentMethod */
        $this->_allPaymentMethod = $allPaymentMethod;
        /** @var _checkoutSessionModel */
        $this->_checkoutSessionModel = $checkoutSessionModel;
        /** @var _quoteFactory */
        $this->_quoteFactory = $quoteFactory;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _productFactory */
        $this->_productFactory = $productFactory;
        /** @var  _customerSessionModel */
        $this->_customerSessionModel = $customerSessionModel;
        /** @var _soapApiModel */
        $this->_soapApiModel = $soapApiModel;
        /** @var _urlBuilder */
        $this->_urlBuilder = $urlBuilder;
        /** @var  storeManagerInterface */
        $this->storeManagerInterface = $storeManagerInterface;

        /**
         * Payment type Options
         */
        $this->paymentTypeOptions = $this->_config->paymentTypeOptions;

        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Get EbizCustomer Token
     *
     * @return array|mixed|null
     */
    public function getEbizCustomerToken()
    {
        return $this->getCustomer()->getEcCustToken();
    }

    /**
     * Is Card Code Required For Admin
     *
     * @return bool[]
     */
    public function isCardCodeRequiredForAdmin()
    {
        return $this->_customerFactory->create()->isCvvRequiredForAdminSidePaymentMethod();
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function isVoidEditSelected($storeId)
    {
        return $this->_config->getVoidOrderEditFlow($storeId);
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * Get Customer
     *
     * @return Customer|null
     */
    public function getCustomer()
    {
        $customerSession = $this->_customerSessionModel;
        $customerId = $customerSession->getId();
        return $this->_customerFactory->create()->load($customerId);
    }

    /**
     * Get Saved Payment Bank Accounts
     *
     * @return array
     */
    public function getSavedPaymentBankAccounts()
    {
        $customer = $this->getCustomer();
        $customerId = $customer->getId();
        return $customer->getEbizCustomerBankAccounts($customerId);
    }

    /**
     * Get Payment Method Request Params
     *
     * @return array
     */
    public function getPaymentMethodRequestParams()
    {
        return $this->getRequest()->getParams();
    }

    /**
     * Get Payment Params
     *
     * @return array
     */
    public function getPaymentParams()
    {
        return $this->getRequest()->getParam('payment');
    }

    /**
     * Bank Account Types
     *
     * @return array
     */
    public function getPaymentBankAccounTypes()
    {
        return $this->_soapApiModel->bankAccountTypes;
    }

    /**
     * Get Saved Payment Bank Accounts
     *
     * @return array
     */
    public function getSavedPaymentCreditCards()
    {
        $customer = $this->getCustomer();
        $customerId = $customer->getId();
        return $customer->getEbizCustomerCreditCards($customerId);
    }

    /**
     * Get Customer Id
     *
     * @return mixed
     */
    public function getCustomerId()
    {
        $customer = $this->getCustomer();
        return $customer->getId();
    }

    /**
     * Get client Key
     *
     * @return mixed
     */
    public function getClientKey()
    {
        return $this->_config->getSourceKey();
    }

    /**
     * Get All Payment Methods
     *
     * @return array
     */
    public function getAllPaymentMethods()
    {
        return $this->_allPaymentMethod->toOptionArray();
    }

    /**
     * Get Cc Available Types
     *
     * @return array
     */
    public function getCcAvailableTypes(): array
    {
        $storeId = $this->_config->getStoreId();
        return $this->_config->getSelectedPaymentCardTypes($storeId);
    }

    /**
     * Get Request Card Code Admin
     *
     * @return bool
     */
    public function getRequestCardCodeAdmin()
    {
        $storeId = $this->getStore()->getId();
        return $this->_config->getRequestCardCodeAdmin($storeId) ?? 1;
    }

    /**
     * Get AvsCvvValidateUrl
     *
     * @return string
     */
    public function getAvsCvvValidateUrl()
    {
        return $this->getUrl('ebizcharge_ebizcharge/cards/validatecvvavscards');
    }

    /**
     * Is AVS CVV Zip Enabled
     *
     * @return bool
     */
    public function isAvsCvvZipEnabled()
    {
        $storeId = $this->getStore()->getId();
        return $this->_config->isAvsCvvZipEnabled($storeId);
    }

    /**
     * Is Bank Account Save Allowed
     *
     * @return bool
     */
    public function isBankAccountSaveAllowed()
    {
        return $this->achEnabled();
    }

    /**
     * Get ACH Enabled
     *
     * @return bool
     */
    public function achEnabled()
    {
        return $this->_config->isAchActive();
    }

    /**
     * Is Credit Card Save Allowed
     *
     * @return bool
     */
    public function isCreditCardSaveAllowed()
    {
        return $this->saveCardEnabled();
    }

    /**
     * Save Card Enabled
     *
     * @return bool
     */
    public function saveCardEnabled()
    {
        return $this->_config->saveCard();
    }

    /**
     * Is Save Cards Enabled
     *
     * @return bool
     */
    public function isSaveCardsEnabled()
    {
        return $this->_config->getIsSaveCreditCards();
    }

    /**
     * Is Save Bank Accounts Enabled
     *
     * @return bool
     */
    public function isSaveBankAccountsEnabled()
    {
        return $this->_config->getIsSaveBankAccounts();
    }

    /**
     * Get Payment Save Payment
     *
     * @return mixed
     */
    public function getPaymentSavePayment()
    {
        return $this->_config->getPaymentSavePayment();
    }

    /**
     * Get Delete URL
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->_config->getBaseDeleteUrl();
    }

    /**
     * Get Save Accounts
     *
     * @return array
     */
    public function getSavedBankAccounts()
    {
        return $this->_ebizchargePaymentsCard->getSavedBankAccounts();
    }

    /**
     * Get Saved Cards
     *
     * @return array
     */
    public function getSavedCards()
    {
        return $this->_ebizchargePaymentsCard->getSavedCards();
    }

    /**
     * Get Ebizcharge Customer Id
     *
     * @return mixed
     */
    public function getEbzcCustId()
    {
        return $this->_ebizchargePaymentsCard->getEbzcCustId();
    }

    /**
     * Has token
     *
     * @return bool
     */
    public function hasToken()
    {
        return $this->_ebizchargePaymentsCard->hasToken();
    }

    /**
     * Payment Options Disabled
     *
     * @param int $storeId
     * @return bool
     */
    public function paymentOptionsDisabled($storeId = 0)
    {
        $isPaymentsEnabled = false;
        if (!$this->getCcPaymentStatus($storeId) &&
            !$this->achEnabled($storeId) &&
            !$this->isPayLaterEnabled()) {
            $isPaymentsEnabled = true;
        }
        return $isPaymentsEnabled;
    }

    /**
     * Get CC Paymetn Status
     *
     * @param int $storeId
     * @return bool
     */
    public function getCcPaymentStatus($storeId = 0)
    {
        return $this->_config->isCreditCardEnabled($storeId);
    }

    /**
     * Is Pay Later Enabled
     *
     * @return bool
     */
    public function isPayLaterEnabled()
    {
        return $this->_config->getPayLaterActive();
    }

    /**
     * Get is Credit Card Allowed
     *
     * @param int $storeId
     * @return bool
     */
    public function getIsCreditCardAllowed($storeId = 0)
    {
        return $this->_config->isCreditCardEnabled($storeId);
    }

    /**
     * Get is Bank Account Allowed
     *
     * @param int $storeId
     * @return bool
     */
    public function getIsBankAccountAllowed($storeId = 0)
    {
        return $this->_config->isAchActive($storeId);
    }

    /**
     * Get Store
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->_config->getStore();
    }

    /**
     * Check is auto save credit card details configuration is enabled or not
     *
     * @return bool
     */
    public function isAutoSaveCreditCard()
    {
        return $this->_config->getPaymentSavePayment();
    }

    /**
     * Get Surcharge Settings
     *
     * @return array
     */
    public function getSurchargeSettings()
    {
        $surchargeSettings = [];
        try {
            $surchargeSettings = $this->_customerFactory->create()->getSurchargeSettings($this->getStore()->getId());
        } catch (\Exception $exception) {
            $this->_ebizchargeLogger->addError($exception->getMessage());
        }

        return $surchargeSettings;
    }

    /**
     * Get Surcharge Banner Message
     *
     * @param string|null $surchargeTermsNote
     * @return array|string|string[]
     */
    public function getSurchargeBannerMessage($surchargeTermsNote = '')
    {
        $surchargeMessage = '';
        if (empty($surchargeTermsNote)) {
            $surchargeSettings = $this->getSurchargeSettings();
            $surchargeEnabled = $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_ENABLED] ?? false;
            $surchargeTermsNote = $surchargeEnabled ?
                ($surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_TERMS_NOTE] ?? '') : '';
        }

        if ($surchargeTermsNote) {
            $surchargeMessage = str_ireplace(
                'credit card',
                '<b>credit card</b>',
                $surchargeTermsNote
            );
        }

        return $surchargeMessage;
    }

    /**
     * Get Calculate Surcharge AJAX controller Url
     *
     * @return string
     */
    public function getCalculateSurchargeUrl()
    {
        return $this->getUrl(
            'ebizcharge_ebizcharge/sales/calculatesurcharge',
            ['_secure' => true]
        );
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getCurrentUrl()
    {
        return $this->storeManagerInterface->getStore()->getCurrentUrl();
    }


    /**
     * @return bool
     */
    public function isOrderCreatePage()
    {
        $isCreateOrderPage = false;
        $currentUrl = $this->getCurrentUrl();
        if (strpos($currentUrl, self::ORDER_CREATE_PAGE) !== false) {
            $isCreateOrderPage = true;
        }
        return $isCreateOrderPage;
    }

    /**
     * @return bool
     */
    public function isOrderEditPage()
    {
        $isEditOrderPage = false;
        $currentUrl = $this->getCurrentUrl();
        if (strpos($currentUrl, self::ORDER_EDIT_PAGE) !== false) {
            $isEditOrderPage = true;
        }
        return $isEditOrderPage;
    }

}
