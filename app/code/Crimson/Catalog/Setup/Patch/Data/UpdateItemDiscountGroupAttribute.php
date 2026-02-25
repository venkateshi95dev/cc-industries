<?php
declare(strict_types=1);

namespace Crimson\Catalog\Setup\Patch\Data;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateItemDiscountGroupAttribute implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavConfig $eavConfig,
        private readonly AttributeRepositoryInterface $attributeRepository
    ) {
    }

    /**
     * @return void
     * @throws StateException|LocalizedException
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'item_discount_group');
        $attribute->setData('is_visible', 1);
        $this->attributeRepository->save($attribute);
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
