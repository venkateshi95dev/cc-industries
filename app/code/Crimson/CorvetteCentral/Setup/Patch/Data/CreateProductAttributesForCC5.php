<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use Exception;

class CreateProductAttributesForCC5 implements DataPatchInterface
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

        $this->createSearchDescriptionAttribute($eavSetup);
        $this->createParagonItemNoAttribute($eavSetup);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @param EavSetup $eavSetup
     * @return void
     */
    public function createSearchDescriptionAttribute(EavSetup $eavSetup) : void
    {
        $attributeCode = "cc_searchdescription";

        $attributeConfig = [
            'label' => 'Search Description',
            'type' => 'varchar',
            'input' => 'text',
            'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'group' => 'CorvetteCentral',
            'required' => false,
            'sort_order' => 100,
            'user_defined' => true,
            'system' => false,
            'searchable' => true,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => true,
            'used_in_product_listing' => false,
            'frontend_class' => 'validate-length maximum-length-50'
        ];

        try {
            $this->createAttribute($eavSetup, $attributeCode, $attributeConfig);
        } catch (Exception $e) {
            $this->logger->error("Wasn't able to create the attribute $attributeCode");
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param EavSetup $eavSetup
     * @return void
     */
    public function createParagonItemNoAttribute(EavSetup $eavSetup) : void
    {
        $attributeCode = "cc_paragonitemno";

        $attributeConfig = [
            'label' => 'Paragon Item No',
            'type' => 'varchar',
            'input' => 'text',
            'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'group' => 'CorvetteCentral',
            'required' => false,
            'sort_order' => 101,
            'user_defined' => true,
            'system' => false,
            'searchable' => true,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => true,
            'used_in_product_listing' => false,
            'frontend_class' => 'validate-length maximum-length-50'
        ];

        try {
            $this->createAttribute($eavSetup, $attributeCode, $attributeConfig);
        } catch (Exception $e) {
            $this->logger->error("Wasn't able to create the attribute $attributeCode");
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param EavSetup $eavSetup
     * @param string $code
     * @param array $config
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Validator\ValidateException
     */
    protected function createAttribute(EavSetup $eavSetup, string $code, array $config) : void
    {
        $eavSetup->addAttribute(Product::ENTITY, $code, $config);
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
