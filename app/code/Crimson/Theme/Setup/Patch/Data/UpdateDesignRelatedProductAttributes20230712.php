<?php

namespace Crimson\Theme\Setup\Patch\Data;

use Crimson\CatalogCriticalCss\Setup\Patch\Data\ConvertDesignRelatedAttributesConfigToCriticalCssSettings;
use Crimson\EavCriticalCss\Model\OptionSource\AttributeDesignRelation;
use Crimson\Theme\Setup\Patch\AbstractAttributeCriticalCssSettingsDataPatch;
use Magento\Catalog\Model\Product;

class UpdateDesignRelatedProductAttributes20230712 extends AbstractAttributeCriticalCssSettingsDataPatch
{
    public function apply()
    { 
        $this->updateCriticalCssSettings(
            Product::ENTITY, 
            'promocopydate', 
            AttributeDesignRelation::OPTION_UNRELATED
        );

        $this->updateCriticalCssSettings(
            Product::ENTITY,
            'pricefactor',
            AttributeDesignRelation::OPTION_SPECIFIC,
            '{"874":{"value":"874","group":"ND"},"875":{"value":"875","group":"NDR"}}'
        );

        $this->updateCriticalCssSettings(
            Product::ENTITY,
            'shippingcharge',
            AttributeDesignRelation::OPTION_SPECIFIC,
            '{"939":{"criticalValue":false,"groupCode":"","value":"939"},"940":{"criticalValue":"940","groupCode":"asdf","value":"940"},"941":{"criticalValue":"941","groupCode":"qwer","value":"941"},"942":{"value":"942","group":""},"943":{"value":"943","group":""},"944":{"value":"944","group":""},"945":{"value":"945","group":""},"946":{"value":"946","group":""},"947":{"value":"947","group":""},"948":{"value":"948","group":""},"949":{"value":"949","group":""},"950":{"value":"950","group":""},"951":{"value":"951","group":""},"952":{"value":"952","group":""},"953":{"value":"953","group":""},"954":{"value":"954","group":""},"955":{"value":"955","group":""},"956":{"value":"956","group":""},"957":{"value":"957","group":""},"958":{"value":"958","group":""},"959":{"value":"959","group":""},"960":{"value":"960","group":""},"961":{"value":"961","group":""},"962":{"value":"962","group":""},"963":{"value":"963","group":""},"1650":{"value":"1650","group":""},"1651":{"value":"1651","group":""},"1652":{"value":"1652","group":""},"1653":{"value":"1653","group":""},"1654":{"value":"1654","group":""},"1655":{"value":"1655","group":""},"1656":{"value":"1656","group":""},"1657":{"value":"1657","group":""},"1795":{"criticalValue":"1795","groupCode":"asdf","value":false},"1806":{"value":"1806","group":""},"1807":{"value":"1807","group":""},"1808":{"criticalValue":"1808","groupCode":"","value":"1808"},"1809":{"value":"1809","group":""},"1810":{"value":"1810","group":""},"7238":{"value":"7238","group":""},"7239":{"value":"7239","group":""},"7240":{"criticalValue":"7240","groupCode":"","value":"7240"},"7241":{"value":"7241","group":""},"7242":{"value":"7242","group":""},"7243":{"value":"7243","group":""},"7244":{"value":"7244","group":""},"7245":{"value":"7245","group":""},"7246":{"value":"7246","group":""},"7247":{"value":"7247","group":""},"7248":{"value":"7248","group":""},"7249":{"value":"7249","group":""},"7267":{"value":"7267","group":""},"7342":{"value":"7342","group":""},"7343":{"value":"7343","group":""},"7344":{"value":"7344","group":""},"7345":{"value":"7345","group":""},"7420":{"value":"7420","group":""},"7421":{"value":"7421","group":""},"7422":{"value":"7422","group":""},"7423":{"value":"7423","group":""},"7424":{"value":"7424","group":""},"7425":{"value":"7425","group":""},"7426":{"value":"7426","group":""},"7427":{"value":"7427","group":""},"7436":{"value":"7436","group":""},"7437":{"value":"7437","group":""},"7438":{"value":"7438","group":""},"7439":{"value":"7439","group":""},"7440":{"value":"7440","group":""},"7441":{"value":"7441","group":""},"7442":{"criticalValue":"7442","groupCode":"","value":"7442"},"7443":{"value":"7443","group":""},"7444":{"value":"7444","group":""},"7445":{"value":"7445","group":""},"7446":{"value":"7446","group":""},"7448":{"value":"7448","group":""},"7449":{"value":"7449","group":""},"7450":{"value":"7450","group":""},"7451":{"value":"7451","group":""},"7457":{"value":"7457","group":""}}'
        );
    }

    public static function getDependencies() 
    { 
        return [
            ConvertDesignRelatedAttributesConfigToCriticalCssSettings::class,
        ];
    }
}