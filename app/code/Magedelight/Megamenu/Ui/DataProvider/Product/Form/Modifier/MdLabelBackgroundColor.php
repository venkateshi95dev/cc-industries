<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;

class MdLabelBackgroundColor extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * MdLabelBackgroundColor constructor.
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * Modify meta
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->customizeColorPickerAttribute($meta);
        return $meta;
    }

    /**
     * Customize Color Picker Attribute
     *
     * @param array $meta
     * @return array
     */
    protected function customizeColorPickerAttribute(array $meta)
    {
        $attributeCode = 'md_label_background_color';
        $path = $this->arrayManager->findPath($attributeCode, $meta, null, 'children');
        if ($path) {
            $meta = $this->arrayManager->merge(
                $path,
                $meta,
                [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => \Magento\Ui\Component\Form\Field::NAME,
                                'component' => 'Magento_Ui/js/form/element/color-picker',
                                'dataType' => 'text',
                                'elementTmpl' => 'ui/form/element/color-picker',
                                'label' => __('Background Color (hex)'),
                                'dataScope' => $attributeCode,
                                'formElement' => 'colorPicker',
                                'colorFormat' => 'hax',
                                'colorPickerMode' => 'full'
                            ],
                        ],
                    ],
                ]
            );
        }
        return $meta;
    }

    /**
     * Modify Data
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
