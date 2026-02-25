<?php

namespace Crimson\CorvetteCentralImport\Console\Command;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\File\Csv;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\App\Emulation;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCategories extends Command
{

    CONST CSV_FILE_NAME  = 'categories_cc.csv';
    CONST CSV_FILE_PATH  = "/var/import/ImportCCProducts/CategoryData/";

    public function __construct(
        protected Csv $csvProcessor,
        protected DirectoryList $directoryList,
        protected CategoryFactory $categoryFactory,
        protected CollectionFactory $categoryCollectionFactory,
        protected UrlRewrite $urlRewrite,
        protected StoreRepositoryInterface $storeRepositoryInterface,
        protected CategoryRepositoryInterface $categoryRepository,
        protected Emulation $appEmulation,
        protected State $appState
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('crimson:import:cc_categories');
        $this->setDescription('Import CC categories from CSV file');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ccStoreId = $this->storeRepositoryInterface->get(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();
        $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        $this->appEmulation->startEnvironmentEmulation($ccStoreId, Area::AREA_ADMINHTML);

        //clearing CC category ur rewrites
        $this->_removeCCUrlRewritesBeforeImport($ccStoreId);

        // initial message
        $this->setConsoleColor('yellow');
        $output->writeln("Importing categories from file...");

        // get files name
        $filename = $this->directoryList->getRoot() . self::CSV_FILE_PATH . self::CSV_FILE_NAME;

        // insert data from file to an array
        $categoriesArrayData = $this->csvProcessor->getData($filename);

        // remove header data from array
        array_shift($categoriesArrayData);

        // start processing data
        $this->setConsoleColor('green');
        $currentCategoryCount = 1;
        $this->showStatus(0, count((array)$categoriesArrayData));
        foreach ($categoriesArrayData as $category) {
            // get root path for the new category
            $rootPath = explode("__/__", $category[1]);
            // get parent name for the new category
            $parentName = end($rootPath);

            // build the path that will be used to filter to create the new category
            $idsPath = '%1/';
            $idsPathToCompareWhenDuplicatedName = '1';
            foreach ($rootPath as $namePath) {
                // filter category collection by name
                $filterCategoriesByName = $this->categoryCollectionFactory->create()->addAttributeToFilter('name', $namePath);

                // check size of the collection
                if ($filterCategoriesByName->getSize()) {
                    $collectionSize = $filterCategoriesByName->getSize();
                    // if there is only one category get the id of that one
                    if ($collectionSize < 2) {
                        // build id path
                        $idsPath .= $filterCategoriesByName->getFirstItem()->getId() .  '/';
                        $idsPathToCompareWhenDuplicatedName .= '/' . $filterCategoriesByName->getFirstItem()->getId();
                    } else {
                        // there are many categories with the same name, get the corresponding id by the parent id
                        $findId = $this->findCategoryByParent($idsPathToCompareWhenDuplicatedName, $filterCategoriesByName);
                        $idsPath .= $findId . '/';
                        $idsPathToCompareWhenDuplicatedName .= '/' . $findId;
                    }
                }
            }
            // remove last slash from id path
            $idsPath = substr($idsPath, 0, -1);
            $idsPath .= '%';

            // filter category collection by parent category name of the new category
            $collectionFilterByName = $this->categoryCollectionFactory->create()->addAttributeToSelect('*')->addAttributeToFilter('name', $parentName);

            // get parent category id for the new category
            $parentCategoryId = '';
            if ($collectionFilterByName->getSize()) {
                $size = $collectionFilterByName->getSize();
                if ($size > 1) {
                    // if there are many categories with the same name, filter the collection by the category path to get the corresponding id
                    $collectionFilterByNameAndPath = $this->categoryCollectionFactory->create()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('name', $parentName)
                        ->addAttributeToFilter('path', array('like' => $idsPath));
                    $parentCategoryId = $collectionFilterByNameAndPath->getFirstItem()->getId();
                } else {
                    // there is only one category with that name, so get the corresponding id
                    $parentCategoryId = $collectionFilterByName->getFirstItem()->getId();
                }
            }

            // create the new category
            $this->createCategory($ccStoreId, $category, $parentCategoryId);

            // show status on console
            $this->showStatus($currentCategoryCount, count((array)$categoriesArrayData));
            $currentCategoryCount++;
        }

        // final message
        $this->setConsoleColor('yellow');
        $output->writeln("All categories have been imported!");
        $this->setConsoleColor('white');

        $this->appEmulation->stopEnvironmentEmulation();

        return Cli::RETURN_SUCCESS;
    }

    private function _removeCCUrlRewritesBeforeImport($ccStoreId): void
    {
        $condition = [
            'store_id = ?' => $ccStoreId,
            'entity_type = ?' => 'category',
        ];

        $connection = $this->urlRewrite->getConnection();
        $connection->beginTransaction();
        $connection->delete(
            $this->urlRewrite->getMainTable(),
            $condition
        );
        $connection->commit();
    }

    public function createCategory($ccStoreId, $row, $parentId)
    {
        try {
            $newCategory = $this->categoryFactory->create();
            $newCategory->setName($row[0]);
            $newCategory->setUrlKey($row[2] ?? $this->getPathUrl($row[0]));
            $newCategory->setParentId($parentId);
            $newCategory->setData('description', $row[3] );
            $newCategory->setData('meta_title', $row[4]);
            $newCategory->setData('meta_description', $row[5]);
            $newCategory
                ->setIsActive(true)
                ->setIsAnchor(1)
                ->setIncludeInMenu(true)
                ->setDisplayMode(Category::DM_MIXED);

            $this->categoryRepository->save($newCategory);
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
    }

    private function getPathUrl(string $pathUrl): string
    {
        if (!$pathUrl) {
            throw new \Exception("Category URL name cannot be empty.");
        }

        $search  = [' ', '/', '"', '\''];
        $replace = ['-', '-', '', ''];

        return str_replace($search, $replace, strtolower($pathUrl));
    }

    public function findCategoryByParent($idsPathToCompareWhenDuplicatedName, $collection)
    {
        $idsPathArray = explode('/', $idsPathToCompareWhenDuplicatedName);
        $idToSearch = end($idsPathArray);
        $idReturn = '';
        foreach ($collection as $category){
            $parentId = $category->getParentcategory()->getId();
            if ($idToSearch == $parentId){
                $idReturn = $category->getId();
            }
        }
        return $idReturn;
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

    public function showStatus($done, $total, $size = 30)
    {
        static $start_time;

        // if we go over our bound, just ignore it
        if($done > $total) return;

        if(empty($start_time) || $done == 0) $start_time=time();
        $now = time();

        $perc = (double) ($done/$total);

        $bar = floor($perc * $size);

        $this->setConsoleColor('white');

        $statusBar="\r[";
        $statusBar .= str_repeat("#", $bar);

        if ($bar < $size) {
            $statusBar .= "";
            $statusBar .= str_repeat(" ", $size-$bar);
        } else {
            $statusBar .= "#";
        }

        $disp = number_format($perc * 100, 0);

        $statusBar .= "]";

        $this->setConsoleColor('green');

        $statusBar .= " $disp% - $done processed of $total in total";

        echo "$statusBar";

        flush();

        // when done, send a newline
        if($done === $total) {
            echo "\n";
        }

    }
}
