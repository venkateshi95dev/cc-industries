<?php
/**
 * @namespace   Crimson
 * @module      Demographics
 * @author      Chad Carlson
 * @email       ccarlson@crimsonagility.com
 * @date        07/16/2020
 */
namespace Crimson\Demographics\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetup;

class UpdateCarDemosAttribute implements DataPatchInterface
{
    private $eavSetup;

    public function __construct(
        EavSetup $eavSetup
    ) {
        $this->eavSetup = $eavSetup;
    }

    public function apply()
    {
        $this->eavSetup->updateAttribute('customer', 'car_demos', 'is_user_defined', true);
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [];
    }
}