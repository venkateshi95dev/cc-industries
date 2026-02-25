<?php
/**
 * @namespace   Crimson
 * @module      Attrbitues
 * @author      Jennifer Nodwell
 * @email       jnodwell@crimsonagility.com
 * @date        05/27/2019
 */

namespace Crimson\Attributes\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer; //only for aliasing
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class AddCustomerAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;
    private $eavSetupFactory;

    private $eavConfig;


    private $attributeSetFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory     $customerSetupFactory
     */
    public function __construct(

        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup      = $moduleDataSetup;
        $this->eavSetupFactory      = $eavSetupFactory;
        $this->eavConfig            = $eavConfig;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
    }

    public function apply()
    {
        $customerSetup  = $this->customerSetupFactory->create(
            ['setup' => $this->moduleDataSetup]
        );
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(
            'customer'
        );
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet     = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'car_demos',
            [
                'type'       => 'varchar',
                'label'      => 'Corvette/Generation Year',
                'input'      => 'multiselect',
                'required'   => false,
                'sort_order' => 100,
                'visible'    => true,
                'backend'    => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'source'     => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'system'     => false,
                'option'     =>
                    [
                        'values' =>
                            [
                                3303 => '1953-1962 C1',
                                3302 => '1963-1967 C2',
                                3301 => '1968-1982 C3',
                                3300 => '1984-1996 C4',
                                3299 => '1997-2004 C5',
                                3298 => '2005-2013 C6',
                                3297 => '2014-2018 C7',
                            ],
                    ],
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY, 'car_demos'
        )
            ->addData(
                [
                    'attribute_set_id'   => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms'      => ['adminhtml_customer'],
                    //you can use other forms also ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
                ]
            );
        $attribute->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'ncoa_member',
            [
                'type'       => 'varchar',
                'label'      => 'NOCA Member Number',
                'input'      => 'text',
                'required'   => false,
                'sort_order' => 100,
                'visible'    => true,
                'system'     => false,
            ]
        );
        $attribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY, 'ncoa_member'
        )
            ->addData(
                [
                    'attribute_set_id'   => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms'      => ['adminhtml_customer'],
                    //you can use other forms also ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
                ]
            );
        $attribute->save();


    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [

        ];
    }
}