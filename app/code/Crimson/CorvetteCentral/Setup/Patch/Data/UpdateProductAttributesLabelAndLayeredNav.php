<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use Exception;

class UpdateProductAttributesLabelAndLayeredNav implements DataPatchInterface
{
    const ATTRIBUTES = [
        "cc_body_style",
        "cc_code",
        "cc_color",
        "cc_cove",
        "cc_date",
        "cc_exterior",
        "cc_interior",
        "cc_logo",
        "cc_model",
        "cc_option",
        "cc_quarter",
        "cc_ratio",
        "cc_size",
        "cc_stinger",
        "cc_style",
        "cc_transmission",
        "cc_trim",
        "cc_year",
        "cc_base_unit_measure",
        "cc_hasdiscount"
    ];

    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory,
        private AttributeRepositoryInterface $attributeRepository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        foreach (self::ATTRIBUTES as $attributeCode) {
            try {
                $attribute = $eavSetup->getAttribute(Product::ENTITY, $attributeCode);

                if (!$attribute || !$attribute['attribute_id']) {
                    continue;
                }

                //Remove from layered navigation
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $attributeCode,
                    'is_filterable',
                    0
                );
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $attributeCode,
                    'is_filterable_in_search',
                    0
                );
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $attributeCode,
                    'used_in_layered_navigation',
                    0
                );

                $attribute = $this->attributeRepository->get(Product::ENTITY, $attributeCode);

                //Remove 'CorvetteCentral - ' from labels
                $frontendLabel = $attribute->getFrontendLabel() ?? [];

                if (str_starts_with($frontendLabel, 'CorvetteCentral - ')) {
                    $newLabel = ucfirst(trim(str_replace('CorvetteCentral - ', '', $frontendLabel)));
                    $attribute->setFrontendLabel($newLabel);
                }

                $this->attributeRepository->save($attribute);
            } catch (Exception $e) {
                $this->logger->error("Wasn't able to update the attribute $attributeCode");
                $this->logger->error($e->getMessage());
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies() : array
    {
        return [CreateProductAttributesForCC3::class, AddHasDiscountCustomerAttribute::class];
    }

    /**
     * @return array|string[]
     */
    public function getAliases() : array
    {
        return [];
    }
}
