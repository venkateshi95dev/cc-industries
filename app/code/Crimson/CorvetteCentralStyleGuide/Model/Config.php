<?php

namespace Crimson\CorvetteCentralStyleGuide\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'crimson_general/styleguide/enabled';

    /**
     * Constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    )
    {
    }

    /**
     * Check if the style guide is enabled.
     *
     * @param null|int|string $store
     * @return bool
     */
    public function isEnabled($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $store);
    }
}
