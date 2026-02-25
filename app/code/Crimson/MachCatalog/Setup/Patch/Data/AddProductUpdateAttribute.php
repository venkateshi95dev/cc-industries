<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 2:07 PM
 * @brief
 */

namespace Crimson\MachCatalog\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class AddProductUpdateAttribute implements
    DataPatchInterface,
    PatchVersionInterface
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

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $categorySetup->addAttribute(
            'catalog_product',
            'updated_from_mach',
            [
                'type'             => 'int',
                'backend'          => '',
                'frontend'         => '',
                'label'            => 'Updated from Mach',
                'input'            => 'text',
                'class'            => '',
                'source'           => '',
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'          => false,
                'required'         => false,
                'user_defined'     => false,
                'default'          => '',
                'searchable'       => false,
                'filterable'       => false,
                'comparable'       => false,
                'visible_on_front' => false,
                'unique'           => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.3.0';
    }
}
