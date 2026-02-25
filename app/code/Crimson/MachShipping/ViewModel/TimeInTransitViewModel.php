<?php

namespace Crimson\MachShipping\ViewModel;

use Crimson\MachShipping\Model\Config;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class TimeInTransitViewModel implements ArgumentInterface
{
    protected Config $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getTransitTimeMessage(): string
    {
        return $this->config->getTransitTimeMessage();
    }
}
