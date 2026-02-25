<?php

namespace Crimson\MachShipping\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Crimson\MachShipping\Model\Config;

/**
 * Class ConfigProvider
 * @package Crimson\MachShipping\Model
 */
class ConfigProvider implements ConfigProviderInterface
{

    protected Config $config;


    public function __construct(
        Config $config
    )
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'in_transit_message' => $this->config->getTransitTimeMessage(),
        ];
    }

}
