<?php

namespace Crimson\Catalog\Cron;

use Crimson\Catalog\Model\Config;
use Crimson\Catalog\Service\UpdateSpecialPrice;
use Magento\Store\Model\StoreManagerInterface;

class SpecialPriceUpdate
{

    public function __construct(
        protected Config $config,
        protected StoreManagerInterface $storeManagerInterface,
        protected UpdateSpecialPrice $updateSpecialPrice
    ) {}

    public function execute(): void
    {
        foreach ($this->storeManagerInterface->getWebsites() as $website) {
            if (!$this->config->isSpecialPriceCronEnabled($website->getId())) {
                continue;
            }

            $this->updateSpecialPrice->run((int) $website->getId());
        }
    }
}
