<?php

/**
 * @author    Subhan
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_TierPrice
 */

namespace I95DevConnect\TierPrice\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Entitiesdata implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $setup->getConnection()->insertOnDuplicate(
            $setup->getTable('i95dev_entity'),
            [
                [
                    'entity_name' => 'Tier Price',
                    'entity_code' => 'tierprice',
                    'sort_order' => 6,
                    'support_for_inbound' => true,
                    'support_for_outbound' => false
                ]
            ]
        );
    }

    /**
     * Get Aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get Dependencies
     *
     * @return array
     */
    public static function getDependencies() // NOSONAR
    {
        return [

        ];
    }

    /**
     * Get patch version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '2.0.1';
    }
}
