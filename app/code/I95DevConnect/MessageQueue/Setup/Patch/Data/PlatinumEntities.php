<?php

/**
 * @author Subhan
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PlatinumEntities implements DataPatchInterface
{
    public const ENTITY_NAME = 'entity_name';
    public const ENTITY_CODE = 'entity_code';
    public const SORT_ORDER = 'sort_order';
    public const SUPPORT_FOR_INBOUND = 'support_for_inbound';
    public const SUPPORT_FOR_OUTBOUND = 'support_for_outbound';

    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * PlatinumEntities constructor.
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
        $i95DevEntitiesArr = [
            ['Customer Group', 'CustomerGroup', 8, true, true],
            ['Customer', 'Customer', 11, true, true],
            ['Address', 'address', 12, true, true],
            ['Product', 'product', 4, true, true],
            ['Inventory', 'inventory', 7, true, false],
            ['Order', 'order', 13, true, true],
            ['Invoice', 'invoice', 15, true, false],
            ['Shipment', 'shipment', 14, true, false],
        ];

        $columnData = [];
        foreach ($i95DevEntitiesArr as $data) {
            $columnData[] = [
                self::ENTITY_NAME => $data[0],
                self::ENTITY_CODE => $data[1],
                self::SORT_ORDER => $data[2],
                self::SUPPORT_FOR_INBOUND => $data[3],
                self::SUPPORT_FOR_OUTBOUND => $data[4]
            ];
        }

        $setup->getConnection()->insertOnDuplicate(
            $setup->getTable('i95dev_entity'),
            $columnData
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
        return '2.1.8';
    }
}
