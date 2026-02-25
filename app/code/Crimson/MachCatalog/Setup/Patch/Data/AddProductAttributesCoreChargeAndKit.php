<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/6/2019 8:50 AM
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
class AddProductAttributesCoreChargeAndKit implements
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
        $this->moduleDataSetup      = $moduleDataSetup;
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
            'iskit', [

                'type'                    => 'int',
                'label'                   => 'Is Kit',
                'input'                   => 'boolean',
                'required'                => true,
                'sort_order'              => '30',
                'global'                  => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'                 => true,
                'user_defined'            => true,
                'searchable'              => false,
                'filterable'              => false,
                'comparable'              => false,
                'visible_on_front'        => false,
                'unique'                  => false,
                'group'                   => 'Mach Info',
                'used_in_product_listing' => false,
                'is_used_in_grid'         => true,
                'is_visible_in_grid'      => true,
                'is_filterable_in_grid'   => true,
            ]
        );

        $categorySetup->addAttribute(
            'catalog_product',
            'corecharge',
            [
                'type'                    => 'int',
                'label'                   => 'Core Charges',
                'input'                   => 'select',
                'required'                => false,
                'sort_order'              => '30',
                'global'                  => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source'                  => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'default'                 => null,
                'visible'                 => true,
                'user_defined'            => true,
                'searchable'              => false,
                'filterable'              => false,
                'comparable'              => false,
                'visible_on_front'        => false,
                'unique'                  => false,
                'group'                   => 'Mach Info',
                'used_in_product_listing' => false,
                'is_used_in_grid'         => true,
                'is_visible_in_grid'      => true,
                'is_filterable_in_grid'   => false,
                'option'                  => [
                    'values' => [
                        '100',
                        '115',
                        '1200',
                        '125',
                        '1375',
                        '150',
                        '1500',
                        '180',
                        '1800',
                        '200',
                        '225',
                        '240',
                        '25',
                        '250',
                        '2750',
                        '300',
                        '40',
                        '400',
                        '45',
                        '450',
                        '50',
                        '500',
                        '60',
                        '600',
                        '65',
                        '75',
                        '750',
                        '80',
                        '800',
                        '90',
                        '900',
                        '35',
                        '20',
                        '650',
                        '1000',
                        '275',
                        '350',
                        '375',
                        '320',
                    ]]
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
