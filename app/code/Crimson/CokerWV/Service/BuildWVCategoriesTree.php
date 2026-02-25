<?php

namespace Crimson\CokerWV\Service;

use Crimson\CokerWV\Api\WVStoreInterface;
use Crimson\CokerWV\Setup\Patch\Data\CreateWVCategory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\File\CsvFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;

class BuildWVCategoriesTree
{
    const CSV_FILE_PATH = '/import/coker_zip_data_migration/wv_categories.csv';
    private array $arrayCatCreated = [];

    public function __construct(
        private readonly CategoryFactory                            $categoryFactory,
        private readonly CategoryRepository                         $categoryRepository,
        private readonly DirectoryList                              $directoryList,
        private readonly CsvFactory                                 $csvReaderFactory,
        private readonly \Magento\Store\Model\StoreManagerInterface $storeManager,
        private readonly \Magento\Store\Model\App\Emulation         $emulation
    )
    {
    }

    /**
     * @throws CouldNotSaveException
     */
    public function execute(): void
    {
        $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . self::CSV_FILE_PATH;
        $csvReader = $this->csvReaderFactory->create();
        $rows = $csvReader->getData($csvPath);
        // Clean up the categories with filler url_key
        foreach ($rows as $row){
            if($row[0] == 2){
                $urlKey = 'wv-'.$row[3];
                $cleanUpCat = $this->categoryFactory->create();
                $cleanUpCat = $cleanUpCat->loadByAttribute('url_key', $urlKey);
                if ($cleanUpCat && $cleanUpCat->getId()){
                    $this->categoryRepository->delete($cleanUpCat);
                }
            }
        }

        $wvStoreId        = $this->storeManager->getStore(WVStoreInterface::WV_STORE_CODE)->getId();
        $this->emulation->startEnvironmentEmulation($wvStoreId, \Magento\Framework\App\Area::AREA_FRONTEND, true);

        $this->_initRootCategoryLevel();
        $catByLevel = $this->_getArrCategoriesByLevel($rows);
        foreach ($catByLevel as $level => $levelCats) {
            foreach ($levelCats as $categoryData) {
                // Create category, return new category id and name
                // Build an array to map category name and id
                $parentId = $this->arrayCatCreated[$level - 1][$categoryData['parent_cat']];

                /** @var \Magento\Catalog\Model\Category $category */
                $category = $this->categoryFactory->create();
                $category
                    ->setStoreId($wvStoreId)
                    ->setName($categoryData['cat_name'])
                    ->setIsActive(true)
                    ->setIncludeInMenu(true)
                    ->setUrlKey($categoryData['url_key'])
                    ->setParentId($parentId);
                try {
                    $category = $this->categoryRepository->save($category);
                }
                catch (\Exception $e){
                    print_r($categoryData);
                    echo $e->getMessage();
                }
                $this->arrayCatCreated[$level][$categoryData['cat_name']] = $category->getId();
            }
        }
        $this->emulation->stopEnvironmentEmulation();
    }

    private function _initRootCategoryLevel()
    {

        $collection = $this->categoryFactory->create()
            ->getCollection()
            ->addAttributeToFilter('name', CreateWVCategory::WV_ROOT_CATEGORY_NAME)
            ->setPageSize(1);

        if ($collection->getSize()) {
            $rootCategoryId = $collection->getFirstItem()->getId();
            $this->arrayCatCreated[1][CreateWVCategory::WV_ROOT_CATEGORY_NAME] = $rootCategoryId;
        }
    }

    private function _getArrCategoriesByLevel($rows)
    {
        $arrCatByLevel = [];
        foreach ($rows as $num => $data) {
            if ($num > 0) {
                $arrCatByLevel[$data[0]][] = [
                    'cat_name' => $data[1],
                    'parent_cat' => $data[2],
                    'url_key' => $data[3]
                ];
            }
        }

        return $arrCatByLevel;
    }
}
