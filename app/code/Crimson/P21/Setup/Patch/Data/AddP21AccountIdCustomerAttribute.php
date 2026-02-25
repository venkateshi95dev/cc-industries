<?php
declare(strict_types=1);

namespace Crimson\P21\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use Exception;

class AddP21AccountIdCustomerAttribute implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CustomerSetupFactory $customerSetupFactory,
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly AttributeSetFactory $attributeSetFactory,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @return void
     */
    public function apply(): void
    {
        try {
            $this->moduleDataSetup->getConnection()->startSetup();

            $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $customerEntity = $customerSetup->getEavConfig()->getEntityType(
                'customer'
            );
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();

            $attributeSet     = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

            $customerSetup->addAttribute(
                Customer::ENTITY,
                'p21_account_id',
                [
                    'type'         => 'varchar',
                    'label'        => 'P21 Account ID',
                    'input'        => 'text',
                    'required'     => false,
                    'visible'      => true,
                    'user_defined' => true,
                    'position'     => 32,
                    'system'       => 0,
                    'is_used_in_grid'        => true,
                    'is_visible_in_grid'     => true,
                    'is_filterable_in_grid'  => true,
                    'is_searchable_in_grid'  => true,
                    'global'       => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );

            // Make it available in forms
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'p21_account_id');
            $attribute->addData(
                [
                    'attribute_set_id'   => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms'      => ['adminhtml_customer']
                ]
            );
            $attribute->save();

            $this->moduleDataSetup->getConnection()->endSetup();
        } catch (Exception $e) {
            $this->logger->error("Wasn't able to install the p21_account_id customer attribute patch");
            $this->logger->error($e->getMessage());
        }
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
