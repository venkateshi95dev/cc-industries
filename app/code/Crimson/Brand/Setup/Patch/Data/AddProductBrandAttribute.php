<?php
/**
 * @namespace   Crimson
 * @module      Brands
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        03/15/2019
 */
namespace Crimson\Brand\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddProductBrandAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory     $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    public function apply()
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $productEntityId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $categorySetup->removeAttribute($productEntityId,'brands');
        $categorySetup->addAttribute(
            $productEntityId,
            'brands',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Brands',
                'input' => 'select',
                'class' => '',
                'source' => 'Crimson\Brand\Model\Config\Source\BrandOptions',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => null,
                'searchable' => true,
                'filterable' => true,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'system' => 0,
                'group' => 'General'
            ]
        );

    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [
            \Crimson\Attributes\Setup\Patch\Data\AddProductAttributes::class,
            \Crimson\Attributes\Setup\Patch\Data\AddProductUpdateAttribute::class
        ];
    }
}
