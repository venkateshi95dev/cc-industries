<?php

namespace Crimson\AttributeSearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class AttributeSearchConfig
{
    private ScopeConfigInterface $scopeConfig;

    public const XML_PATH_ENABLED = 'crimson_attribute_search/general/enabled';
    public const XML_PATH_FAST_SIMON_ENABLED = 'crimson_attribute_search/general/fast_simon';
    public const XML_PATH_FAST_SIMON_INSTANT_SEARCH_ENABLED = 'crimson_attribute_search/general/fast_simon_instant_search';
    public const XML_PATH_ATTR = 'crimson_attribute_search/general/enabled_attributes';
    public const XML_PATH_TITLE = 'crimson_attribute_search/general/select_title';
    public const XML_PATH_TOOLTIP = 'crimson_attribute_search/general/tooltip_text';

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if module is enabled
     */
    public function isModuleEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if should use Fast Simon integration
     */
    public function isFastSimonEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_FAST_SIMON_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if should use Fast Simon integration
     */
    public function isFastSimonInstantSearchEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_FAST_SIMON_INSTANT_SEARCH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Fetches all attribute codes selected
     */
    public function getEnabledAttributes(): array
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_ATTR, ScopeInterface::SCOPE_STORE);
    
        if (!$value) {
            return [];
        }
        return explode(',', $value);
    }

    /**
     * Get input select title
     */
    public function getDropdownTitle(): ?string
    {
        return $value = $this->scopeConfig->getValue(self::XML_PATH_TITLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get tooltip content text
     */
    public function getTooltipContent(): ?string
    {
        return $value = $this->scopeConfig->getValue(self::XML_PATH_TOOLTIP, ScopeInterface::SCOPE_STORE);
    }
}
