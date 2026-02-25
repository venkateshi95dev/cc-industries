<?php
/*
 * @author zahirabbas badi
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95Dev_OrderEdit
 */
declare(strict_types=1);

namespace I95DevConnect\OrderEdit\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class CreateEnitytEntry implements DataPatchInterface
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

        $this->moduleDataSetup->startSetup();
        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('i95dev_entity'),
            [
                ['entity_name' => 'Edit Order',
                    'entity_code' => 'editOrder',
                    'sort_order'=>17,
                    'support_for_inbound' => true,
                    'support_for_outbound' => false]
            ]
        );
        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies() //NOSONAR
    {
        return [];
    }
}
