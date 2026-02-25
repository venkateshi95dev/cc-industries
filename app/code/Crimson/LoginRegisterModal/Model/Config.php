<?php

namespace Crimson\LoginRegisterModal\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Crimson\LoginRegisterModal\Model
 */
class Config
{
    CONST XPATH_LOGIN_VALUE = 'loginregistermodal/login/success';
    CONST XPATH_REDIRECT_AFTER_LOGIN = 'loginregistermodal/login/redirect_after_login';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getLoginSuccessValue(): string
    {
        return (string) $this->scopeConfig->getValue(self::XPATH_LOGIN_VALUE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isLoginSuccessValueCustom(): bool
    {
        return $this->getLoginSuccessValue() == 'custom';
    }

    /**
     * @return bool
     */
    public function isLoginSuccessValueCurrent(): bool
    {
        return $this->getLoginSuccessValue() == 'current';
    }

    /**
     * @return string
     */
    public function getAfterLoginUrl(): string
    {
        return (string) $this->scopeConfig->getValue(self::XPATH_REDIRECT_AFTER_LOGIN, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isRedirectToDashboard(): bool
    {
        return $this->scopeConfig->isSetFlag('customer/startup/redirect_dashboard', ScopeInterface::SCOPE_WEBSITE);
    }
}
