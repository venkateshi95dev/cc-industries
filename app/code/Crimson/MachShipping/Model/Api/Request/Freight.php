<?php

namespace Crimson\MachShipping\Model\Api\Request;

use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Class Freight
 * @package Crimson\MachShipping\Model\Api\Request
 *
 * @method Freight setActionCode(string $actionCode)
 *
 * @method Freight getActionCode()
 * @method Freight getRecipientEmail()
 * @method Freight getMachMethod()
 */
class Freight extends RateRequest
{

}
