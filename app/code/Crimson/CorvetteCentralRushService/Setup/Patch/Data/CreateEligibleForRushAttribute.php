<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentralRushService\Setup\Patch\Data;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean as BooleanSource;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class CreateEligibleForRushAttribute implements DataPatchInterface
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

        $attributeCode = "cc_eligible_for_rush";

        $attributeConfig = [
            'label' => 'Eligible for Rush',
            'type' => 'int',
            'input' => 'boolean',
            'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'source'           => BooleanSource::class,
            'group' => 'CorvetteCentral',
            'required' => false,
            'sort_order' => 120,
            'user_defined' => true,
            'default'          => '0',
            'system' => false,
            'searchable' => true,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => true,
            'used_in_product_listing' => false
        ];

        try {
            $eavSetup->addAttribute(Product::ENTITY, $attributeCode, $attributeConfig);
        } catch (Exception $e) {
            $this->logger->error("Wasn't able to create the attribute $attributeCode");
            $this->logger->error($e->getMessage());
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array|string[]
     */
    public function getAliases() : array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies() : array
    {
        return [];
    }
}
