<?php
/**
 * @namespace   Crimson
 * @module      AddMachCustomerNumberAttribute
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/28/2019 3:04 PM
 * @brief
 */

namespace Crimson\MachCustomer\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class AddMachCustomerNumberAttribute implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * AddPaypalOrderStates constructor.
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Customer\Setup\CustomerSetupFactory      $customerSetupFactory
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup      = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create();
        $customerSetup->addAttribute(
            Customer::ENTITY, 'mach_customer_number',
            [
                'type'            => 'varchar',
                'label'           => 'MACH Customer Number',
                'input'           => 'text',
                'sort_order'      => 101,
                'validate_rules'  => '{"max_text_length":"11","input_validation":"numeric"}',
                'position'        => 101,
                'system'          => true,
                'is_user_defined' => true,
                'visible'         => true,
                'is_required'     => false,
                'required'        => false,

                'is_used_in_grid'       => true,
                'is_visible_in_grid'    => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true,
            ]
        );

        $setId   = $customerSetup->getDefaultAttributeSetId(Customer::ENTITY);
        $groupId = $customerSetup->getDefaultAttributeGroupId(Customer::ENTITY, $setId);

        $machCustomerNumberAttribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY, 'mach_customer_number'
        )
            ->addData(
                [
                    'attribute_set_id'   => $setId,
                    'attribute_group_id' => $groupId,
                    'used_in_forms'      => [
                        'adminhtml_customer',
                    ],
                ]
            );

        $machCustomerNumberAttribute->save();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.3.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
