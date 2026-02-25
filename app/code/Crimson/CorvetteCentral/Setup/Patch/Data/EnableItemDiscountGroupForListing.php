<?php
declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class EnableItemDiscountGroupForListing implements DataPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory
    ) {}

    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        $eavSetup = $this->eavSetupFactory->create([
            'setup' => $this->moduleDataSetup
        ]);

        $attributeCode = 'item_discount_group';

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeCode,
            'used_in_product_listing',
            1
        );
        $this->moduleDataSetup->endSetup();

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
