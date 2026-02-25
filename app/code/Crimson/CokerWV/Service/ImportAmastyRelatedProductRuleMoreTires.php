<?php

namespace Crimson\CokerWV\Service;

use Amasty\Mostviewed\Model\Group;
use Amasty\Mostviewed\Model\GroupFactory;
use Amasty\Mostviewed\Model\Repository\GroupRepository;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class ImportAmastyRelatedProductRuleMoreTires
{
    const BASE_RULE_ARRAY = array (
        'group_id' => '',
        'name' => '',
        'priority' => '8',
        'block_title' => 'More Tires This Size',
        'max_products' => '10',
        'status' => '1',
        'for_out_of_stock' => '0',
        'same_as' => '1',
        'current_category' => '0',
        'add_to_cart' => '1',
        'display_wishlist_button' => '1',
        'display_compare_button' => '1',
        'show_out_of_stock' => '1',
        'block_position' => 'product_content_bottom',
        'replace_type' => '0',
        'source_type' => '0',
        'block_layout' => '0',
        'sorting' => 'random',
        'stores' => [],
        'customer_group_ids' => [0, 1, 2, 3, 21],
        'block_embedding' => '',
        'conditions' =>
            array (
                1 =>
                    array (
                        'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Combine',
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ),
                '1--1' =>
                    array (
                        'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Product',
                        'attribute' => 'coker_product_type',
                        'operator' => '==',
                        'value' => '',
                    ),
                '1--2' =>
                    array (
                        'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Product',
                        'attribute' => 'tire_build',
                        'operator' => '==',
                        'value' => '',
                    )
            ),
        'same_as_conditions' =>
            array (),
        'where_conditions' =>
            array (
                1 =>
                    array (
                        'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Combine',
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ),
                '1--1' =>
                    array (
                        'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Product',
                        'attribute' => 'coker_product_type',
                        'operator' => '==',
                        'value' => '',
                    ),
                '1--2' =>
                    array (
                        'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Product',
                        'attribute' => 'tire_build',
                        'operator' => '==',
                        'value' => '',
                    )
            ),
        'category_ids' => '',
    );

    const RULES = [
        [
            'name' => 'More Tires This Size (Radial)',
            'tire_build' => 'Radial',
            'same_as_conditions' =>
                array (
                    1 =>
                        array (
                            'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Combine',
                            'aggregator' => 'all',
                            'value' => '1',
                            'new_child' => '',
                        ),
                    '1--1' =>
                        array (
                            'type' => 'Amasty\\Mostviewed\\Model\\Rule\\Condition\\Product',
                            'attribute' => 'tire_build',
                            'operator' => '==',
                        ),
                    '1--2' =>
                        array (
                            'type' => 'Amasty\\Mostviewed\\Model\\Rule\\Condition\\Product',
                            'attribute' => 'size_section_width_radial',
                            'operator' => '==',
                        ),
                    '1--3' =>
                        array (
                            'type' => 'Amasty\\Mostviewed\\Model\\Rule\\Condition\\Product',
                            'attribute' => 'tire_rim_diameter',
                            'operator' => '==',
                        ),
                    '1--4' =>
                        array (
                            'type' => 'Amasty\\Mostviewed\\Model\\Rule\\Condition\\Product',
                            'attribute' => 'shop_aspect_ratio_radial',
                            'operator' => '==',
                        )
                )
        ],
        [
            'name' => 'More Tires This Size (Bias Look)',
            'tire_build' => 'Bias Look Radial',
            'same_as_conditions' =>
                array (
                    1 =>
                        array (
                            'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Combine',
                            'aggregator' => 'all',
                            'value' => '1',
                            'new_child' => '',
                        ),
                    '1--1' =>
                        array (
                            'type' => 'Amasty\\Mostviewed\\Model\\Rule\\Condition\\Product',
                            'attribute' => 'tire_build',
                            'operator' => '==',
                        ),
                    '1--2' =>
                        array (
                            'type' => 'Amasty\\Mostviewed\\Model\\Rule\\Condition\\Product',
                            'attribute' => 'tire_rim_diameter',
                            'operator' => '==',
                        ),
                    '1--3' =>
                        array (
                            'type' => 'Amasty\\Mostviewed\\Model\\Rule\\Condition\\Product',
                            'attribute' => 'shop_width_bias_look',
                            'operator' => '==',
                        )
                )
            ],
            [
                'name' => 'More Tires This Size (Bias Ply)',
                'tire_build' => 'Bias Ply',
                'same_as_conditions' =>
                    array (
                        1 =>
                            array (
                                'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Combine',
                                'aggregator' => 'all',
                                'value' => '1',
                                'new_child' => '',
                            ),
                        '1--1' =>
                            array (
                                'type' => 'Amasty\\Mostviewed\\Model\\Rule\\Condition\\Product',
                                'attribute' => 'tire_build',
                                'operator' => '==',
                            ),
                        '1--2' =>
                            array (
                                'type' => 'Amasty\\Mostviewed\\Model\\Rule\\Condition\\Product',
                                'attribute' => 'tire_rim_diameter',
                                'operator' => '==',
                            ),
                        '1--3' =>
                            array (
                                'type' => 'Amasty\\Mostviewed\\Model\\Rule\\Condition\\Product',
                                'attribute' => 'shop_width_bias_ply',
                                'operator' => '==',
                            )
                    )
            ]
        ];

    public function __construct(
        private readonly StoreRepositoryInterface $storeRepositoryInterface,
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly GroupRepository $groupRepository,
        private readonly GroupFactory $groupFactory
    )
    {
    }

    public function execute(): array
    {
        $result['message'] = "Assigned related product rule data (More Tires This Size).";
        $count = 0;
        try {
            $cokerTireStoreId        = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_STORE_CODE)->getId();
            $cokerTireDefaultStoreId = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_DEFAULT_STORE_CODE)->getId();

            $attributeProductType = $this->attributeRepository->get('4', 'coker_product_type');
            $coker_product_type = $attributeProductType->getSource()->getOptionId('Tire');
            $attributeTireBuild = $this->attributeRepository->get('4', 'tire_build');

            foreach (self::RULES as $ruleData){
                $newRule = self::BASE_RULE_ARRAY;
                $newRule ['stores'] = [$cokerTireStoreId,$cokerTireDefaultStoreId];
                $newRule ['name'] = $ruleData['name'];

                $tire_build = $attributeTireBuild->getSource()->getOptionId($ruleData['tire_build']);

                $newRule ['conditions']['1--1']['value'] = $coker_product_type;
                $newRule ['conditions']['1--2']['value'] = $tire_build;

                $newRule ['where_conditions']['1--1']['value'] = $coker_product_type;
                $newRule ['where_conditions']['1--2']['value'] = $tire_build;

                $newRule ['same_as_conditions'] = $ruleData['same_as_conditions'];

                $model = $this->groupFactory->create();
                $model->loadPost($newRule);
                $this->groupRepository->save($model);
                $count++;
            }
        } catch (\Exception $e) {
            $result['message'] = __('Can not insert data ' . $e->getMessage());
        }
        $result['message'] .= ' total : '.$count;
        return $result;
    }
}
