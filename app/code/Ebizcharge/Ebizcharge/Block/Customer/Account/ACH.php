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

use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\Customer;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\Payment;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\Config as PaymentConfig;

/**
 * ACH edit action class
 *
 * Class ACH
 */
class ACH extends Template
{
    /**
     * Save Ach Bank Account Action Url
     *
     * @const: SAVE_ACH_BANK_ACCOUNT_ACTION_URL
     */
    public const SAVE_ACH_BANK_ACCOUNT_ACTION_URL = 'ebizcharge/ach/saveaction';

    /**
     * Add Ach Bank Account Action URl
     *
     * @const: ADD_ACH_BANK_ACCOUNT_ACTION_URL
     */
    public const ADD_ACH_BANK_ACCOUNT_ACTION_URL = 'ebizcharge/ach/addaction/';

    /**
     * Customer Account URL
     *
     * @const: CUSTOMER_ACCOUNT_URL
     */
    public const CUSTOMER_ACCOUNT_URL = 'customer/account/';

    /**
     * @var TranApi
     */
    protected TranApi $tranApi;

    /**
     * @var PaymentConfig
     */
    protected PaymentConfig $paymentConfig;

    /**
     * @var Config
     */
    protected Config $_configModel;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManager;

    /**
     * @var Http
     */
    protected Http $response;

    /**
     * @var RedirectInterface
     */
    protected RedirectInterface $redirect;

    /**
     * @var SessionFactory
     */
    protected SessionFactory $customerSession;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected PriceCurrencyInterface $_priceCurrency;

    /**
     * ACH constructor.
     *
     * @param ManagerInterface $messageManager
     * @param Http $response
     * @param RedirectInterface $redirect
     * @param Template\Context $context
     * @param TranApi $tranApi
     * @param SessionFactory $session
     * @param CustomerFactory $customerFactory
     * @param PaymentConfig $paymentConfig
     * @param Config $configModel
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        ManagerInterface $messageManager,
        Http $response,
        RedirectInterface $redirect,
        Template\Context $context,
        TranApi $tranApi,
        SessionFactory $session,
        CustomerFactory $customerFactory,
        PaymentConfig $paymentConfig,
        Config $configModel,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);

        /** @var  tranApi */
        $this->tranApi = $tranApi;
        /** @var  customerSession */
        $this->customerSession = $session;
        /** @var  paymentConfig */
        $this->paymentConfig = $paymentConfig;
        /** @var  _configModel */
        $this->_configModel = $configModel;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;

        /** @var  messageManager */
        $this->messageManager = $messageManager;
        /** @var  response */
        $this->response = $response;
        /** @var  redirect */
        $this->redirect = $redirect;
        /** @var  _priceCurrency */
        $this->_priceCurrency = $priceCurrency;

        /** Verify Edit Action */
        $this->verifyEditAction();
    }

    /**
     * Verify edit action params are set
     *
     * @return void
     */
    public function verifyEditAction(): void
    {
        if ($this->getRequest()->getFullActionName() == 'ebizcharge_ach_editaction') {
            $cid = $this->getRequest()->getParam('cid');
            $mid = $this->getRequest()->getParam('mid');
            $method = $this->getRequest()->getParam('method');

            if ($cid && $mid && $method) {
                return;
            }
            $this->messageManager->addErrorMessage(__('Unable to update bank account.'));
            $this->redirect->redirect($this->response, '*/*/listaction');
        }
    }

    /**
     * Check User if Customer Logged
     *
     * @return mixed
     */
    public function checkUserIfCustomerLogged()
    {
        $loggedIn = $this->isLoggedIn();
        if (!$loggedIn) {
            return $this->redirect($this->response, '*/*/listaction');
        }
    }

    /**
     * Is Logged In
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->customerSession->create()->isLoggedIn();
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
     * Get ACH Status
     *
     * @return bool
     */
    public function getAchStatus()
    {
        return $this->_configModel->isAchActive();
    }

    /**
     * Is Credit Card Status
     *
     * @return bool
     */
    public function getCreditCardsStatus()
    {
        return $this->_configModel->isCreditCardEnabled();
    }

    /**
     * Get Ebiz method id
     *
     * @return int
     */
    public function getEbzcMethodId()
    {
        return $this->getRequest()->getParam('mid');
    }

    /**
     * Get Method Name
     *
     * @return string
     */
    public function getMethodName()
    {
        $method = $this->getRequest()->getParam('method');
        return urldecode($method);
    }

    /**
     * Get Customer Id
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getCustomerId()
    {
        return $this->getCustomer()->getId();
    }

    /**
     * Get Customer
     *
     * @return bool|Customer
     * @throws LocalizedException
     */
    public function getCustomer()
    {
        $customer = $this->customerSession->create()->getCustomer();
        if ($customer) {
            return $this->_customerFactory->create()->loadByEmail($customer->getEmail());
        }
        return false;
    }

    /**
     * Get Back Url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(self::CUSTOMER_ACCOUNT_URL);
    }

    /**
     * Get AddCardUrl
     *
     * @return string
     */
    public function getAddCardUrl()
    {
        return $this->getUrl(self::ADD_ACH_BANK_ACCOUNT_ACTION_URL);
    }

    /**
     * Get SaveUrl
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            self::SAVE_ACH_BANK_ACCOUNT_ACTION_URL,
            [
                '_secure' => true
            ]
        );
    }

    /**
     * Get config value with path
     *
     * @param mixed $path
     * @return int|string
     */
    public function getConfig($path)
    {
        return $this->_configModel->getConfig($path);
    }

    /**
     * Get CcTypes
     *
     * @return array
     */
    public function getCcTypes()
    {
        return $this->paymentConfig->getCcTypes();
    }

    /**
     * For user account payment methods listing
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        $customer = $this->getCustomer();
        $customerEbizToken = $customer->getEcCustToken();
        return $this->tranApi->getSavedAccounts($customerEbizToken);
    }

    /**
     * Get Ebiz customer id
     *
     * @return int|null
     */
    public function getEbzcCustId()
    {
        return $this->getCustomer()->getEcCustId();
    }

    /**
     * Get customer id
     *
     * @return int
     */
    public function getMageCustId()
    {
        return $this->getCustomer()->getEntityId();
    }

    /**
     * Get Customer Payment Method
     *
     * @return mixed|null
     */
    public function getCustomerPaymentMethod()
    {
        /**
         * Payment Method Id
         */
        $paymentMethodId = $this->getRequest()->getParam('mid');
        $customerToken = $this->getEbzCustomerToken();

        return $this->tranApi->getCustomerPaymentMethodProfile($customerToken, $paymentMethodId);
    }

    /**
     * Get Customer Ebizcharge Token
     *
     * @return array|mixed|null
     * @throws LocalizedException
     */
    public function getEbzCustomerToken()
    {
        return $this->getCustomer()->getEcCustToken();
    }

    /**
     * Get Ach Payment Types
     *
     * @return mixed
     */
    public function getAchPaymentTypes()
    {
        return PaymentInterface::ACH_PAYMENT_OPTION_TYPES;
    }
}
