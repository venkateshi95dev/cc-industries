<?php

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\CokerWV\Setup\Patch\Data\CreateCokerWebiste;
use Crimson\CokerWV\Setup\Patch\Data\CreateWVWebiste;
use Crimson\CokerWV\Setup\Patch\Data\SetShareCustomerAccountsToWebsite;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddCCCustomerGroups implements DataPatchInterface
{

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {}

    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();
        $data[] = ['customer_group_id' => 31, 'customer_group_code' => 'CC-CCEMPLOY', 'tax_class_id' => 3];
        $data[] = ['customer_group_id' => 32, 'customer_group_code' => 'CC-GENTRADE', 'tax_class_id' => 3];
        $data[] = ['customer_group_id' => 33, 'customer_group_code' => 'CC-INTERCO', 'tax_class_id' => 3];
        $data[] = ['customer_group_id' => 34, 'customer_group_code' => 'CC-JOBBER', 'tax_class_id' => 3];
        $data[] = ['customer_group_id' => 35, 'customer_group_code' => 'CC-JOBBER SPC', 'tax_class_id' => 3];
        $data[] = ['customer_group_id' => 36, 'customer_group_code' => 'CC-JOBBERPLUS', 'tax_class_id' => 3];
        $data[] = ['customer_group_id' => 37, 'customer_group_code' => 'CC-JOBBPLU*SP', 'tax_class_id' => 3];
        $data[] = ['customer_group_id' => 38, 'customer_group_code' => 'CC-MILITARY', 'tax_class_id' => 3];
        $data[] = ['customer_group_id' => 39, 'customer_group_code' => 'CC-PREFERRED', 'tax_class_id' => 3];
        $data[] = ['customer_group_id' => 40, 'customer_group_code' => 'CC-RETAIL', 'tax_class_id' => 3];
        $data[] = ['customer_group_id' => 41, 'customer_group_code' => 'CC-STARTTOFIN', 'tax_class_id' => 3];
        $data[] = ['customer_group_id' => 42, 'customer_group_code' => 'CC-TURN5', 'tax_class_id' => 3];
        $data[] = ['customer_group_id' => 43, 'customer_group_code' => 'CC-VENDOR JOB', 'tax_class_id' => 3];

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
            CreateCorvetteCentralWebiste::class,
            SetShareCustomerAccountsToWebsite::class
        ];
    }
}
