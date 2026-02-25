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
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

class AddImageIconCategoryAttribute implements DataPatchInterface
{
    public const ATTRIBUTE_CODE = 'category_icon_image';

    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * AddImageIconCategoryAttribute constructor.
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
            self::ATTRIBUTE_CODE,
            [
                'type' => 'varchar',
                'label' => 'Category Icon Image',
                'input' => 'image',
                'backend' => \Magento\Catalog\Model\Category\Attribute\Backend\Image::class,
                'required' => false,
                'sort_order' => 70,
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
}
