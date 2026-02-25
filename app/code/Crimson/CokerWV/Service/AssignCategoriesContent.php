<?php

namespace Crimson\CokerWV\Service;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Crimson\CokerWV\Setup\Patch\Data\CreateWVCategory;
use Crimson\CokerWV\Setup\Patch\Data\CreateCokerCategory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\File\CsvFactory;
use Psr\Log\LoggerInterface;

class AssignCategoriesContent
{

    const CSV_FILE_PATH_COKER = '/import/coker_zip_data_migration/coker_category_contents.csv';
    const CSV_FILE_PATH_COKER_STOREVIEW = '/import/coker_zip_data_migration/coker_category_contents_storeview.csv';
    const CSV_FILE_PATH_WV = '/import/coker_zip_data_migration/wv_category_contents.csv';
    public function __construct(
        private readonly CategoryFactory $categoryFactory,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly DirectoryList $directoryList,
        private readonly CsvFactory $csvReaderFactory,
        private readonly LoggerInterface $logger,
        private readonly \Magento\Store\Model\App\Emulation $emulation,
        private readonly \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {}

    /**
     * @throws CouldNotSaveException
     */
    public function execute(): void
    {
        $cokerTireStoreId        = $this->storeManager->getStore(CokerStoreInterface::COKER_STORE_CODE)->getId();
        $wvStoreId               = $this->storeManager->getStore(WVStoreInterface::WV_STORE_CODE)->getId();

        $cokerStoreRoot = $this->getRootCategory(CreateCokerCategory::COKER_ROOT_CATEGORY_NAME);
        $this->assignCategoryContent(self::CSV_FILE_PATH_COKER, $cokerStoreRoot,0);
        $this->assignCategoryContent(self::CSV_FILE_PATH_COKER_STOREVIEW, $cokerStoreRoot,$cokerTireStoreId);

        $wvStoreRoot = $this->getRootCategory(CreateWVCategory::WV_ROOT_CATEGORY_NAME);
        $this->assignCategoryContent(self::CSV_FILE_PATH_WV, $wvStoreRoot,$wvStoreId);
    }

    private function assignCategoryContent($filePath, $storeRoot, $storeId){
        $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).$filePath;
        $csvReader = $this->csvReaderFactory->create();
        $rows = $csvReader->getData($csvPath);
        $childIds = explode(',',$storeRoot->getAllChildren(false));

        $this->emulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        foreach ($rows as $num=>$row){
            if($num>1) {
                $urlKey = str_replace(['coker-', 'wv-'], '', $row[0]);
                $description = $row[1] != 'NULL' ? str_replace(['&comma;','&quot;'], [',','"'], $row[1])  : null;
                $meta_title = $row[2] != 'NULL' ? str_replace('&comma;', ',', $row[2]) : null;
                $meta_keywords = $row[3] != 'NULL' ? str_replace('&comma;', ',', $row[3]) : null;
                $meta_description = $row[4] != 'NULL' ? str_replace('&comma;', ',', $row[4]) : null;
                $layout = $row[5] != 'NULL' ? $row[5] : null;
                $topDescription = (isset($row[6]) && $row[6] != 'NULL')?str_replace(['&comma;','&quot;'], [',','"'], $row[6]) : null;

                $category = $this->categoryCollectionFactory
                    ->create()
                    ->addAttributeToFilter('url_key', $urlKey)
                    ->addAttributeToFilter('entity_id', array('in' => $childIds))
                    ->getFirstItem(); // The child category
                $category = $this->categoryFactory->create()->load($category->getId());
                $category
                    ->setStoreId($storeId)
                    ->setData('description', $description)
                    ->setData('meta_title', $meta_title)
                    ->setData('meta_keywords', $meta_keywords)
                    ->setData('meta_description', $meta_description)
                    ->setData('top_description', $topDescription)
                    ->setData('page_layout', $layout);
                try {
                    $category->save();
                } catch (\Exception $e)
                {
                    $this->logger->debug($e->getMessage().' url_key: '.$urlKey);
                    $this->logger->debug($e);
                }
            }
        }
        $this->emulation->stopEnvironmentEmulation();
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
}
