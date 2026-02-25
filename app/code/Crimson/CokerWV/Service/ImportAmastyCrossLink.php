<?php

namespace Crimson\CokerWV\Service;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Crimson\CokerWV\Setup\Patch\Data\CreateCokerCategory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Api\StoreRepositoryInterface;
use Amasty\CrossLinks\Model\ResourceModel\Link as LinkResource;
use Amasty\CrossLinks\Model\LinkFactory;
use Magento\Cms\Model\ResourceModel\Block as BlockResource;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Cms\Model\BlockFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ImportAmastyCrossLink
{

    const CSV_FILE_PATH = '/import/coker_zip_data_migration/amasty_cross_link.json';

    public function __construct(
        private readonly DirectoryList              $directoryList,
        private readonly File                       $file,
        private readonly StoreRepositoryInterface   $storeRepositoryInterface,
        private readonly LinkResource               $linkResource,
        private readonly LinkFactory                $linkFactory,
        private readonly CategoryCollectionFactory  $categoryCollectionFactory,
        private readonly ProductRepositoryInterface $productRepository
    )
    {
    }


    public function execute(): array
    {
        $result['message'] = "All Amasty Cross Linking data imported.";
        try {
            $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . self::CSV_FILE_PATH;
            if (!$this->file->isExists($csvPath)) {
                throw new \Exception("Error with the JSON file.");
            }

            $rows = json_decode($this->file->fileGetContents($csvPath));
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        if (!empty($rows)) {
            $cokerTireStoreId = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_STORE_CODE)->getId();
            $this->_cleanBeforeImport([$cokerTireStoreId]);
        }

        $rootCategory = $this->getRootCategory(CreateCokerCategory::COKER_ROOT_CATEGORY_NAME);
        // looping
        foreach ($rows as $key => $row) {
            if (empty($row->link_id) || empty($row->title)) {
                continue;
            }
            switch ($row->reference_type) {
                case '1':
                    $product = $this->productRepository->get($row->sku);
                    $resource = $product->getId();
                    break;
                case '2':
                    $category = $this->categoryCollectionFactory
                        ->create()
                        ->addAttributeToFilter('url_path', $row->category_path)
                        ->addAttributeToFilter('entity_id', array('in' => $rootCategory->getAllChildren(true)))
                        ->getFirstItem(); // The child category
                    $resource = $category->getId();
                    break;
                default:
                    $resource = $row->reference_resource;
            }
            $data = [
                'title' => $row->title,
                'status' => $row->status,
                'keywords' => $row->keywords,
                'link_target' => $row->link_target,
                'reference_type' => $row->reference_type,
                'reference_resource' => $resource,
                'replacement_limit' => $row->replacement_limit,
                'priority' => $row->priority,
                'is_nofollow' => $row->is_nofollow
            ];

            $data['store_ids'] = [$cokerTireStoreId];


            //creating and saving
            $this->linkFactory->create()
                ->setData($data)
                ->save();
        }

        return $result;
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
     * @param array $storeIds
     * @return void
     * @throws LocalizedException
     */
    private function _cleanBeforeImport(array $storeIds): void
    {
        $connection = $this->linkResource->getConnection();

        $connection->beginTransaction();
        $connection->delete(
            $this->linkResource->getMainTable(),
            $connection->quoteInto('status IN (?)', ['0', '1'])
        );
        $connection->commit();
    }
}
