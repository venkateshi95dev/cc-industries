<?php
declare(strict_types=1);

namespace Crimson\Catalog\Setup\Patch\Data;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Psr\Log\LoggerInterface;
use Exception;

class PopulateComparePosition implements DataPatchInterface
{
    private const ATTRIBUTES = [
        'brand' => 2,
        'tire_series' => 3,
        'tire_build' => 4,
        'sidewall_style' => 5,
        'whitewall_width' => 6,
        'tire_size' => 7,
        'overall_diameter_text' => 20,
        'tire_rim_diameter' => 21,
        'section_width_actual_text' => 22,
        'tread_width_text' => 23,
        'rec_rim_width' => 24,
        'shop_aspect_ratio_radial' => 25,
        'shop_width_bias_look' => 26,
        'size_section_width_radial' => 27,
        'service_description' => 40,
        'load_index' => 41,
        'speed_rating' => 42,
        'max_load_capacity_text' => 44,
        'utqg_new' => 45,
        'tubetype' => 46,
        'weight' => 80
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavConfig $eavConfig,
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @return void
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        foreach (self::ATTRIBUTES as $attributeCode => $comparePosition) {
            try {
                $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
                if (!$attribute || !$attribute->getId()) {
                    continue;
                }

                $attribute->setData('compare_position', $comparePosition);
                $this->attributeRepository->save($attribute);
            } catch (Exception $e) {
                $this->logger->error("Error adding compare_position value for the attribute " . $attributeCode);
                $this->logger->error($e->getMessage());
            }

        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
