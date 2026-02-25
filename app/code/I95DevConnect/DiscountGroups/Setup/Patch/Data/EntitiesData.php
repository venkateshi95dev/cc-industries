<?php

/**
 * @author i95Dev
 * @copyright Copyright (c) 2023 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Setup\Patch\Data;

use I95DevConnect\MessageQueue\Api\LoggerInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class EntitiesData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Entities data constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param IndexerRegistry $indexerRegistry
     * @param AttributeSetFactory $attributeSetFactory
     * @param CategorySetupFactory $categorySetupFactory
     * @param State $appState
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        IndexerRegistry $indexerRegistry,
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory,
        State $appState,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->appState = $appState;
        $this->logger = $logger;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        /* Set the area code and catch exception if thrown */
        try {
            $this->appState->setAreaCode('global');
        } catch (LocalizedException $exception) {
            $this->logger->createLog(
                __METHOD__,
                $exception->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
        }

        $setup->getConnection()->insertOnDuplicate(
            $setup->getTable('i95dev_entity'),
            [
                [
                    'entity_name' => 'Customer Discount Group',
                    'entity_code' => 'customer_discount_group',
                    'sort_order' => 9, 'support_for_inbound' => true,
                    'support_for_outbound' => true
                ],
                [
                    'entity_name' => 'Item Discount Group',
                    'entity_code' => 'item_discount_group',
                    'sort_order' => 3,
                    'support_for_inbound' => true,
                    'support_for_outbound' => true
                ],
                [
                    'entity_name' => 'Discount Calculation',
                    'entity_code' => 'discount_calculation',
                    'sort_order' => 10,
                    'support_for_inbound' => true,
                    'support_for_outbound' => true
                ]
            ]
        );

        $this->createCustomerAttribute();

        $this->createProductAttribute();
    }

    /**
     * Create customer attribute
     */
    public function createCustomerAttribute()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'customer_discount_group',
            [
                'label' => 'Customer Discount Group',
                'input' => 'text',
                'required' => false,
                'sort_order' => 51,
                'visible' => false,
                'system' => false
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'customer_discount_group');
        $attribute->setData('used_in_forms', ['adminhtml_customer']);
        $attribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
        ]);
        $attribute->save();
    }

    /**
     * Create item attribute
     */
    public function createProductAttribute()
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $categorySetup->addAttribute(
            Product::ENTITY,
            'item_discount_group',
            [
                'label' => __('Item Discount Group'),
                'input' => 'text',
                'required' => false,
                'sort_order' => 40,
                'visible' => false,
                'system' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true
            ]
        );
    }

    /**
     * Get Aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get Dependencies
     *
     * @return array
     */
    public static function getDependencies() //NOSONAR
    {
        return [];
    }

    /**
     * Get patch version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.2';
    }
}
