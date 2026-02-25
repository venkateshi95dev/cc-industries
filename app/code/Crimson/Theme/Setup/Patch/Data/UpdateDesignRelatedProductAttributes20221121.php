<?php

namespace Crimson\Theme\Setup\Patch\Data;

use Crimson\CatalogCriticalCss\Model\Config as CatalogCriticalCssConfig;
use Crimson\CatalogCriticalCss\Model\OptionSource\AttributeDesignRelation;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateDesignRelatedProductAttributes20221121 implements DataPatchInterface
{
    /** @var CatalogCriticalCssConfig */
    private $catalogCriticalCssConfig;

    /** @var WriterInterface */
    private $configWriter;

    public function __construct(
        CatalogCriticalCssConfig $catalogCriticalCssConfig,
        WriterInterface $configWriter
    ) {
        $this->catalogCriticalCssConfig = $catalogCriticalCssConfig;
        $this->configWriter = $configWriter;
    }

    public function apply() 
    { 
        $productAttributeDesignRelations = $this->catalogCriticalCssConfig->getProductAttributeDesignRelations();
        $productAttributeDesignRelations['freeshipping'] = AttributeDesignRelation::OPTION_BINARY;
        $productAttributeDesignRelations['promocopy'] = AttributeDesignRelation::OPTION_BINARY;
        $productAttributeDesignRelations = json_encode($productAttributeDesignRelations, JSON_THROW_ON_ERROR);

        $this->configWriter->save('system/critical_css/generator/catalog_product/design_related_attribute_codes', $productAttributeDesignRelations);
    }

    public function getAliases() 
    { 
        return [];
    }

    public static function getDependencies() 
    { 
        return [];
    }
}