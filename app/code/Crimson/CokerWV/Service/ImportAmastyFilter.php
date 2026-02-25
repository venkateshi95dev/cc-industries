<?php

namespace Crimson\CokerWV\Service;

use Crimson\CokerWV\Setup\Patch\Data\CreateCokerCategory;
use Crimson\CokerWV\Setup\Patch\Data\CreateWVCategory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Amasty\ShopbyBase\Model\FilterSettingFactory;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting as FilterSettingResource;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Amasty\ShopbyBase\Setup\Patch\Data\FillAttributeIdFilters;

class ImportAmastyFilter
{

    const DATA_FILE_PATH = '/import/coker_zip_data_migration/coker_amasty_shopby_filters.json';
    const CATEGORIES_FILE_PATH = '/import/coker_zip_data_migration/coker_categories_map.json';

    public function __construct(
        private readonly DirectoryList             $directoryList,
        private readonly File                      $file,
        private readonly FilterSettingResource     $filterSettingResource,
        private readonly FilterSettingFactory      $filterSettingFactory,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly FillAttributeIdFilters $fillAttributeIdFilters
    )
    {
    }


    public function execute(): array
    {
        $result['message'] = "All Amasty Shopby Filters data imported.";
        $categoriesMap = $this->getCategoriesMap();

        try {
            $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . self::DATA_FILE_PATH;
            if (!$this->file->isExists($csvPath)) {
                throw new \Exception("Error with the JSON file.");
            }

            $rows = json_decode($this->file->fileGetContents($csvPath));
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        if (!empty($rows)) {
            $this->_cleanBeforeImport();
        }

        foreach ($rows as $row) {
            if (empty($row->filter_code) || empty($row->attribute_code)) {
                continue;
            }
            $newCategoriesFilter = null;
            if ($row->categories_filter) {
                $newCategoriesFilter = $this->mapCategoriesFilter($row->categories_filter, $categoriesMap);
            }
            $data = [
                'filter_code' => $row->filter_code,
                'attribute_code' => $row->attribute_code,
                'is_multiselect' => $row->is_multiselect,
                'display_mode' => $row->display_mode,
                'is_seo_significant' => $row->is_seo_significant,
                'slider_step' => $row->slider_step,
                'units_label_use_currency_symbol' => $row->units_label_use_currency_symbol,
                'units_label' => $row->units_label,
                'index_mode' => $row->index_mode,
                'follow_mode' => $row->follow_mode,
                'sort_options_by' => $row->sort_options_by,
                'show_product_quantities' => $row->show_product_quantities,
                'is_show_search_box' => $row->is_show_search_box,
                'number_unfolded_options' => $row->number_unfolded_options,
                'tooltip' => $row->tooltip,
                'is_expanded' => $row->is_expanded,
                'add_from_to_widget' => $row->add_from_to_widget,
                'visible_in_categories' => $row->visible_in_categories,
                'categories_filter' => $newCategoriesFilter,
                'attributes_filter' => $row->attributes_filter,
                'attributes_options_filter' => $row->attributes_options_filter,
                'slider_min' => $row->slider_min,
                'slider_max' => $row->slider_max,
                'rel_nofollow' => $row->rel_nofollow,
                'show_icons_on_product' => $row->show_icons_on_product,
                'category_tree_display_mode' => $row->category_tree_display_mode,
                'position_label' => $row->position_label,
                'top_position' => $row->top_position,
                'side_position' => $row->side_position,
                'attribute_url_alias' => $row->attribute_url_alias,
                'hide_zeros' => $row->hide_zeros
            ];

            //creating and saving
            $this->filterSettingFactory->create()
                ->setData($data)
                ->save();
        }

        $this->fillAttributeIdFilters->apply();
        return $result;
    }

    private function mapCategoriesFilter($categoriesFilter, $categoriesMap)
    {
        $result = [];
        $cats = explode(',', $categoriesFilter);
        foreach ($cats as $cat) {
            if (isset($categoriesMap[$cat]))
                $result[] = $categoriesMap[$cat];
        }
        return implode(',', $result);
    }

    // Map old category Ids from coker to zip
    private function getCategoriesMap()
    {
        $categories = [];
        try {
            $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . self::CATEGORIES_FILE_PATH;
            if (!$this->file->isExists($csvPath)) {
                throw new \Exception("Error with the JSON file.");
            }

            $rootCategoryCoker = $this->getRootCategory(CreateCokerCategory::COKER_ROOT_CATEGORY_NAME)->getAllChildren(true);
            $rootCategoryWV = $this->getRootCategory(CreateWVCategory::WV_ROOT_CATEGORY_NAME)->getAllChildren(true);

            $childIDs = array_merge($rootCategoryCoker, $rootCategoryWV);

            $rows = json_decode($this->file->fileGetContents($csvPath));
            foreach ($rows as $key => $row) {
                if ($row->url_path) {
                    $category = $this->categoryCollectionFactory
                        ->create()
                        ->addAttributeToFilter('url_path', $row->url_path)
                        ->addAttributeToFilter('entity_id', array('in' => $childIDs))
                        ->getFirstItem(); // The child category
                    if ($category->getData('entity_id'))
                        $categories[$row->entity_id] = $category->getData('entity_id');
                }
            }
        } catch (\Exception $e) {
        }
        return $categories;
    }


    /**
     * @return \Magento\Catalog\Model\Category
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getRootCategory($name): \Magento\Catalog\Model\Category
    {
        $collection = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $name)
            ->setPageSize(1);

        return $collection->getFirstItem();
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function _cleanBeforeImport(): void
    {
        $connection = $this->filterSettingResource->getConnection();

        $connection->beginTransaction();
        $connection->delete(
            $this->filterSettingResource->getMainTable(),
            $connection->quoteInto('setting_id > ?', ['0'])
        );
        $connection->commit();
    }
}
