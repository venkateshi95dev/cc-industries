<?php

namespace Crimson\Theme\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class ConfigureCriticalCssUpdateForceIncludeSelectors implements DataPatchInterface
{
    private WriterInterface $configWriter;

    public function __construct(
        WriterInterface $configWriter
    ) {
        $this->configWriter = $configWriter;
    }

    public function apply()
    {
        $forceIncludeSelectors = <<<TEXT
.toolbar-products .pages
.section-item-content .menu-container .menu-mobile
.catalog-category-view .page-main .category-view>.category-image
.catalogsearch-result-index .page-main .category-view>.category-image
/^\.catalog-product-view \.product\.attribute\.manufacturer/
/^\.catalog-product-view \.product-info-main \.crosssell/
/^\.block\.crosssell/
/^\.fotorama__/
/^\.catalog-product-view \.product-info-main \.product-add-form \.stock\.dropship/
.fa
/^.fa-truck/
/\.product-items/
/\.product-item-info/
/^\.product-item-name/
.owl-carousel
TEXT;

        $this->configWriter->save('system/critical_css/force_include_selectors', $forceIncludeSelectors);
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
