<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateAttributesScopeGlobal implements DataPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory
    ) {}

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributeCodes = [
            "cc_airshipmentok",
            "cc_C10",
            "cc_C1_53_62",
            "cc_C2_63_67",
            "cc_C3_68_82",
            "cc_C4_84_96",
            "cc_C5_97_04",
            "cc_C6_05_13",
            "cc_C7_14_",
            "cc_C8",
            "cc_C9",
            "cc_core_charge_required",
            "cc_ecommerce_lead_days",
            "cc_ecommerce_logo",
            "cc_gmpartnumber",
            "cc_height",
            "cc_length",
            "cc_net_weight",
            "cc_packageexists",
            "cc_packages",
            "cc_paragonitemno",
            "cc_searchdescription",
            "cc_truck_freight_type",
            "cc_width"
        ];
        $entityType = Product::ENTITY;
        foreach ($attributeCodes as $code) {
            try {
                $attribute = $eavSetup->getAttribute($entityType, $code);
                if ($attribute && isset($attribute['attribute_id'])) {
                    $eavSetup->updateAttribute(
                        $entityType,
                        $code,
                        'is_global',
                        ScopedAttributeInterface::SCOPE_GLOBAL
                    );
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}

