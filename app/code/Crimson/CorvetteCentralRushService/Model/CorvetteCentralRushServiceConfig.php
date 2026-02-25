<?php

namespace Crimson\CorvetteCentralRushService\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CorvetteCentralRushServiceConfig
{

    CONST XPATH_RUSH_SERVICE_ENABLED = "shipping/rush_service/enabled";
    CONST XPATH_RUSH_SERVICE_LABEL   = "shipping/rush_service/rush_label";
    CONST XPATH_RUSH_SERVICE_MESSAGE = "shipping/rush_service/rush_message";

    public function __construct(
        protected ScopeConfigInterface $scopeConfig
    ) {}

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_RUSH_SERVICE_ENABLED, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getLabel(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_RUSH_SERVICE_LABEL, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getMessage(): string
    {
        return (string)$this->scopeConfig->getValue(self::XPATH_RUSH_SERVICE_MESSAGE, ScopeInterface::SCOPE_WEBSITE);
    }
}
