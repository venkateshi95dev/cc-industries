<?php

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;


class AddLeadDaysLastUpdateDateProductAttribute implements DataPatchInterface
{

    protected EavSetup $eavSetup;

    public function __construct(
        protected ModuleDataSetupInterface $setup,
        protected CatalogConfig $catalogConfig,
        protected EavConfig $eavConfig,
        protected AttributeManagementInterface $attributeManagement,
        protected ProductAttributeRepositoryInterface $productAttributeRepository,
        protected ProductRepositoryInterface $productRepository,
        protected SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        protected State $state,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->eavSetup = $eavSetupFactory->create([
            'setup' => $setup
        ]);
    }

    public function apply()
    {
        $this->eavSetup->addAttribute(
            Product::ENTITY,
            'cc_ecomleaddays_lastupdate_date',
            [
                'label'         => "Ecomm Lead Days Last Update Date",
                'type'          => 'varchar',
                'input'         => 'text',
                'global'        => ScopedAttributeInterface::SCOPE_GLOBAL,
                'group'         => 'CorvetteCentral',
                'required'      => false,
                'sort_order'    => 999,
                'user_defined'  => true,
                'system'        => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front'         => true,
                'used_in_product_listing'  => true,
            ]
        );
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
