<?php

namespace Crimson\Attributes\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;

class RemoveAffirmAttributes implements DataPatchInterface
{

    const AFFIRM_ATTRIBUTES_CUSTOMER = [
        'affirm_customer_mfp'
    ];

    const AFFIRM_ATTRIBUTES_PRODUCT = [
        'affirm_product_mfp',
        'affirm_product_mfp',
        'affirm_product_mfp_priority',
        'affirm_product_mfp_start_date',
        'affirm_product_mfp_type',
        'affirm_product_promo_id',
        'affirm_product_mfp_end_date'
    ];

    const AFFIRM_ATTRIBUTES_CATEGORY = [
        'affirm_category_mfp',
        'affirm_category_mfp_end_date',
        'affirm_category_mfp_priority',
        'affirm_category_mfp_start_date',
        'affirm_category_mfp_type',
        'affirm_category_promo_id'
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory          $eavSetupFactory
    ) {}

    public function apply(): void
    {
        try {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            foreach (self::AFFIRM_ATTRIBUTES_CUSTOMER as $customerAttributeCode) {
                $eavSetup->removeAttribute(Customer::ENTITY, $customerAttributeCode);
            }

            foreach (self::AFFIRM_ATTRIBUTES_PRODUCT as $productAttributeCode) {
                $eavSetup->removeAttribute(Product::ENTITY, $productAttributeCode);
            }

            foreach (self::AFFIRM_ATTRIBUTES_CATEGORY as $categoryAttributeCode) {
                $eavSetup->removeAttribute(Category::ENTITY, $categoryAttributeCode);
            }
        } catch (\Exception $e) {

        }
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }
}
