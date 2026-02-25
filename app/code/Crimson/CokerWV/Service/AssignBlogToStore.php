<?php

namespace Crimson\CokerWV\Service;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magefan\Blog\Api\TagRepositoryInterface;
use Magefan\Blog\Api\PostRepositoryInterface;
use Magefan\Blog\Api\CategoryRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Api\StoreRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;

class AssignBlogToStore
{
    const RELATEDPRODUCT_CSV_PATH = '/import/coker_zip_data_migration/blog/magefan_blog_post_relatedproduct.csv';

    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly PostRepositoryInterface     $postRepository,
        private readonly TagRepositoryInterface      $tagRepository,
        private readonly SearchCriteriaBuilder       $searchCriteriaBuilder,
        private readonly StoreRepositoryInterface    $storeRepositoryInterface,
        private readonly ProductRepositoryInterface  $productRepository,
        private readonly Csv                         $csvReader,
        private readonly DirectoryList               $directoryList,
        private readonly File                        $file,
        private readonly LoggerInterface             $logger
    )
    {
    }

    public function execute(): array
    {
        $result['message'] = "Assigned stores and related product data for blog entities.";
        $cokerTireStoreId = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_STORE_CODE)->getId();

        try {
            $this->assignCategoryStore($cokerTireStoreId);
            $this->assignPostStore($cokerTireStoreId);
            $this->assignTagStore($cokerTireStoreId);
            $this->assignRelatedProductToPost();
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }
        return $result;
    }

    private function assignRelatedProductToPost()
    {
        $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . self::RELATEDPRODUCT_CSV_PATH;
        if (!$this->file->isExists($csvPath)) {
            throw new \Exception("Error with the CSV file.");
        }

        $rows = $this->csvReader->getData($csvPath);

        array_shift($rows);
        $linkData = [];
        foreach ($rows as $key => $row) {
            $data = [
                'post_id' => $row[0],
                'position' => $row[2],
                'product_sku' => $row[3]
            ];
            try {
                $product = $this->productRepository->get($data['product_sku']);
                if ($productId = $product->getId()) {
                    $linkData[$data['post_id']][$productId] = [
                        'position' => $data['position']
                    ];
                }
            } catch (\Exception $e) {
                $this->logger->debug($e);
            }
        }
        foreach ($linkData as $postId => $relatedProduct) {
            $links = ['post' => [], 'product' => $relatedProduct];
            $post = $this->postRepository->getById($postId);
            $post->setData('links', $links);
            try {
                $this->postRepository->save($post);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->debug($e->getMessage() . ' post_id: ' . $postId);
            }
        }
    }

    private function assignCategoryStore($cokerTireStoreId)
    {
        $categories = $this->categoryRepository->getList($this->searchCriteriaBuilder->create());
        foreach ($categories->getItems() as $cat) {
            $catModel = $this->categoryRepository->getById($cat['category_id']);
            $catModel->setData('store_ids', [$cokerTireStoreId]);
            try {
                $this->categoryRepository->save($catModel);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->debug($e->getMessage() . ' category_id: ' . $cat['category_id']);
            }
        }
    }

    private function assignPostStore($cokerTireStoreId)
    {
        $posts = $this->postRepository->getList($this->searchCriteriaBuilder->create());
        foreach ($posts->getItems() as $post) {
            $postModel = $this->postRepository->getById($post['post_id']);
            $postModel->setData('store_ids', [$cokerTireStoreId]);
            try {
                $this->postRepository->save($postModel);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->debug($e->getMessage() . ' post_id: ' . $post['post_id']);
            }
        }
    }

    private function assignTagStore($cokerTireStoreId)
    {
        $tags = $this->tagRepository->getList($this->searchCriteriaBuilder->create());
        foreach ($tags->getItems() as $tag) {
            $tagModel = $this->tagRepository->getById($tag['tag_id']);
            $tagModel->setData('store_ids', [$cokerTireStoreId]);
            try {
                $this->tagRepository->save($tagModel);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->debug($e->getMessage() . ' tag_id: ' . $tag['tag_id']);
            }
        }
    }
}
