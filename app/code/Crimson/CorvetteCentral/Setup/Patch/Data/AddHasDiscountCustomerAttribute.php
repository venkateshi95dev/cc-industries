<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;

class AddHasDiscountCustomerAttribute implements DataPatchInterface
{

    public function __construct(

        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory                           $eavSetupFactory,
        private readonly Config                                    $eavConfig,
        private readonly CustomerSetupFactory                      $customerSetupFactory,
        private readonly AttributeSetFactory                       $attributeSetFactory
    ) {}

    public function apply(): void
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
            'cc_hasdiscount',
            [
                'label' => 'Corvette Central - Has Discount',
                'type' => 'int',
                'input' => 'boolean',
                'source' => Boolean::class,
                'position' => 700,
                'sort_order' => 700,
                'visible' => false,
                'required' => false,
                'default' => '0',
                'user_defined' => true,
                'system' => false
            ]
        );

        $attribute = $customerSetup
            ->getEavConfig()
            ->getAttribute(Customer::ENTITY, 'cc_hasdiscount')
            ->addData(
                [
                    'attribute_set_id'   => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms'      => ['adminhtml_customer']
                ]
            );

        $attribute->save();
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}

