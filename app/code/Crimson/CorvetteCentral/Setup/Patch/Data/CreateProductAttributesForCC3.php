<?php

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateProductAttributesForCC3 implements DataPatchInterface
{
    protected EavSetup $eavSetup;

    public function __construct(
        protected ModuleDataSetupInterface $setup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetup = $eavSetupFactory->create([
            'setup' => $setup
        ]);
    }

    public function apply()
    {
        $jsonFilePath = BP . '/app/code/Crimson/CorvetteCentral/Setup/data/product_attributes_3.json';
        $jsonData = file_get_contents($jsonFilePath);
        $attributesData = json_decode($jsonData, true);

        foreach ($attributesData['attributes'] as $attribute) {
            $attributeConfig = [
                'label' => $attribute['name'],
                'type' => $attribute['type'] ?? 'varchar',
                'input' => $attribute['input'],
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'required' => false,
                'user_defined' => true,
                'system' => false,
                'searchable' => false,
                'filterable' => $attribute['filterable'],
                'comparable' => false,
                'visible_on_front' => $attribute['visible_on_front'],
                'used_in_product_listing' => true,
            ];

            if (isset($attribute['source']) && $attribute['source'] === 'boolean') {
                $attributeConfig['source'] = Boolean::class;
            } elseif (!empty($attribute['values'])) {
                $attributeConfig['source'] = Table::class;
                $attributeConfig['option'] = [
                    'values' => $attribute['values']
                ];
            }

            $this->eavSetup->addAttribute(Product::ENTITY, $attribute['code'], $attributeConfig);
        }
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
