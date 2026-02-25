<?php

namespace Crimson\Payware\Model\Checkout;

use Crimson\Payware\Model\Config;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 * @package Crimson\Payware\Model\Checkout
 */
class ConfigProvider implements ConfigProviderInterface
{

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'payware_transaction_customer_message' => $this->config->getTransactionCustomerMessage()
        ];
    }
}
