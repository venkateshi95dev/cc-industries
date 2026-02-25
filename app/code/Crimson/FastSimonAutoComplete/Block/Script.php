<?php
namespace Crimson\FastSimonAutoComplete\Block;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Script extends Template
{
    const XML_PATH_ENABLED = 'autosuggest/general/autosuggest_enabled';
    const XML_PATH_UUID    = 'autosuggest/api/uuid';

    public function __construct(
        Template\Context $context,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getUUID(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_UUID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getStoreId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }
}
