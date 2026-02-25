<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Peter Talavera
 * @email       ptalavera@crimsonagility.com
 * @date        2/26/2019 11:35 AM
 * @brief
 */

namespace Crimson\Attributes\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class AddProductUpdateAttribute implements
    DataPatchInterface,
    PatchVersionInterface
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

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $productEntityId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $categorySetup->removeAttribute(
            $productEntityId,
            'shipvia'
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'shipvia',
            [
                'label' => 'Ships Via',
                'input' => 'select',
                'type' => 'text',
                'visible' => true,
                'required' => false,
                'default' => 0,
                'group' => 'General',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'user_defined' => true,
                'option'  => ['values' => [
                    '501',
                    '502',
                    '503',
                    '504',
                    '505',
                    '506',
                    '507',
                    '508',
                    'FED01',
                    'GND',
                    'TRK'
                ]]
            ]
        );

        $categorySetup->removeAttribute(
            $productEntityId,
            'ships_from_manufacturer'
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'ships_from_manufacturer',
            [
                'label' => 'Dropship',
                'input' => 'select',
                'type' => 'text',
                'visible' => true,
                'required' => false,
                'default' => 0,
                'group' => 'General',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'user_defined' => true,
            ]
        );

        $categorySetup->removeAttribute(
            $productEntityId,
            'model_year'
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'model_year',
            [
                'label' => 'Model Year',
                'input' => 'multiselect',
                'type' => 'text',
                'visible' => true,
                'required' => false,
                'default' => 0,
                'group' => 'General',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined' => true,
                'option' => ['values' => [
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

        $categorySetup->removeAttribute(
            $productEntityId,
            'outboundtechlinks'
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'outboundtechlinks',
            [
                'label' => 'Outbound Tech Links',
                'input' => 'textarea',
                'type' => 'text',
                'visible' => true,
                'required' => false,
                'default' => 0,
                'group' => 'General',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'is_wysiwyg_enabled' => true,
                'used_in_product_listing' => true,
                'user_defined' => true,
            ]
        );

        $categorySetup->removeAttribute(
            $productEntityId,
            'installprice'
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'installprice',
            [
                'label' => 'Install Price',
                'input' => 'select',
                'type' => 'text',
                'visible' => true,
                'required' => false,
                'default' => 0,
                'group' => 'General',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'backend_model' => 'eav/entity_attribute_backend_array',
                'user_defined' => true,
                'option' => ['values' => [
                    '129',
                    '149',
                    '169',
                    '249',
                    '250',
                    '340',
                    '349',
                    '42.50',
                    '425',
                    '499',
                    '500',
                    '599',
                    '649',
                    '849',
                    '85',
                    '99',
                    'None'
                ]]
            ]
        );

        $categorySetup->removeAttribute(
            $productEntityId,
            'manufacturer_videos'
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'manufacturer_videos',
            [
                'label' => 'Manufacturer Videos',
                'input' => 'textarea',
                'type' => 'text',
                'visible' => true,
                'required' => false,
                'default' => 0,
                'group' => 'General',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'is_wysiwyg_enabled' => true,
                'used_in_product_listing' => true,
                'user_defined' => true,
            ]
        );

        $categorySetup->removeAttribute(
            $productEntityId,
            'installdifficulty'
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'installdificulty',
            [
                'label' => 'Install Difficulty',
                'input' => 'select',
                'type' => 'text',
                'visible' => true,
                'required' => false,
                'default' => 0,
                'group' => 'General',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'backend_model' => 'eav/entity_attribute_backend_array',
                'user_defined' => true,
                'option' => ['values' => [
                    '1',
                    '2',
                    '3',
                    '4',
                    '5'
                ]]
            ]
        );

    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.6';
    }
}