<?php
declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Psr\Log\LoggerInterface;
use Exception;

class AddCcCorvetteYearsCustomerAttribute implements DataPatchInterface
{
    const ATTRIBUTE_CODE = 'cc_corvetteyears';

    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private CustomerSetupFactory $customerSetupFactory,
        private AttributeSetFactory $attributeSetFactory,
        private AttributeRepositoryInterface $attributeRepository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     */
    public function apply() : void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        try {
            /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

            $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();
            $attributeSet     = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

            $customerSetup->addAttribute(Customer::ENTITY, self::ATTRIBUTE_CODE, [
                'type' => 'varchar',
                'label' => 'Select years',
                'input' => 'multiselect',
                'source' => \Crimson\CorvetteCentral\Model\Customer\Attribute\Source\CorvetteYears::class,
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'system' => false,
                'position' => 150
            ]);

            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, self::ATTRIBUTE_CODE);
            $attribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => [
                    'customer_account_create',
                    'customer_account_edit',
                    'adminhtml_customer'
                ]
            ]);

            $attribute->save();
        } catch (Exception $e) {
            $this->logger->error("Wasn't able to create the attribute " . self::ATTRIBUTE_CODE);
            $this->logger->error($e->getMessage());
        }



        $this->moduleDataSetup->getConnection()->endSetup();
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
