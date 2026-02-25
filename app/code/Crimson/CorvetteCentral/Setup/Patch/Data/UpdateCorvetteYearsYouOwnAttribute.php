<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateCorvetteYearsYouOwnAttribute implements DataPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory
    ) {
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributeCode = 'corvette_years_you_own';
        $entityType = \Magento\Customer\Model\Customer::ENTITY;

        $attribute = $eavSetup->getAttribute($entityType, $attributeCode);

        if ($attribute && isset($attribute['attribute_id'])) {
            $eavSetup->updateAttribute(
                $entityType,
                $attributeCode,
                'source_model',
                \Crimson\CorvetteCentral\Model\Customer\Attribute\Source\CorvetteYears::class
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();

    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}

