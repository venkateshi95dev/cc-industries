<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentralOSCO\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class CreateShippingBoxFunctionalityProductAttrs implements DataPatchInterface
{

    public function __construct(
        protected ModuleDataSetupInterface $moduleDataSetup,
        protected EavSetupFactory $eavSetupFactory,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     */
    public function apply() : void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributesData = [
            'cc_packageexists' => [
                'label' => "Package Exists",
                'type' => 'int',
                'input' => 'boolean',
                'source' => Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'required' => false,
                'user_defined' => true,
                'default' => 0,
                'sort_order' => 121,
                'system' => false,
                'group' => 'CorvetteCentral',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
            ],
            'cc_length' => [
                'label' => "Shipping Length",
                'type' => 'decimal',
                'input' => 'text',
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'required' => false,
                'user_defined' => true,
                'default' => null,
                'system' => false,
                'sort_order' => 120,
                'group' => 'CorvetteCentral',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
            ],
            'cc_width' => [
                'label' => "Shipping Width",
                'type' => 'decimal',
                'input' => 'text',
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'required' => false,
                'user_defined' => true,
                'default' => null,
                'system' => false,
                'sort_order' => 120,
                'group' => 'CorvetteCentral',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
            ],
            'cc_height' => [
                'label' => "Shipping Height",
                'type' => 'decimal',
                'input' => 'text',
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'required' => false,
                'user_defined' => true,
                'default' => null,
                'system' => false,
                'sort_order' => 120,
                'group' => 'CorvetteCentral',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
            ],
            'cc_packages' => [
                'label' => "Packages",
                'type' => 'text',
                'input' => 'text',
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'required' => false,
                'user_defined' => true,
                'default' => null,
                'system' => false,
                'sort_order' => 122,
                'group' => 'CorvetteCentral',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
            ],
        ];

        try {
            foreach ($attributesData as $code => $attributeData) {
                $eavSetup->addAttribute(Product::ENTITY, $code, $attributeData);
            }
        } catch (\Exception $e) {
            $this->logger->error("Wasn't able to create the attribute");
            $this->logger->error($e->getMessage());
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function getAliases() : array
    {
        return [];
    }

    public static function getDependencies() : array
    {
        return [];
    }
}
