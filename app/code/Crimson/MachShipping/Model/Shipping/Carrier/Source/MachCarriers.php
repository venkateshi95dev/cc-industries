<?php

namespace Crimson\MachShipping\Model\Shipping\Carrier\Source;

use Magento\Framework\Data\OptionSourceInterface;

class MachCarriers implements OptionSourceInterface
{

    public function __construct(
        protected Method $method
    ) {}

    public function toOptionArray(): array
    {
        return $this->method->machCarriersToOptionArray();
    }
}
