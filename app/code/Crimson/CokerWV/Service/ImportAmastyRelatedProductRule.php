<?php

namespace Crimson\CokerWV\Service;

use Amasty\Mostviewed\Model\Group;
use Amasty\Mostviewed\Model\GroupFactory;
use Amasty\Mostviewed\Model\Repository\GroupRepository;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class ImportAmastyRelatedProductRule
{

    const BASE_RULE_ARRAY = array (
        'group_id' => '',
        'name' => '',
        'priority' => '6',
        'block_title' => 'Bias Look Radial Tires That Fit This Size ',
        'max_products' => '10',
        'status' => '1',
        'for_out_of_stock' => '0',
        'same_as' => '0',
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
                    ),
                '1--3' =>
                    array (
                        'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Product',
                        'attribute' => 'shop_width_bias_look',
                        'operator' => '==',
                        'value' => '',
                    ),
                '1--4' =>
                    array (
                        'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Product',
                        'attribute' => 'tire_rim_diameter',
                        'operator' => '==',
                        'value' => '',
                    ),
            ),
        'same_as_conditions' =>
            array (
                1 =>
                    array (
                        'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Combine',
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ),
            ),
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
                    ),
                '1--3' =>
                    array (
                        'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Product',
                        'attribute' => 'shop_width_bias_ply',
                        'operator' => '==',
                        'value' => '',
                    ),
                '1--4' =>
                    array (
                        'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Product',
                        'attribute' => 'tire_rim_diameter',
                        'operator' => '==',
                        'value' => '',
                    ),
            ),
        'category_ids' => '',
    );

    const RULES = [
        [
            'name' => 'Bias Ply > Bias Look | 600-16',
            'shop_width_bias_ply' => '6.00',
            'shop_width_bias_look' => '6.00',
            'tire_rim_diameter' => '16'
        ],
        [
            'name' => 'Bias Ply > Bias Look | L78-15',
            'shop_width_bias_ply' => 'L78',
            'shop_width_bias_look' => 'L',
            'tire_rim_diameter' => '15'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 820-15',
            'shop_width_bias_ply' => '820',
            'shop_width_bias_look' => '820',
            'tire_rim_diameter' => '15'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 800-15',
            'shop_width_bias_ply' => '800',
            'shop_width_bias_look' => '800',
            'tire_rim_diameter' => '15'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 800-14',
            'shop_width_bias_ply' => '800',
            'shop_width_bias_look' => '800',
            'tire_rim_diameter' => '14'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 760-15',
            'shop_width_bias_ply' => '760',
            'shop_width_bias_look' => '760',
            'tire_rim_diameter' => '15'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 750-17',
            'shop_width_bias_ply' => '750',
            'shop_width_bias_look' => '750',
            'tire_rim_diameter' => '17'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 750-16',
            'shop_width_bias_ply' => '750',
            'shop_width_bias_look' => '750',
            'tire_rim_diameter' => '16'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 750-14',
            'shop_width_bias_ply' => '750',
            'shop_width_bias_look' => '750',
            'tire_rim_diameter' => '14'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 725-13',
            'shop_width_bias_ply' => '725',
            'shop_width_bias_look' => '725',
            'tire_rim_diameter' => '13'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 710-15',
            'shop_width_bias_ply' => '710',
            'shop_width_bias_look' => '710',
            'tire_rim_diameter' => '15'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 700-19',
            'shop_width_bias_ply' => '700',
            'shop_width_bias_look' => '700',
            'tire_rim_diameter' => '19'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 700-18',
            'shop_width_bias_ply' => '700',
            'shop_width_bias_look' => '700',
            'tire_rim_diameter' => '18'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 700-16',
            'shop_width_bias_ply' => '700',
            'shop_width_bias_look' => '700',
            'tire_rim_diameter' => '16'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 670-16',
            'shop_width_bias_ply' => '670',
            'shop_width_bias_look' => '670',
            'tire_rim_diameter' => '16'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 670-15',
            'shop_width_bias_ply' => '670',
            'shop_width_bias_look' => '670',
            'tire_rim_diameter' => '15'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 650-19',
            'shop_width_bias_ply' => '650',
            'shop_width_bias_look' => '650',
            'tire_rim_diameter' => '19'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 650-16',
            'shop_width_bias_ply' => '650',
            'shop_width_bias_look' => '650',
            'tire_rim_diameter' => '16'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 650-13',
            'shop_width_bias_ply' => '650',
            'shop_width_bias_look' => '650',
            'tire_rim_diameter' => '13'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 640-13',
            'shop_width_bias_ply' => '640',
            'shop_width_bias_look' => '640',
            'tire_rim_diameter' => '13'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 600-20',
            'shop_width_bias_ply' => '600',
            'shop_width_bias_look' => '600',
            'tire_rim_diameter' => '20'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 600-19',
            'shop_width_bias_ply' => '600',
            'shop_width_bias_look' => '600',
            'tire_rim_diameter' => '19'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 560-15',
            'shop_width_bias_ply' => '560',
            'shop_width_bias_look' => '560',
            'tire_rim_diameter' => '15'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 550-19',
            'shop_width_bias_ply' => '550',
            'shop_width_bias_look' => '550',
            'tire_rim_diameter' => '19'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 550-18',
            'shop_width_bias_ply' => '550',
            'shop_width_bias_look' => '550',
            'tire_rim_diameter' => '18'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 550-17',
            'shop_width_bias_ply' => '550',
            'shop_width_bias_look' => '550',
            'tire_rim_diameter' => '17'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 550-16',
            'shop_width_bias_ply' => '550',
            'shop_width_bias_look' => '550',
            'tire_rim_diameter' => '16'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 550/600-21',
            'shop_width_bias_ply' => '550/600',
            'shop_width_bias_look' => '550/600',
            'tire_rim_diameter' => '21'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 500-19',
            'shop_width_bias_ply' => '500',
            'shop_width_bias_look' => '500',
            'tire_rim_diameter' => '19'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 500-16',
            'shop_width_bias_ply' => '500',
            'shop_width_bias_look' => '500',
            'tire_rim_diameter' => '16'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 475/500-19',
            'shop_width_bias_ply' => '475/500',
            'shop_width_bias_look' => '475/500',
            'tire_rim_diameter' => '19'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 440/450-21',
            'shop_width_bias_ply' => '440/450',
            'shop_width_bias_look' => '440/450',
            'tire_rim_diameter' => '21'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 600/650-18',
            'shop_width_bias_ply' => '600/650',
            'shop_width_bias_look' => '600/650',
            'tire_rim_diameter' => '18'
        ],
        [
            'name' => 'Bias Ply > Bias Look | 600/650-17',
            'shop_width_bias_ply' => '600/650',
            'shop_width_bias_look' => '600/650',
            'tire_rim_diameter' => '17'
        ],
        [
            'name' => 'Bias Ply > Bias Look | G70-15',
            'shop_width_bias_ply' => 'G70',
            'shop_width_bias_look' => 'G',
            'tire_rim_diameter' => '15'
        ],
        [
            'name' => 'Bias Ply > Bias Look | G70-14',
            'shop_width_bias_ply' => 'G70',
            'shop_width_bias_look' => 'G',
            'tire_rim_diameter' => '14'
        ],
        [
            'name' => 'Bias Ply > Bias Look | F70-15',
            'shop_width_bias_ply' => 'F70',
            'shop_width_bias_look' => 'F',
            'tire_rim_diameter' => '15'
        ],
        [
            'name' => 'Bias Ply > Bias Look | F70-14',
            'shop_width_bias_ply' => 'F70',
            'shop_width_bias_look' => 'F',
            'tire_rim_diameter' => '14'
        ],
        [
            'name' => 'Bias Ply > Bias Look | F60-15',
            'shop_width_bias_ply' => 'F60',
            'shop_width_bias_look' => 'F',
            'tire_rim_diameter' => '15'
        ],
        [
            'name' => 'Bias Ply > Bias Look | E70-15',
            'shop_width_bias_ply' => 'E70',
            'shop_width_bias_look' => 'E',
            'tire_rim_diameter' => '15'
        ],
        [
            'name' => 'Bias Ply > Bias Look | E70-14',
            'shop_width_bias_ply' => 'E70',
            'shop_width_bias_look' => 'E',
            'tire_rim_diameter' => '14'
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
        $result['message'] = "Assigned related product rule data.";
        $count = 0;
        try {
            $cokerTireStoreId        = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_STORE_CODE)->getId();
            $cokerTireDefaultStoreId = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_DEFAULT_STORE_CODE)->getId();

            $attributeProductType = $this->attributeRepository->get('4', 'coker_product_type');
            $coker_product_type = $attributeProductType->getSource()->getOptionId('Tire');

            $attributeTireBuild = $this->attributeRepository->get('4', 'tire_build');
            $tire_build_bias_ply = $attributeTireBuild->getSource()->getOptionId('Bias Ply');
            $tire_build_bias_look = $attributeTireBuild->getSource()->getOptionId('Bias Look Radial');

            $attributeBiasPly = $this->attributeRepository->get('4', 'shop_width_bias_ply');
            $attributeTireRimDiameter = $this->attributeRepository->get('4', 'tire_rim_diameter');
            $attributeBiasLook = $this->attributeRepository->get('4', 'shop_width_bias_look');

            foreach (self::RULES as $ruleData){
                $newRule = self::BASE_RULE_ARRAY;
                $newRule ['stores'] = [$cokerTireStoreId,$cokerTireDefaultStoreId];
                $newRule ['name'] = $ruleData['name'];

                $shop_width_bias_ply = $attributeBiasPly->getSource()->getOptionId($ruleData['shop_width_bias_ply']);
                $tire_rim_diameter = $attributeTireRimDiameter->getSource()->getOptionId($ruleData['tire_rim_diameter']);
                $shop_width_bias_look = $attributeBiasLook->getSource()->getOptionId($ruleData['shop_width_bias_look']);

                $newRule ['conditions']['1--1']['value'] = $coker_product_type;
                $newRule ['conditions']['1--2']['value'] = $tire_build_bias_ply;
                $newRule ['conditions']['1--3']['value'] = $shop_width_bias_ply;
                $newRule ['conditions']['1--4']['value'] = $tire_rim_diameter;

                $newRule ['where_conditions']['1--1']['value'] = $coker_product_type;
                $newRule ['where_conditions']['1--2']['value'] = $tire_build_bias_look;
                $newRule ['where_conditions']['1--3']['value'] = $shop_width_bias_look;
                $newRule ['where_conditions']['1--4']['value'] = $tire_rim_diameter;

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
