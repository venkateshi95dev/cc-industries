<?php

namespace Crimson\LoginRegisterModal\Block\Form;

use Magento\Customer\Model\Form;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Login
 * @package Crimson\LoginRegisterModal\Block\Form
 */
class Login extends Template
{
    /**
     * @var int
     */
    private $_username = -1;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Url
     */
    protected $_customerUrl;

    public function __construct(
        Context $context,
        Session $customerSession,
        Url $customerUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = false;
        $this->_customerUrl = $customerUrl;
        $this->_customerSession = $customerSession;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout(): Login
    {
        return parent::_prepareLayout();
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl(): string
    {
        return $this->_customerUrl->getLoginPostUrl();
    }

    /**
     * Retrieve password forgotten url
     *
     * @return string
     */
    public function getForgotPasswordUrl(): string
    {
        return $this->_customerUrl->getForgotPasswordUrl();
    }

    /**
     * Check if autocomplete is disabled on storefront
     *
     * @return bool
     */
    public function isAutocompleteDisabled(): bool
    {
        return (bool)!$this->_scopeConfig->getValue(
            Form::XML_PATH_ENABLE_AUTOCOMPLETE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
