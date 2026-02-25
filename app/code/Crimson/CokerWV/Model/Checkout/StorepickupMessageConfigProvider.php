<?php
namespace Crimson\CokerWV\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class StorepickupMessageConfigProvider implements ConfigProviderInterface
{
    public const XML_PATH_MESSAGE = 'checkout/options/storepickup_message_html';
    public const XML_PATH_LABEL = 'checkout/options/storepickup_label';
    public const XML_PATH_PICKUP_ACTIVE = 'carriers/amstorepickup/active';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {}

    public function getConfig(): array
    {
        $message = (string) $this->scopeConfig->getValue(self::XML_PATH_MESSAGE, ScopeInterface::SCOPE_STORE);
        $label = (string) $this->scopeConfig->getValue(self::XML_PATH_LABEL, ScopeInterface::SCOPE_STORE);
        $enabled = (bool) $this->scopeConfig->isSetFlag(self::XML_PATH_PICKUP_ACTIVE, ScopeInterface::SCOPE_STORE);
        return [
            'cokerwv' => [
                'storepickupMessageHtml' => $message,
                'storepickupLabel' => $label,
                'amstorepickupEnabled' => $enabled,
            ],
        ];
    }
}
