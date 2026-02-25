<?php
/**
 * @namespace   Crimson
 * @module      Attrbitues
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        03/07/2019
 */
namespace Crimson\Attributes\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class FixScopeAndFilterableToProductAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory     $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    public function apply()
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $productEntityId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $scopeStoreAttributes = [
            'carbcertified',
            'carbnumber',
            'installprice',
            'iskit',
            'manufacturer',
            'manufacturersku',
            'manufacturerskudisplay',
            'manufacturerskudisplay',
            'marketplaceitem',
            'outboundtechlinks',
            'percarusage',
            'productvideosgeneral',
            'promocopy',
            'prop65',
            'upc',
            'webupc',
            'wheelstyle',
            'willdropship'
        ];
        foreach ($scopeStoreAttributes as $attribute) {
            $categorySetup->updateAttribute(
                $productEntityId,
                $attribute,
                'is_global',
                \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
            );
        }

        $categorySetup->removeAttribute(
            $productEntityId,
            'model_year'
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'model_year',
            [
                'label'         => 'Model Year',
                'input'         => 'multiselect',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    '1953',
                    '1954',
                    '1955',
                    '1956',
                    '1957',
                    '1958',
                    '1959',
                    '1960',
                    '1961',
                    '1962',
                    '1963',
                    '1964',
                    '1965',
                    '1966',
                    '1967',
                    '1968',
                    '1969',
                    '1970',
                    '1971',
                    '1972',
                    '1973',
                    '1974',
                    '1975',
                    '1976',
                    '1977',
                    '1978',
                    '1979',
                    '1980',
                    '1981',
                    '1982',
                    '1984',
                    '1985',
                    '1986',
                    '1987',
                    '1988',
                    '1989',
                    '1990',
                    '1991',
                    '1992',
                    '1993',
                    '1994',
                    '1995',
                    '1996',
                    '1997',
                    '1998',
                    '1999',
                    '2000',
                    '2001',
                    '2002',
                    '2003',
                    '2004',
                    '2005',
                    '2006',
                    '2007',
                    '2008',
                    '2009',
                    '2010',
                    '2011',
                    '2012',
                    '2013',
                    '2014',
                    '2015',
                    '2016',
                    '2017',
                    '2018',
                    '2019',
                    'All'
                ]]
            ]
        );
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [
            \Crimson\Attributes\Setup\Patch\Data\AddProductAttributes::class,
            \Crimson\Attributes\Setup\Patch\Data\AddProductUpdateAttribute::class,
            \Crimson\Attributes\Setup\Patch\Data\FixToProductAttribute::class
        ];
    }
}