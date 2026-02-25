<?php
namespace Crimson\BuyNow\Cron;

use Crimson\BuyNow\Model\BuyNowConfig;
use Crimson\BuyNow\Service\Exporter;
use Crimson\BuyNow\Model\Logger;

class GenerateFeeds
{

    public function __construct(
        protected Exporter $exporter,
        protected BuyNowConfig $buyNowConfig,
        protected Logger $logger
    ) {}

    public function execute(): void
    {
        try {
            if ($this->buyNowConfig->isEnabled()) {
                $this->exporter->execute();
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
