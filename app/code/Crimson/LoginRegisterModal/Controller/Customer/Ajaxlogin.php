<?php

namespace Crimson\LoginRegisterModal\Controller\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Session\Generic;
use Magento\Framework\UrlFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Crimson\LoginRegisterModal\Model\Config;
use Magento\Framework\App\Response\RedirectInterface;

/**
 * Login controller
 *
 * @method RequestInterface getRequest()
 * @method Http getResponse()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Ajaxlogin extends Action
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Generic
     */
    protected $session;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var Data $helper
     */
    protected $helper;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var UrlInterface
     */
    protected $urlModel;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var RedirectInterface
     */
    protected $redirectInterface;

    public function __construct(
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        Data $helper,
        AccountManagementInterface $customerAccountManagement,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory,
        Config $config,
        RedirectInterface $redirectInterface,
        UrlFactory $urlFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->config = $config;
        $this->urlModel = $urlFactory->create();
        $this->redirectInterface = $redirectInterface;
    }

    /**
     * Get account redirect.
     * For release backward compatibility.
     *
     * @deprecated
     * @return AccountRedirect
     */
    protected function getAccountRedirect(): AccountRedirect
    {
        if (!is_object($this->accountRedirect)) {
            $this->accountRedirect = ObjectManager::getInstance()->get(AccountRedirect::class);
        }

        return $this->accountRedirect;
    }

    /**
     * @param $value
     */
    public function setAccountRedirect($value)
    {
        $this->accountRedirect = $value;
    }

    /**
     * @param $value
     */
    public function setScopeConfig($value)
    {
        $this->scopeConfig = $value;
    }

    /**
     * Login registered users and initiate a session.
     *
     * Expects a POST. ex for JSON {"username":"user@magento.com", "password":"userpassword"}
     *
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(): ResultInterface
    {
        $credentials = null;
        $httpBadRequestCode = 400;

        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        try {
            $credentials = $this->getRequest()->getParams();
        } catch (\Exception $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        if (!$credentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        $response = [
            'errors' => false,
            'message' => __('Login successful.')
        ];
        try {
            $customer = $this->customerAccountManagement->authenticate(
                $credentials['username'], $credentials['password']
            );

            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $this->customerSession->regenerateId();

            if ($this->config->isLoginSuccessValueCustom()) {
                $response['redirectUrl'] = $this->urlModel->getUrl($this->config->getAfterLoginUrl());
            } elseif ($this->config->isLoginSuccessValueCurrent()) {
                $redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
                if (!$this->config->isRedirectToDashboard() && $redirectRoute) {
                    $response['redirectUrl'] = $this->_redirect->success($redirectRoute);
                    $this->getAccountRedirect()->clearRedirectCookie();
                }
            } else {
                $response['redirectUrl'] = !empty($this->redirectInterface->getRefererUrl())
                ? $this->redirectInterface->getRefererUrl()
                : $this->urlModel->getUrl();
            }
        } catch (EmailNotConfirmedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (InvalidEmailOrPasswordException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => __($e->getMessage())
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

}
