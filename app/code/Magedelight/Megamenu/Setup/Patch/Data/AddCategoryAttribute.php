<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magedelight\Megamenu\Model\Category\Attribute\Source\Width;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

class AddCategoryAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * AddCategoryAttribute constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
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
        $this->moduleDataSetup->getConnection()->startSetup();
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'md_submenu_width',
            [
                'type' => 'varchar',
                'label' => 'Submenu Width',
                'input' => 'select',
                'sort_order' => 10,
                'source' => Width::class,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => null,
                'group' => 'Md Mega Menu',
            ]
        );

        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'md_column_count',
            [
                'type' => 'text',
                'label' => 'Number of Columns with Subcategories',
                'input' => 'text',
                'sort_order' => 20,
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => null,
                'group' => 'Md Mega Menu',
            ]
        );

        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'md_label',
            [
                'type' => 'text',
                'label' => 'Menu Label Text',
                'input' => 'text',
                'sort_order' => 30,
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => null,
                'group' => 'Md Mega Menu',
            ]
        );

        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'md_label_text_color',
            [
                'type' => 'text',
                'label' => 'Text Color (hex)',
                'input' => 'text',
                'sort_order' => 40,
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => null,
                'group' => 'Md Mega Menu',
            ]
        );

        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'md_label_background_color',
            [
                'type' => 'text',
                'label' => 'Background Color (hex)',
                'input' => 'text',
                'sort_order' => 50,
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => null,
                'group' => 'Md Mega Menu',
            ]
        );

        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'md_category_editor',
            [
                'type' => 'text',
                'label' => 'Editor',
                'input' => 'textarea',
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'required' => false,
                'sort_order' => 60,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Md Mega Menu',
            ]
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
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get Version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '2.0.18';
    }
}
