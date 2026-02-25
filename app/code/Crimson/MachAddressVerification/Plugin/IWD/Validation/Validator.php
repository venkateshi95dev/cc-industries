<?php

namespace Crimson\MachAddressVerification\Plugin\IWD\Validation;

use Crimson\MachAddressVerification\Model\Config as MachAddressConfig;
use Crimson\MachAddressVerification\Model\Mach\Validation;

class Validator
{

    /** @var MachAddressConfig $machAddrConfig */
    protected $machAddrConfig;

    /**
     * @var Validation $machValidator
     */
    private $machValidator;

    /**
     * Validator constructor.
     *
     * @param MachAddressConfig $machAddrConfig
     * @param Validation        $machValidator
     */
    public function __construct(
        MachAddressConfig $machAddrConfig,
        Validation $machValidator
    ) {
        $this->machValidator = $machValidator;
        $this->machAddrConfig  = $machAddrConfig;
    }

    /**
     * @param \IWD\AddressValidation\Model\Validation\Validator $subject
     * @param \Closure                                          $proceed
     *
     * @return Validation
     */
    public function aroundGetValidator(\IWD\AddressValidation\Model\Validation\Validator $subject, \Closure $proceed)
    {
        if ($this->machAddrConfig->isMachAddrValidationEnabled()) {
            $subject->setValidationMode(MachAddressConfig::MACH_ADDRESS_VALIDATION_CODE);
            $result = $this->machValidator;
        } else {
            $result = $proceed();
        }

        return $result;
    }
}