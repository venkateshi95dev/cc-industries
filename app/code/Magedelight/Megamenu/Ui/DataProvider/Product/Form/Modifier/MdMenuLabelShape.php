<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;

class MdMenuLabelShape extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * MdMenuLabelShape constructor.
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * Modify Meta
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
        $attributeCode = 'md_menu_label_shape';
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
                                'dataType' => 'string',
                                'label' => __('Menu Label Shape'),
                                'dataScope' => $attributeCode,
                                'formElement' => 'radioset',
                                'additionalClasses' => 'md-label-shape'
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
