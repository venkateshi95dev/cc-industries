<?php

namespace Crimson\MachCustomer\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Model\Customer;

class AddCustomerAttributes  implements
    DataPatchInterface,
    PatchVersionInterface
{
    private $eavSetupFactory;

    private $eavConfig;

    private $customerSetupFactory;

    private $attributeSetFactory;

    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig       = $eavConfig;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    public function apply()
    {
            $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();

            $attributeSet     = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

            //mach_pin
            $customerSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'mach_pin',
                [
                    'type'         => 'varchar',
                    'label'        => 'MACH PIN',
                    'note'         => 'Will be auto-generated if not set.',
                    'input'        => 'text',
                    'user_defined' => false,
                    'required'     => false,
                    'sort_order'   => 120,
                    'visible'      => true,
                    'system'       => false,
                    'position'     => 102,
                ]
            );

            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'mach_pin')
                ->addData(
                    [
                        'attribute_set_id'   => $attributeSetId,
                        'attribute_group_id' => $attributeGroupId,
                        'used_in_forms'      => ['adminhtml_customer'],
                        //you can use other forms also ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
                    ]
                );

            $attribute->save();

            //mach_price_level
            $customerSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'mach_price_level',
                [
                    'type'         => 'int',
                    'input'        => 'text',
                    'label'        => 'Customer Mach Price Level',
                    'global'       => 1,
                    'visible'      => false,
                    'default'      => 0,
                    'required'     => false,
                    'user_defined' => false,
                    'comment'      => 'Customer Price Level from Mach',
                ]
            );

            //is_club_member
            $customerSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'is_club_member',
                [
                    'type'         => 'int',
                    'label'        => 'Is Club Member',
                    'input'        => 'boolean',
                    'source'       => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'user_defined' => false,
                    'default'      => 0,
                    'global'       => 1,
                    'required'     => false,
                    'visible'      => true,
                    'system'       => false,
                    'sort_order'   => 130,
                    'position'     => 103,
                ]
            );

            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'is_club_member')
                ->addData(
                    [
                        'attribute_set_id'   => $attributeSetId,
                        'attribute_group_id' => $attributeGroupId,
                        'used_in_forms'      => ['adminhtml_customer'],
                    ]
                );

            $attribute->save();

            //club_expiration_date
            $customerSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'club_expiration_date',
                [
                    'type'         => 'datetime',
                    'label'        => 'Club Expiration Date',
                    'input'        => 'date',
                    'class'        => 'validate-date',
                    'user_defined' => false,
                    'default'      => '',
                    'global'       => 1,
                    'required'     => false,
                    'visible'      => true,
                    'system'       => false,
                    'sort_order'   => 140,
                    'position'     => 104,
                ]
            );
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'club_expiration_date')
                ->addData(
                    [
                        'attribute_set_id'   => $attributeSetId,
                        'attribute_group_id' => $attributeGroupId,
                        'used_in_forms'      => ['adminhtml_customer'],
                    ]
                );

            $attribute->save();

            //club_discount
            $customerSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'club_discount',
                [
                    'type'         => 'decimal',
                    'label'        => 'Club Discount Percentage',
                    'input'        => 'text',
                    'note'         => 'Enter value as whole percentage (e.g. enter "12.5" for 12.5% off)',
                    'user_defined' => false,
                    'default'      => 0,
                    'global'       => 1,
                    'required'     => false,
                    'visible'      => true,
                    'system'       => false,
                    'sort_order'   => 150,
                    'position'     => 105,
                ]
            );

            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'club_discount')
                ->addData(
                    [
                        'attribute_set_id'   => $attributeSetId,
                        'attribute_group_id' => $attributeGroupId,
                        'used_in_forms'      => ['adminhtml_customer'],
                    ]
                );

            $attribute->save();

            //needs_mach_update
            $customerSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'needs_mach_update',
                [
                    'type'         => 'int',
                    'label'        => 'Needs Mach Update',
                    'input'        => 'boolean',
                    'source'       => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'user_defined' => false,
                    'default'      => 0,
                    'required'     => false,
                    'visible'      => false,
                    'system'       => false,
                    'comment'      => 'Flag to indicate user needs to be updated. 0 = No, 1 = Yes.',
                ]
            );

            //needs_mach_export
            $customerSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'needs_mach_export',
                [
                    'type'         => 'int',
                    'label'        => 'Mach Export Scheduled',
                    'input'        => 'boolean',
                    'source'       => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'user_defined' => false,
                    'default'      => 0,
                    'required'     => false,
                    'visible'      => false,
                    'system'       => false,
                    'comment'      => 'Flag to indicate user needs to be exported. 0 = No, 1 = Yes.',
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