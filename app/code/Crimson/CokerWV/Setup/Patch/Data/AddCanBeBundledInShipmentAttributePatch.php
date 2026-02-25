<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Validator\ValidateException;

class AddCanBeBundledInShipmentAttributePatch implements DataPatchInterface
{
    public const ATTRIBUTE_CODE = 'can_be_bundled_in_shipment';

    public function __construct(
        protected readonly ModuleDataSetupInterface $moduleDataSetup,
        protected readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    /**
     * @throws LocalizedException
     * @throws ValidateException
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            self::ATTRIBUTE_CODE,
            [
                'type' => 'int',
                'label' => 'Can Be Bundled In Shipment?',
                'input' => 'boolean',
                'source' => Boolean::class,
                'sort_order' => 100,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => 0,
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
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
