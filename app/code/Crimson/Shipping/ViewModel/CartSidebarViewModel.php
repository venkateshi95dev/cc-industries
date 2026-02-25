<?php

namespace Crimson\Shipping\ViewModel;

use Crimson\Shipping\Model\Config;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CartSidebarViewModel implements ArgumentInterface
{
    protected Config $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function isCartSidebarTaxMessageEnabled(): bool
    {
        return $this->config->isCartSidebarTaxMessageEnabled();
    }

    public function getCartSidebarTaxMessage(): ?string
    {
        return $this->config->getCartSidebarTaxMessage();
    }

    public function isCartSidebarShippingUpdateEnabled(): bool
    {
        return $this->config->isCartSidebarShippingUpdateEnabled();
    }

    public function getCartSidebarShippingUpdate(): ?string
    {
        return $this->config->getCartSidebarShippingUpdate();
    }
}
