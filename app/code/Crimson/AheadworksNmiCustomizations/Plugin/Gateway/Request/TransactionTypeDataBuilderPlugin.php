<?php

namespace Crimson\AheadworksNmiCustomizations\Plugin\Gateway\Request;

use Aheadworks\Nmi\Gateway\Request\TransactionTypeDataBuilder;
use Crimson\AheadworksNmiCustomizations\Model\AheadworksNmiCustomizationsConfig;

class TransactionTypeDataBuilderPlugin
{

    public function __construct(
        private readonly AheadworksNmiCustomizationsConfig $config
    ) {}

    public function afterBuild(TransactionTypeDataBuilder $subject, $result)
    {
        if ($this->config->getValidateModeOnly()) {
            $result[TransactionTypeDataBuilder::TYPE] = 'validate';
        }

        return $result;
    }
}
