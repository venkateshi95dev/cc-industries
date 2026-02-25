<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddCustomerGroups implements DataPatchInterface
{

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {}

    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();
        $data[] = ['customer_group_id' => 3, 'customer_group_code' => 'General', 'tax_class_id' => 3];
        $data[] = ['customer_group_id' => 2, 'customer_group_code' => 'Wholesale', 'tax_class_id' => 3];
        $data[] = ['customer_group_id' => 21, 'customer_group_code' => 'Retailer', 'tax_class_id' => 3];

        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('customer_group'),
            ['customer_group_id', 'customer_group_code', 'tax_class_id'],
            $data
        );
        $this->moduleDataSetup->endSetup();
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [
            CreateCokerWebiste::class,
            CreateWVWebiste::class,
            SetShareCustomerAccountsToWebsite::class
        ];
    }
}
