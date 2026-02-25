<?php

namespace Crimson\CorvetteCentralImport\Console\Command;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magefan\Blog\Api\CategoryRepositoryInterface;
use Magefan\Blog\Api\PostRepositoryInterface;
use Magefan\Blog\Model\CategoryFactory;
use Magefan\Blog\Model\PostFactory;
use Magefan\Blog\Model\ResourceModel\Category;
use Magefan\Blog\Model\ResourceModel\Post;
use Magefan\Blog\Model\ResourceModel\Tag;
use Magefan\Blog\Model\TagFactory;
use Magefan\Blog\Model\TagRepository;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\App\Emulation;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportBlogs extends Command
{

    CONST TAGS_FILE_NAME  = 'tags.json';
    CONST CATEGORIES_FILE_NAME  = 'categories.json';
    CONST POSTS_FILE_NAME  = 'posts.json';
    CONST FILE_PATH  = "/import/cc_blog/";
    private $_ccStoreId = null;
    private $_createdTagMap = [];
    private $_createdCategoryMap = [];
    public function __construct(
        protected DirectoryList $directoryList,
        protected TagRepository $tagRepository,
        protected CategoryRepositoryInterface $categoryRepository,
        protected PostRepositoryInterface $postRepository,
        protected File $file,
        protected TagFactory $tagFactory,
        protected PostFactory $postFactory,
        protected Tag $tagResources,
        protected Post $postResources,
        protected CategoryFactory $categoryFactory,
        protected Category $categoryResources,
        protected StoreRepositoryInterface $storeRepositoryInterface,
        protected LoggerInterface $logger,
        protected Emulation $appEmulation,
        protected State $appState
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('crimson:import:cc_blogs');
        $this->setDescription('Import CC blogs categories/tags/posts');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ccStoreId = $this->getCCStoreId();
        $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        $this->appEmulation->startEnvironmentEmulation($ccStoreId, Area::AREA_ADMINHTML);

        // initial message
        $this->setConsoleColor('yellow');
        $output->writeln("Importing tags from file...");
        $tags = $this->_importTags();
        $output->writeln("All tags have been imported!");

        $output->writeln("Importing categories from file...");
        $categories = $this->_importCategories();
        $output->writeln("All categories have been imported!");

        $output->writeln("Importing posts from file...");
        $posts = $this->_importPosts();
        $this->setConsoleColor('green');
        $output->writeln("All blogs have been imported!");
        $this->setConsoleColor('white');

        $this->appEmulation->stopEnvironmentEmulation();

        return Cli::RETURN_SUCCESS;
    }

    private function _importTags()
    {
        try {
            $path = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::FILE_PATH.self::TAGS_FILE_NAME;
            if (!$this->file->isExists($path)) {
                throw new \Exception("Error with the JSON file.");
            }
            $rows = json_decode($this->file->fileGetContents($path));
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        if (!empty($rows)) {
            $ccStoreId = $this->getCCStoreId();
            $this->_removeCCTagsBeforeImport($ccStoreId);
            foreach ($rows as $row){
                $tagModel = $this->tagFactory->create();
                if(is_numeric($row->tag_slug))
                {
                    $row->tag_slug = 'cc_'.$row->tag_slug; // Magefan doesn't allow tag url to be number only
                }
                $data = [
                    'is_active' => 1,
                    'identifier' => $row->tag_slug,
                    'title' => $row->tag_name,
                    'meta_title' => $row->tag_name,
                    'store_ids' => [$ccStoreId]
                ];
                $tagModel->addData($data);
                try {
                    $tagModel = $this->tagRepository->save($tagModel);
                }catch (\Exception $exception){
                    $data ['title'] = 'CC:'.$row->tag_name;
                    $data ['meta_title'] = 'CC:'.$row->tag_name;
                    $tagModel->addData($data);
                    $tagModel = $this->tagRepository->save($tagModel);
                }
                $this->_createdTagMap[$row->tag_id] = $tagModel->getId();
            }
        }
        return $this->_createdTagMap;
    }
    private function _importCategories()
    {
        try {
            $path = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::FILE_PATH.self::CATEGORIES_FILE_NAME;
            if (!$this->file->isExists($path)) {
                throw new \Exception("Error with the JSON file.");
            }
            $rows = json_decode($this->file->fileGetContents($path));
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        if (!empty($rows)) {
            $ccStoreId = $this->getCCStoreId();
            $this->_removeCCBlogCategoriesBeforeImport($ccStoreId);
            foreach ($rows as $row){
                $catModel = $this->categoryFactory->create();
                $path = '0';
                if(($row->parent_id>0))
                {
                    $path = $this->_createdCategoryMap[$row->parent_id]??0;
                }
                $data = [
                    'is_active' => 1,
                    'identifier' => $row->category_slug,
                    'title' => $row->category_name,
                    'meta_title' => $row->category_name,
                    'path' => $path,
                    'store_ids' => [$ccStoreId]
                ];
                $catModel->addData($data);
                try {
                    $catModel = $this->categoryRepository->save($catModel);
                }catch (\Exception $exception){
                    $data ['title'] = 'CorvetteCentral:'.$row->tag_name;
                    $data ['meta_title'] = 'CorvetteCentral:'.$row->tag_name;
                    $data ['identifier'] = 'cc_'.$row->category_slug;
                    $catModel->addData($data);
                    $catModel = $this->categoryRepository->save($catModel);
                }
                $this->_createdCategoryMap[$row->category_id] = $catModel->getId();
            }
        }
        return $this->_createdCategoryMap;
    }
    private function _importPosts()
    {
        try {
            $path = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::FILE_PATH.self::POSTS_FILE_NAME;
            if (!$this->file->isExists($path)) {
                throw new \Exception("Error with the JSON file.");
            }
            $rows = json_decode($this->file->fileGetContents($path));
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        if (!empty($rows)) {
            $ccStoreId = $this->getCCStoreId();
            $this->_removeCCPostsBeforeImport($ccStoreId);
            foreach ($rows as $row){
                $postModel = $this->postFactory->create();
                $arrNewCategories = [];
                $arrNewTags = [];
                if(($row->categories))
                {
                    $arrCategories = explode(',',$row->categories);
                    foreach ($arrCategories as $oldCategory){
                        $arrNewCategories[] = (int)$this->_createdCategoryMap[(int)$oldCategory];
                    }
                }
                if(($row->tags))
                {
                    $arrTags = explode(',',$row->tags);
                    foreach ($arrTags as $oldTag){
                        $arrNewTags[] = (int)$this->_createdTagMap[(int)$oldTag];
                    }
                }

                $content = $this->replaceCaptionShortcodes($row->post_content);

                $data = [
                    'is_active' => ($row->post_status == 'publish')?1:0,
                    'identifier' => $row->post_name,
                    'title' => $row->post_title,
                    'content_heading' => $row->post_title,
                    'content' => $content,
                    'meta_description' => $row->post_excerpt,
                    'short_content' => $row->post_excerpt,
                    'meta_title' => $row->post_title,
                    'featured_img' => $row->thumbnail_url,
                    'publish_time' => $row->post_date,
                    'creation_time' => $row->post_date,
                    'store_ids' => [$ccStoreId],
                    'categories' => $arrNewCategories,
                    'tags' => $arrNewTags,
                ];
                $postModel->addData($data);
                try {
                    $postModel = $this->postRepository->save($postModel);
                }catch (\Exception $exception){
                    $this->logger->debug('Error importing blog : '.$row->post_name.' Error: '.$exception->getMessage());
                }
            }
        }
        return true;
    }

    private function replaceCaptionShortcodes(string $content): string
    {
        $content = str_replace('https://cc.localhost/',"{{config path='web/secure/base_url'}}", $content);
        $pattern = '/\[caption\b[^\]]*\](.*?)\[\/caption\]/is';
        return preg_replace($pattern, '<div class="caption">$1</div>', $content);
    }

    private function getCCStoreId()
    {
        if(!$this->_ccStoreId)
            $this->_ccStoreId = $this->storeRepositoryInterface->get(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();
        return $this->_ccStoreId;
    }

    private function _removeCCTagsBeforeImport($storeId): void
    {
        $connection = $this->tagResources->getConnection();
        $select = $connection->select()
            ->from(['main_table' => $this->tagResources->getTable('magefan_blog_tag_store')])
            ->where('store_id = ?', $storeId)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['main_table.tag_id'])
        ;
        $data = $connection->fetchAll($select);
        if (!$data) {
            $data = [];
        }

        if (!empty($data)) {
            $connection->beginTransaction();
            $connection->delete(
                $this->tagResources->getMainTable(),
                $connection->quoteInto('tag_id IN (?)', [$data])
            );
            $connection->commit();
        }
    }
    private function _removeCCPostsBeforeImport($storeId): void
    {
        $connection = $this->postResources->getConnection();
        $select = $connection->select()
            ->from(['main_table' => $this->postResources->getTable('magefan_blog_post_store')])
            ->where('store_id = ?', $storeId)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['main_table.post_id'])
        ;
        $data = $connection->fetchAll($select);
        if (!$data) {
            $data = [];
        }

        if (!empty($data)) {
            $connection->beginTransaction();
            $connection->delete(
                $this->postResources->getMainTable(),
                $connection->quoteInto('post_id IN (?)', [$data])
            );
            $connection->commit();
        }
    }
    private function _removeCCBlogCategoriesBeforeImport($storeId): void
    {
        $connection = $this->categoryResources->getConnection();
        $select = $connection->select()
            ->from(['main_table' => $this->categoryResources->getTable('magefan_blog_category_store')])
            ->where('store_id = ?', $storeId)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['main_table.category_id'])
        ;
        $data = $connection->fetchAll($select);
        if (!$data) {
            $data = [];
        }

        if (!empty($data)) {
            $connection->beginTransaction();
            $connection->delete(
                $this->categoryResources->getMainTable(),
                $connection->quoteInto('category_id IN (?)', [$data])
            );
            $connection->commit();
        }
    }




    public function setConsoleColor($color)
    {
        switch ($color) {

            case 'yellow':
                echo "\033[0;33m";
                break;

            case 'green':
                echo "\033[32m";
                break;

            case 'red':
                echo "\033[0;31m";
                break;

            case 'white':
                echo "\033[0m";
                break;
        }
    }

}
