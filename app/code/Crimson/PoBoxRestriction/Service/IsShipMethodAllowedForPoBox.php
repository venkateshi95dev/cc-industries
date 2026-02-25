<?php

namespace Crimson\PoBoxRestriction\Service;

use Crimson\PoBoxRestriction\Helper\Data as PoBoxHelper;

/**
 * Class IsShipMethodAllowedForPoBox
 * @package Crimson\PoBoxRestriction\Service
 */
class IsShipMethodAllowedForPoBox
{

    CONST SHIP_DEST_STREET_FIELD = "dest_street";

    /**
     * @var PoBoxHelper
     */
    protected $poBoxHelper;

    public function __construct(
        PoBoxHelper $poBoxHelper
    )
    {
        $this->poBoxHelper = $poBoxHelper;
    }

    /**
     * @param string $destStreet
     * @param string $carrierCode
     * @return bool
     */
    public function is(string $destStreet, string $carrierCode): bool
    {
        if (!$this->poBoxHelper->isRestrictionEnabledForShipMethods()) {
            return true;
        }

        if (!$destStreet || !$carrierCode) {
            return true;
        }

        if ($this->poBoxHelper->isPoStreet($destStreet)
            && !in_array($carrierCode, $this->poBoxHelper->getAllowedShipMethodsForPoBox())
        ) {
            return false;
        }

        return true;
    }
}
