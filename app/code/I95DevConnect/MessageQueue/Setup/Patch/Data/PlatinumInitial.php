<?php

/**
 * @author Subhan
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PlatinumInitial implements DataPatchInterface
{
    public const LABEL = 'label';
    public const INPUT = 'input';
    public const REQUIRED = 'required';
    public const SORT_ORDER = 'sort_order';
    public const VISIBLE = 'visible';
    public const SYSTEM = 'system';
    public const IS_USED_IN_GRID = 'is_used_in_grid';
    public const IS_VISIBLE_IN_GRID = 'is_visible_in_grid';
    public const IS_FILTERABLE_IN_GRID = 'is_filterable_in_grid';
    public const IS_SEARCHABLE_IN_GRID = 'is_searchable_in_grid';
    public const UPDATE_BY = 'update_by';

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * @var EavSetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * PlatinumInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param EavSetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        EavSetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $targetCustomerExists = $customerSetup->getAttribute(Customer::ENTITY, 'target_customer_id');
        if (!$targetCustomerExists) {
            $customerSetup->addAttribute(
                Customer::ENTITY,
                'target_customer_id',
                [
                    self::LABEL => 'ERP Customer ID',
                    self::INPUT => 'text',
                    'type' => 'text',
                    self::REQUIRED => false,
                    self::SORT_ORDER => 30,
                    self::VISIBLE => false,
                    self::SYSTEM => false,
                    self::IS_USED_IN_GRID => true,
                    self::IS_VISIBLE_IN_GRID => true,
                    self::IS_FILTERABLE_IN_GRID => true,
                    self::IS_SEARCHABLE_IN_GRID => true
                ]
            );
        }

        $originExists = $customerSetup->getAttribute(Customer::ENTITY, 'origin');
        if (!$originExists) {
            $customerSetup->addAttribute(
                Customer::ENTITY,
                'origin',
                [
                    self::LABEL => 'Origin',
                    self::INPUT => 'text',
                    'type' => 'text',
                    self::REQUIRED => false,
                    self::SORT_ORDER => 40,
                    self::VISIBLE => false,
                    self::SYSTEM => false,
                    self::IS_USED_IN_GRID => true,
                    self::IS_VISIBLE_IN_GRID => true,
                    self::IS_FILTERABLE_IN_GRID => true,
                    self::IS_SEARCHABLE_IN_GRID => true
                ]
            );
        }

        $updateByStatus = $customerSetup->getAttribute(Customer::ENTITY, self::UPDATE_BY);
        if (!$updateByStatus) {
            $customerSetup->addAttribute(
                Customer::ENTITY,
                self::UPDATE_BY,
                [
                    self::LABEL => self::UPDATE_BY,
                    self::INPUT => 'text',
                    self::REQUIRED => false,
                    self::SORT_ORDER => 40,
                    self::VISIBLE => false,
                    self::SYSTEM => false
                ]
            );
        }

        $addressTargetId = $customerSetup->getAttribute('customer_address', 'target_address_id');
        if (!$addressTargetId) {
            $customerSetup->addAttribute(
                'customer_address',
                'target_address_id',
                [
                    self::LABEL => 'ERP Address ID',
                    self::INPUT => 'text',
                    self::REQUIRED => false,
                    self::SORT_ORDER => 40,
                    self::VISIBLE => false,
                    self::SYSTEM => false,
                    'frontend_input' => 'hidden',
                ]
            );
        }

        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);

        $StatusExists = $categorySetup->getAttribute(Product::ENTITY, 'targetproductstatus');
        if (!$StatusExists) {
            $categorySetup->addAttribute(
                Product::ENTITY,
                'targetproductstatus',
                [
                    self::LABEL => __('Target Product Status'),
                    self::INPUT => 'text',
                    self::REQUIRED => false,
                    self::SORT_ORDER => 40,
                    self::VISIBLE => false,
                    self::SYSTEM => false,
                    self::IS_USED_IN_GRID => true,
                    self::IS_VISIBLE_IN_GRID => true,
                    self::IS_FILTERABLE_IN_GRID => true,
                    self::IS_SEARCHABLE_IN_GRID => true
                ]
            );
        }

        $updatedByExists = $categorySetup->getAttribute(Product::ENTITY, self::UPDATE_BY);
        if (!$updatedByExists) {
            $categorySetup->addAttribute(
                Product::ENTITY,
                self::UPDATE_BY,
                [
                    self::LABEL => self::UPDATE_BY,
                    self::INPUT => 'text',
                    self::REQUIRED => false,
                    self::SORT_ORDER => 40,
                    self::VISIBLE => false,
                    self::SYSTEM => false
                ]
            );
        }
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
    public static function getDependencies() // NOSONAR
    {
        return [

        ];
    }

    /**
     * Get patch version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '2.1.9';
    }
}
