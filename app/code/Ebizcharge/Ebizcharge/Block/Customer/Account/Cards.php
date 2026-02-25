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

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config as EbizConfig;
use Ebizcharge\Ebizcharge\Model\Customer;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\Config;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Payment card class
 * Accesses data to pass to the
 * Manage My Payment Method pages
 *
 * Class Cards
 */
class Cards extends Template
{
    /**
     * Stored Bank Account URL
     *
     * @CONST: STORED_BANK_ACCOUNTS_URL
     */
    public const STORED_BANK_ACCOUNTS_URL = 'ebizcharge/ach/listaction';

    /**
     * Stored Cards Add new Card action URL
     *
     * @const: STORED_CARDS_ADD_ACTION_URL
     */
    public const STORED_CARDS_ADD_ACTION_URL = 'ebizcharge/cards/addaction';

    /**
     * Customer Account Url
     *
     * @const: CUSTOMER_ACCOUNT_URL
     */
    public const CUSTOMER_ACCOUNT_URL = 'customer/account';

    /**
     * Cards Save Action
     *
     * @const: STORED_CARDS_SAVEACTION
     */
    public const STORED_CARDS_SAVEACTION = 'ebizcharge/cards/saveaction';

    /**
     * Stored Cards Edit URL
     *
     * @const STORED_CARDS_EDIT_URL
     */
    public const STORED_CARDS_EDIT_URL = 'ebizcharge/cards/editaction';

    /**
     * Delete Cards Action URL
     *
     * @cont: DELETE_CARDS_ACTION_URL
     */
    public const DELETE_CARDS_ACTION_URL = 'ebizcharge/cards/deleteaction';

    /**
     * @var Session
     */
    protected Session $_customerSession;

    /**
     * @var TranApi
     */
    protected TranApi $_tranApi;

    /**
     * @var Config
     */
    protected Config $_paymentConfig;

    /**
     * @var EbizConfig
     */
    protected EbizConfig $_myConfig;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $_messageManager;

    /**
     * @var Http
     */
    protected Http $_response;

    /**
     * @var RedirectInterface
     */
    protected RedirectInterface $_redirect;

    /**
     * @var RecurringRepositoryInterface
     */
    protected RecurringRepositoryInterface $_recurringRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $_searchCriteriaBuilder;

    /**
     * @var Json
     */
    protected Json $_json;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected PriceCurrencyInterface $_priceCurrency;

    /**
     * @var Customer
     */
    public Customer $paymentMethodProfile;

    /**
     * @param Json $json
     * @param ManagerInterface $messageManager
     * @param Http $response
     * @param RedirectInterface $redirect
     * @param Template\Context $context
     * @param TranApi $tranApi
     * @param Session $customerSession
     * @param Config $paymentConfig
     * @param EbizConfig $config
     * @param RecurringRepositoryInterface $recurringRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerFactory $customerFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Json $json,
        ManagerInterface $messageManager,
        Http $response,
        RedirectInterface $redirect,
        Template\Context $context,
        TranApi $tranApi,
        Session $customerSession,
        Config $paymentConfig,
        EbizConfig $config,
        RecurringRepositoryInterface $recurringRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerFactory $customerFactory,
        EbizchargeLogger $ebizchargeLogger,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);

        /** @var _tranApi */
        $this->_tranApi = $tranApi;
        /** @var _paymentConfig */
        $this->_paymentConfig = $paymentConfig;
        /** @var _myConfig */
        $this->_myConfig = $config;
        /** Set Ach Status */
        $this->_tranApi->setAchStatus($this->_myConfig->isAchActive());
        /** Verify Edit Action */
        $this->verifyEditAction();
        /** @var _messageManager */
        $this->_messageManager = $messageManager;
        /** @var _response */
        $this->_response = $response;
        /** @var _redirect */
        $this->_redirect = $redirect;
        /** @var _recurringRepository */
        $this->_recurringRepository = $recurringRepository;
        /** @var _searchCriteriaBuilder */
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        /** @var _json */
        $this->_json = $json;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var  _customerSession */
        $this->_customerSession = $customerSession;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var  _priceCurrency */
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * Verify Edit Action
     *
     * @return bool
     */
    public function verifyEditAction()
    {
        if ($this->getRequest()->getFullActionName() === 'ebizcharge_cards_editaction') {
            $cid = $this->getRequest()->getParam('cid');
            $mid = $this->getRequest()->getParam('mid');
            $method = $this->getRequest()->getParam('method');

            /** CID and MID and Method given */
            if ($cid && $mid && $method) {
                return true;
            }
        }
        return false;
    }

    /**
     * Created At
     *
     * @param mixed $payment
     * @return mixed
     */
    public function getCreatedAt($payment)
    {
        return $payment->Created;
    }

    /**
     * Get Ach status from current payment object
     *
     * @return bool
     */
    public function getAchStatus(): bool
    {
        $storeId = $this->getStore()->getId();
        return $this->_tranApi->getAchStatus($storeId);
    }

    /**
     * Get Payment Method Name
     *
     * @param null|mixed $payment
     * @return mixed|string
     */
    public function prepareMethodName($payment = null)
    {
        $paymentMethodName = "";
        if ($payment) {
            $payment = (array)$payment;
            $paymentMethodName = $payment['MethodName'] ?? "";
            $pMethodName =  json_decode($paymentMethodName);

            // phpcs:ignore
            if (@is_object($pMethodName)) {
                $pMethodName = (array)$pMethodName;
                $paymentMethodName = isset($pMethodName["a"]) ? $pMethodName["a"] : "";
                $lastName = isset($pMethodName["b"]) ? $pMethodName["b"] : "";
                if ($lastName !== "") {
                    $paymentMethodName .= "-".$lastName;
                }
            }
        }

        return $paymentMethodName;
    }

    /**
     * Is Credit Card Status
     *
     * @return bool
     */
    public function getCreditCardsStatus():bool
    {
        $storeId = $this->getStore()->getId();
        return $this->_myConfig->isCreditCardEnabled($storeId);
    }

    /**
     * Is Credit Card Active
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isCreditCardActive(): bool
    {
        $storeId = $this->getStore()->getId();
        return $this->_myConfig->isCreditCardEnabled($storeId);
    }

    /**
     * Get Store
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore(): StoreInterface
    {
        return $this->_myConfig->getStore();
    }

    /**
     * Is Save Credit Cards Allowed
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isSaveCardsAllowed(): bool
    {
        $storeId = $this->getStore()->getId();
        return $this->_myConfig->saveCard($storeId);
    }

    /**
     * Get Ebizcharge Method Id
     *
     * @return mixed
     */
    public function getEbzcMethodId():string
    {
        return $this->getRequest()->getParam('mid');
    }

    /**
     * Get Method Name
     *
     * @return string
     */
    public function getMethodName():string
    {
        $method = $this->getRequest()->getParam('method');
        return urldecode($method);
    }

    /**
     * Get Back URL
     *
     * @return string
     */
    public function getBackUrl():string
    {
        return $this->getUrl(self::CUSTOMER_ACCOUNT_URL);
    }

    /**
     * Get Add Card URL
     *
     * @return string
     */
    public function getAddCardUrl():string
    {
        return $this->getUrl(self::STORED_CARDS_ADD_ACTION_URL, ['_secure' => true]);
    }

    /**
     * Get Edit Cards URL
     *
     * @param null|mixed $customerId
     * @param null|mixed $paymentMethodId
     * @param string $paymentMethod
     * @return string
     */
    public function getEditCardsUrl($customerId = null, $paymentMethodId = null, $paymentMethod = ''):string
    {
        return $this->getUrl(self::STORED_CARDS_EDIT_URL, ['_secure' => true,
            'cid' => $customerId,
            'mid' => $paymentMethodId,
            'method' => urlencode($paymentMethod)]);
    }

    /**
     * Get delete Cards Action URl
     *
     * @return string
     */
    public function getDeleteCardActionUrl():string
    {
        return $this->getUrl(self::DELETE_CARDS_ACTION_URL);
    }

    /**
     * Get Save Url
     *
     * @return string
     */
    public function getSaveUrl():string
    {
        return $this->_urlBuilder->getUrl(self::STORED_CARDS_SAVEACTION, ['_secure' => true]);
    }

    /**
     * Get Payment Cards
     *
     * @return mixed
     */
    public function getPaymentCards()
    {
        $searchCriteria = $this->_searchCriteriaBuilder->addFilter(
            RecurringInterface::MAGE_CUST_ID,
            $this->getMageCustId()
        );
        return $this;
        // return $this->_tokenRepository->getList($searchCriteria->create());
    }

    /**
     * Get Mage Customer Id
     *
     * @return mixed
     */
    public function getMageCustId()
    {
        return $this->_customerSession->getCustomer()->getId();
    }

    /**
     * Get config
     *
     * @param mixed $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->_myConfig->getConfig($path);
    }

    /**
     * Get CC Types
     *
     * @return array
     */
    public function getCcTypes():array
    {
        return $this->_paymentConfig->getCcTypes();
    }

    /**
     * Get CC Type Image
     *
     * @param mixed $cardType
     * @return string
     */
    public function getCCTypeImage($cardType = null):string
    {
        $image = '';
        switch (strtolower($cardType)) {
            case 'v':
            case 'vi':
                $image = 'visa.png';
                break;
            case 'ae':
            case 'a':
                $image = 'american_express.png';
                break;
            case 'mc':
            case 'm':
                $image = 'mastercard.png';
                break;
            case 'ds':
                $image = 'discover.png';
                break;
            case 'jcb':
            case 'j':
                $image = 'jcb.png';
                break;
        }

        return $image;
    }

    /**
     * Get Payment Method
     *
     * For user account payment methods listing
     *
     * @return array|null
     */
    public function getPaymentMethods()
    {
        $customerId = $this->getCustomerId();
        return $this->_customerFactory->create()->getEbizCustomerPaymentMethods($customerId);
    }

    /**
     * Get Customer Id
     *
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->getCustomer()->getId();
    }

    /**
     * Get Customer
     *
     * @return bool|Customer
     */
    public function getCustomer()
    {
        $customerId = $this->_customerSession->getId();
        if ($customerId) {
            $customer = $this->_customerFactory->create()->load($customerId);
            return $customer;
        }
        return false;
    }

    /**
     * Get Ebz Customer Token
     *
     * @return array|mixed|null
     */
    public function getEbzcCustToken()
    {
        return $this->getCustomer()->getEcCustToken();
    }

    /**
     * Get EbzcCustId
     *
     * @return array|mixed|null
     */
    public function getEbzcCustId()
    {
        return $this->getCustomer()->getEcCustId();
    }

    /**
     * Get Customer Payment Method
     *
     * @return mixed|null
     */
    public function getCustomerPaymentMethod()
    {
        $paymetnMethodId = $this->getRequest()->getParam('mid');
        $cid = $this->getRequest()->getParam('cid');
        /** @var $customerToken */
        $customerToken = $this->getCustomer()->getEcCustToken();
        $this->paymentMethodProfile = $this->_customerFactory->create();

        return $this->paymentMethodProfile->getPaymentMethodProfileById($paymetnMethodId, $customerToken);
    }

    /**
     * Get Payment Method Name
     *
     * @return mixed
     */
    public function getPaymentMethodName()
    {
        return $this->paymentMethodProfile->getPaymentProfileMethodName();
    }

    /**
     * Get Payment Method Name Json String
     *
     * @return mixed
     */
    public function getPaymentMethodNameJsonString()
    {
        return $this->paymentMethodProfile->getPaymentMethodNameJsonString();
    }

    /**
     * Get Payment Method Type
     *
     * @return mixed
     */
    public function getPaymentMethodType()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileMethodType();
    }

    /**
     * Get Payment Method Json Name
     *
     * @return mixed
     */
    public function getPaymentMethodJsonName()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileJsonName();
    }

    /**
     * Get Payment Method Json Type
     *
     * @return mixed
     */
    public function getPaymentMethodJsonType()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileJsonType();
    }

    /**
     * Get Payment Method ID
     *
     * @return mixed
     */
    public function getPaymentMethodId()
    {
        return $this->paymentMethodProfile->getPaymentProfileMethodId();
    }

    /**
     * Get Payment Method Exp Month
     *
     * @return mixed
     */
    public function getPaymentMethodExpMonth()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileExpMonth();
    }

    /**
     * Get Payment Method Exp Year
     *
     * @return mixed
     */
    public function getPaymentMethodExpYear()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileExpYear();
    }

    /**
     * Get Payment Method Created At
     *
     * @return mixed
     */
    public function getPaymentMethodCreatedAt()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileCreatedAt();
    }

    /**
     * Get Payment Method Modified At
     *
     * @return mixed
     */
    public function getPaymentMethodModifiedAt()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileModifiedAt();
    }

    /**
     * Get Payment Method Card Number
     *
     * @return mixed
     */
    public function getPaymentMethodCardNumber()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileCardNumber();
    }

    /**
     * Get Payment Method AVS Street
     *
     * @return mixed
     */
    public function getPaymentMethodAvsStreet()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileAvsStreet();
    }

    /**
     * Get Payment Method AVS Zip
     *
     * @return mixed
     */
    public function getPaymentMethodAvsZip()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileAvsZip();
    }

    /**
     * Get Payment Method Account Holder Name
     *
     * @return mixed
     */
    public function getPaymentMethodAccountHolderName()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileAccountHolderName();
    }

    /**
     * Get Payment Method Card Type
     *
     * @return mixed
     */
    public function getPaymentMethodCardType()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileCardType();
    }

    /**
     * Get Payment Method Profile Balance
     *
     * @return mixed
     */
    public function getPaymentMethodProfileBalance()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileBalance();
    }

    /**
     * Get Payment Method Profile Max Balance
     *
     * @return mixed
     */
    public function getPaymentMethodProfileMaxBalance()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileMaxBalance();
    }

    /**
     * Get Payment Method Profile Reload Schedule
     *
     * @return mixed
     */
    public function getPaymentMethodProfileReloadSchedule()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileReloadSchedule();
    }

    /**
     * Get Payment Method Profile Secondary Sort
     *
     * @return mixed
     */
    public function getPaymentMethodProfileSecondarySort()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileSecondarySort();
    }

    /**
     * This method verify either payment method is associated to some recurring that is under way
     *
     * @param int $customerId
     * @param string $methodId
     * @return bool
     */
    public function checkPaymentMethodStatus(int $customerId, $methodId): bool
    {
        try {
            $searchCriteria = $this->_searchCriteriaBuilder
                ->addFilter(RecurringInterface::MAGE_CUST_ID, $customerId)
                ->addFilter(RecurringInterface::EB_REC_METHOD_ID, $methodId)
                ->addFilter(RecurringInterface::REC_STATUS, 0);
            $paymentMethodRecurrings = $this->_recurringRepository->getList($searchCriteria->create());

            return count($paymentMethodRecurrings->getItems()) > 0;
        } catch (Exception $e) {
            $this->_ebizchargeLogger->addError(__(
                "Exception occured during checking Payment method status " . $e->getMessage()
            ));
            return false;
        }
    }

    /**
     * Get Card Holder
     *
     * @param mixed $payment
     * @return mixed
     */
    public function getCardHolder($payment = null)
    {
        $payment = (object)$payment;
        return $this->_json->unserialize($payment->ReloadSchedule)['cardholder'];
    }

    /**
     * Get Card Number
     *
     * @param mixed $payment
     * @return mixed
     */
    public function getCardNumber($payment = null)
    {
        return $payment->CardNumber;
    }

    /**
     * Get Expiration Month
     *
     * @param mixed $payment
     * @return string
     */
    public function getExpirationMonth($payment = null):string
    {
        return explode("-", $payment->CardExpiration)[1];
    }

    /**
     * Get Stored Bank Accounts URL
     *
     * @return string
     */
    public function getStoredBankAccountsUrl():string
    {
        return $this->getUrl(self::STORED_BANK_ACCOUNTS_URL);
    }

    /**
     * Get Formatted Price
     *
     * @param null|mixed $amount
     * @param int $precise
     * @return string
     */
    public function getFormatedPrice($amount = null, $precise = 2)
    {
        return $this->_priceCurrency->convertAndFormat($amount, true, $precise);
    }

    /**
     * Get Expiration year
     *
     * @param mixed $payment
     * @return string
     */
    public function getExpirationYear($payment = null):string
    {
        return explode("-", $payment->CardExpiration)[0];
    }
}
