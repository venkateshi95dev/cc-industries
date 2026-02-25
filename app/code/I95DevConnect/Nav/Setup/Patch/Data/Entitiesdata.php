<?php
/**
 * @author Subhan
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_Nav
 */

namespace I95DevConnect\Nav\Setup\Patch\Data;

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
        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('i95dev_entity'),
            [
                [
                    'entity_name' => 'Customer Group',
                    'entity_code' => 'CustomerGroup',
                    'sort_order'=>4,
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
    public function getAliases() // NOSONAR
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
        return [];
    }

    /**
     * Get patch version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '2.0.2';
    }
}
