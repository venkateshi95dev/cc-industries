<?php

namespace Crimson\AheadworksNmiCustomizations\Plugin\Gateway\Request;

use Aheadworks\Nmi\Gateway\Request\PaymentDataBuilder;
use Crimson\AheadworksNmiCustomizations\Model\AheadworksNmiCustomizationsConfig;

class PaymentDataBuilderPlugin
{

    public function __construct(
        private readonly AheadworksNmiCustomizationsConfig $config
    ) {}

    public function afterBuild(PaymentDataBuilder $subject, $result)
    {
        if ($this->config->getValidateModeOnly()) {
            $result[PaymentDataBuilder::AMOUNT] = 0;
        }

        return $result;
    }
}
