<?php
/**
 * @namespace   Crimson
 * @module      Customer
 * @author      Ryan Simmons
 * @email       rsimmons@crimsonagility.com
 * @date        9/4/2020 3:24 PM
 * @brief
 */

namespace Crimson\Customer\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

class FixCustomerStoreAndWebsiteIds implements DataPatchInterface
{
    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface  */
    protected $moduleDataSetup;

    /**
     * FixCustomerStoreAndWebsiteIds constructor.
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->update(
            $this->moduleDataSetup->getTable('customer_entity'),
            ['store_id' => 1, 'website_id' => 1]
        );
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
