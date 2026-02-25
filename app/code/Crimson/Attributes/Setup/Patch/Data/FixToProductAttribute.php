<?php
/**
 * @namespace   Crimson
 * @module      Attrbitues
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        03/07/2019
 */
namespace Crimson\Attributes\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class FixToProductAttribute implements DataPatchInterface
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

        $categorySetup->removeAttribute(
            $productEntityId,
            'productvideosgeneral'
        );
        $categorySetup->addAttribute(
            $productEntityId,
            'productvideosgeneral',
            [
                'label'         => 'Generic Product Videos',
                'input'         => 'textarea',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'user_defined'  => true,
                'filterable'    => false
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
            \Crimson\Attributes\Setup\Patch\Data\AddProductAttributes::class
        ];
    }
}