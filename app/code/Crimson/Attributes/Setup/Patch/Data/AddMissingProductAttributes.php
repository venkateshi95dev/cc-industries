<?php
/**
 * @namespace   Crimson
 * @module      Attrbitues
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        04/12/2019
 */
namespace Crimson\Attributes\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddMissingProductAttributes implements DataPatchInterface
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
            'primarybin'
        );
        $categorySetup->addAttribute(
            $productEntityId,
            'primarybin',
            [
                'label'         => 'Primary Bin',
                'input'         => 'text',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->removeAttribute(
            $productEntityId,
            'hazardous_material'
        );
        $categorySetup->addAttribute(
            $productEntityId,
            'hazardous_material',
            [
                'label'         => 'Hazardous Material',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'No',
                    'Yes'
                ]]
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