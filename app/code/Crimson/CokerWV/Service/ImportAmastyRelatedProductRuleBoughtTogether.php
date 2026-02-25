<?php

namespace Crimson\CokerWV\Service;

use Amasty\Mostviewed\Model\GroupFactory;
use Amasty\Mostviewed\Model\Repository\GroupRepository;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class ImportAmastyRelatedProductRuleBoughtTogether
{
    public function __construct(
        private readonly StoreRepositoryInterface $storeRepositoryInterface,
        private readonly GroupRepository $groupRepository,
        private readonly GroupFactory $groupFactory
    )
    {
    }

    public function execute(): array
    {
        $result['message'] = "Assigned related product rule data (Customers Also Bought).";
        $count = 0;
        try {
            $cokerTireStoreId        = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_STORE_CODE)->getId();
            $cokerTireDefaultStoreId = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_DEFAULT_STORE_CODE)->getId();

            $newRules = [
                array (
                    'name' => 'Customer Also Bought',
                    'priority' => '10',
                    'block_title' => 'Customers Also Bought',
                    'max_products' => '5',
                    'block_position' => 'cart_before_crosssel',
                    'replace_type' => '0',
                    'source_type' => '1',
                    'block_layout' => '0',
                    'sorting' => 'random',
                    'stores' => [$cokerTireStoreId,$cokerTireDefaultStoreId],
                    'customer_group_ids' => [0, 1, 2, 3, 21],
                    'status' => '1',
                    'for_out_of_stock' => '0',
                    'same_as' => '0',
                    'current_category' => '0',
                    'add_to_cart' => '1',
                    'display_wishlist_button' => '1',
                    'display_compare_button' => '1',
                    'show_out_of_stock' => '0',
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
                        ),
                    'category_ids' => '',
                ),
                array (
                    'name' => 'Customer Also Bought',
                    'priority' => '10',
                    'block_title' => 'Customers Also Bought',
                    'max_products' => '5',
                    'block_position' => 'product_content_bottom',
                    'replace_type' => '0',
                    'source_type' => '1',
                    'block_layout' => '0',
                    'sorting' => 'random',
                    'stores' => [$cokerTireStoreId,$cokerTireDefaultStoreId],
                    'customer_group_ids' => [0, 1, 2, 3, 21],
                    'status' => '1',
                    'for_out_of_stock' => '0',
                    'same_as' => '0',
                    'current_category' => '0',
                    'add_to_cart' => '1',
                    'display_wishlist_button' => '1',
                    'display_compare_button' => '1',
                    'show_out_of_stock' => '0',
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
                        ),
                    'category_ids' => '',
                )
            ];

            foreach ($newRules as $newRule){
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
