<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use Exception;

class CreateProductAttributesForCC4 implements DataPatchInterface
{
    const ATTRIBUTES = [
        "cc_C1_53_62" => "C1: 53-62",
        "cc_C10" => "C10",
        "cc_C2_63_67" => "C2: 63-67",
        "cc_C3_68_82" => "C3: 68-82",
        "cc_C4_84_96" => "C4: 84-96",
        "cc_C5_97_04" => "C5: 97-04",
        "cc_C6_05_13" => "C6: 05-13",
        "cc_C7_14_" => "C7: 14-?",
        "cc_C8" => "C8",
        "cc_C9" => "C9",
    ];

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
        $this->addAttributes($eavSetup);
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @param EavSetup $eavSetup
     * @return void
     */
    public function addAttributes(EavSetup $eavSetup) : void
    {
        foreach (self::ATTRIBUTES as $attributeCode => $attributeLabel) {
            try {
                if ($eavSetup->getAttributeId(Product::ENTITY, $attributeCode)) {
                    continue; // Skip if already exists
                }
            } catch (Exception $e) {}

            try {
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    $attributeCode,
                    [
                        'label' => $attributeLabel,
                        'type' => 'int',
                        'input' => 'boolean',
                        'source' => Boolean::class,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'required' => false,
                        'user_defined' => true,
                        'default' => 0,
                        'system' => false,
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'used_in_product_listing' => true,
                    ]
                );
            } catch (Exception $e) {
                $this->logger->error("Wasn't able to create the attribute $attributeCode");
                $this->logger->error($e->getMessage());
            }
        }
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
