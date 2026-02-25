<?php

/**
 * @author Subhan
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ConfigurableProducts
 */

namespace I95DevConnect\ConfigurableProducts\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class CategoryAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    public $categorySetupFactory;

    /**
     * CategoryAttributes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $categorySetup->addAttribute(
            Product::ENTITY,
            'variant_id',
            [
                'label' => __('Variant Id'),
                'input' => 'text',
                'required' => false,
                'sort_order' => 42,
                'visible' => false,
                'system' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_searchable_in_grid' => false
            ]
        );

        $productEntityTypeId = $categorySetup->getEntityTypeId('catalog_product');
        $id = $categorySetup->getAttributeId($productEntityTypeId, 'variant_id');
        $categorySetup->updateAttribute($productEntityTypeId, $id, 'is_used_in_grid', 1);
        $categorySetup->updateAttribute($productEntityTypeId, $id, 'is_visible_in_grid', 1);
        $categorySetup->updateAttribute($productEntityTypeId, $id, 'is_filterable_in_grid', 1);
        $categorySetup->updateAttribute($productEntityTypeId, $id, 'is_searchable_in_grid', 1);
        $categorySetup->updateAttribute($productEntityTypeId, $id, 'apply_to', 'simple');
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
    public static function getDependencies() //NOSONAR
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
        return '2.0.7';
    }
}
