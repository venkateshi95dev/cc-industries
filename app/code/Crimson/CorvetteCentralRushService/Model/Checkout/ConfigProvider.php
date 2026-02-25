<?php

namespace Crimson\CorvetteCentralRushService\Model\Checkout;

use Crimson\CorvetteCentralRushService\Service\RushService;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class ConfigProvider implements ConfigProviderInterface
{

    public function __construct(
        protected RushService $rushService,
        protected CheckoutSession $checkoutSession
    ) {}

    public function getConfig(): array
    {
        $config = [];
        if ($this->rushService->isEnabled() && $this->rushService->isEligible()) {
            try {
                $config['rush_service']['rush_selected'] = $this->rushService->doesCartHaveRushService($this->checkoutSession->getQuote());
                $config['rush_service']['rush_label']    = $this->rushService->getLabel();
                $config['rush_service']['rush_message']  = $this->rushService->getMessage();
                $config['rush_service']['url']           = $this->rushService->getUrl();
            } catch (\Exception $e) {

            }
        }

        return $config;
    }
}
