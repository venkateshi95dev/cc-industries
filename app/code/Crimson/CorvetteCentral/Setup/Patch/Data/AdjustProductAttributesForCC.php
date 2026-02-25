<?php

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;


class AdjustProductAttributesForCC implements DataPatchInterface
{

    protected EavSetup $eavSetup;

    public function __construct(
        protected ModuleDataSetupInterface $setup,
        protected CatalogConfig $catalogConfig,
        protected EavConfig $eavConfig,
        protected AttributeManagementInterface $attributeManagement,
        protected ProductAttributeRepositoryInterface $productAttributeRepository,
        protected ProductRepositoryInterface $productRepository,
        protected SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        protected State $state,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->eavSetup = $eavSetupFactory->create([
            'setup' => $setup
        ]);
    }

    public function apply()
    {
        // Path to the JSON file
        $jsonFilePath = BP . '/app/code/Crimson/CorvetteCentral/Setup/data/product_attributes_3.json';

        // Parse JSON file
        if (!file_exists($jsonFilePath)) {
            throw new \Exception("JSON file not found: " . $jsonFilePath);
        }

        $jsonData = file_get_contents($jsonFilePath);
        $attributes = json_decode($jsonData, true);

        foreach($attributes['attributes'] as $attribute) {

            //remove attr to be recreated
            $this->eavSetup->removeAttribute(Product::ENTITY, $attribute['code']);

            //re-adding
            $this->eavSetup->addAttribute(Product::ENTITY, $attribute['code'], [
                'label' => $attribute['name'],
                'type' => 'varchar',
                'input' => $attribute['input'],

                'source' =>  Table::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,

                'required' => false,
                'user_defined' => true,
                'system' => false,
                'searchable' => false,
                'filterable' => $attribute['filterable'],
                'comparable' => false,
                'visible_on_front' => $attribute['visible_on_front'],
                'used_in_product_listing' => true,

                'option' => [
                    'values' => $attribute['values'] ?? []
                ]
            ]);
        }

    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [
            CreateAttributeSetsForCC::class,
            CreateProductAttributesForCC2::class,
        ];
    }
}
