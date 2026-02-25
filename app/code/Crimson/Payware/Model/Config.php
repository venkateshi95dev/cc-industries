<?php

namespace Crimson\Payware\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Crimson\MachCatalog\Model
 */
class Config
{
    const XPATH_CUSTOMER_TRANSACTION_MESSAGE         = 'payment/payware/card_validation_message';

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
    public function getTransactionCustomerMessage(): string
    {
        return (string) $this->scopeConfig->getValue(self::XPATH_CUSTOMER_TRANSACTION_MESSAGE, ScopeInterface::SCOPE_WEBSITE);
    }
}
