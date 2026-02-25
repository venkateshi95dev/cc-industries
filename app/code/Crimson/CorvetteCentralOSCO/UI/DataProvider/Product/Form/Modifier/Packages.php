<?php

namespace Crimson\CorvetteCentralOSCO\UI\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollection;
use Magento\Framework\Stdlib\ArrayManager;

class Packages extends AbstractModifier
{

    public const PRODUCT_ATTRIBUTE_CODE = 'cc_packages';
    public const FIELD_IS_DELETE = 'is_delete';
    public const FIELD_SORT_ORDER_NAME = 'sort_order';

    public function __construct(
        protected LocatorInterface $locator,
        protected AttributeSetCollection $attributeSetCollection,
        protected SerializerInterface $serializer,
        protected ArrayManager $arrayManager,
    ) {}

    public function modifyData(array $data)
    {
        $fieldCode = self::PRODUCT_ATTRIBUTE_CODE;
        $model = $this->locator->getProduct();
        $modelId = $model->getId();
        $packagesData = $model->getData(self::PRODUCT_ATTRIBUTE_CODE);
        if ($packagesData) {
            $packagesData = $this->serializer->unserialize($packagesData);
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/' . $fieldCode;
            $data = $this->arrayManager->set($path, $data, $packagesData);
        }

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $highlightsPath = $this->arrayManager->findPath(
            self::PRODUCT_ATTRIBUTE_CODE,
            $meta,
            null,
            'children'
        );

        if ($highlightsPath) {
            $meta = $this->arrayManager->merge(
                $highlightsPath,
                $meta,
                $this->initHighlightFieldStructure($meta, $highlightsPath)
            );
            $meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($highlightsPath, 0, -3)
                . '/' . self::PRODUCT_ATTRIBUTE_CODE,
                $meta,
                $this->arrayManager->get($highlightsPath, $meta)
            );
            $meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($highlightsPath, 0, -2),
                $meta
            );
        }

        return $meta;
    }

    /**
     * Get attraction highlights dynamic rows structure
     *
     * @param array $meta
     * @param string $highlightsPath
     * @return array
     */
    protected function initHighlightFieldStructure($meta, $highlightsPath)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Packages'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' =>
                            $this->arrayManager->get($highlightsPath . '/arguments/data/config/sortOrder', $meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'length' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Box Length (in)'),
                                        'dataScope' => 'length',
                                        'require' => '1',
                                        'validation' => [
                                            'required-entry' => true,
                                            'validate-number' => true,
                                            'validate-zero-or-greater' => true
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'width' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Box Width (in)'),
                                        'dataScope' => 'width',
                                        'require' => '1',
                                        'validation' => [
                                            'required-entry' => true,
                                            'validate-number' => true,
                                            'validate-zero-or-greater' => true
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'height' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Box Height (in)'),
                                        'dataScope' => 'height',
                                        'require' => '1',
                                        'validation' => [
                                            'required-entry' => true,
                                            'validate-number' => true,
                                            'validate-zero-or-greater' => true
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'weight' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Box Weight (lbs)'),
                                        'dataScope' => 'weight',
                                        'require' => '1',
                                        'validation' => [
                                            'required-entry' => true,
                                            'validate-number' => true,
                                            'validate-zero-or-greater' => true
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
