<?php

namespace Crimson\CartTopMessages\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigurableMessage implements ArgumentInterface
{
    // for future use, in case there were more messages.
    const TOTAL = 1;

    public function __construct(
        protected ScopeConfigInterface $scopeConfig
    )
    {}

    public function getMessages(): array
    {
        $list = [];
        $path = 'checkout/configurable_cart_message/';
        $scope = ScopeInterface::SCOPE_STORE;
        for ($i = 1; $i <= self::TOTAL; $i++) {
            $finalPathEnabled = $path . 'message_' . $i . '_enabled';
            $finalPathText = $path . 'message_' . $i . '_text';
            if ($this->scopeConfig->isSetFlag($finalPathEnabled, $scope)) {
                $list[$i] = (string)$this->scopeConfig->getValue($finalPathText, $scope);
            }
        }
        return $list;
    }
}
