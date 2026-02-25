<?php
/**
 * @namespace   Crimson
 * @module      Category
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        12/26/2018
 * @brief       Command to import categories from .csv file located in /etc/data folder of module
 */
namespace Crimson\Category\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCategoriesCorvetteGifts extends Command
{
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $_csvProcessor;

    /**
     * \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var \Crimson\Category\Helper\Data
     */
    protected $_helper;

    /**
     * ImportCategories constructor.
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Crimson\Category\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Crimson\Category\Helper\Data $helper
    ) {
        $this->_csvProcessor = $csvProcessor;
        $this->_directoryList = $directoryList;
        $this->_categoryFactory = $categoryFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_helper = $helper;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('crimson:import:categories-corvette-gifts');
        $this->setDescription('Import categories from csv file located in /etc/data folder of module - corvette gifts categories');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // initial message
        $this->_helper->setConsoleColor('yellow');
        $output->writeln("Importing categories from file...");

        // get root path
        $rootPath = $this->_directoryList->getRoot();
        // get file path
        $filename = $rootPath . '/app/code/Crimson/Category/Setup/data/categories-corvette-gifts.csv';
        // insert data from file to an array
        $categoriesArrayData = $this->_csvProcessor->getData($filename);
        // remove header data from array
        array_shift($categoriesArrayData);

        // start processing data
        $this->_helper->setConsoleColor('green');
        $currentCategoryCount = 1;
        $this->_helper->showStatus(0, count((array)$categoriesArrayData));
        foreach ($categoriesArrayData as $category){
            // get root path for the new category
            $rootPath = explode("/", $category[1]);
            // get parent name for the new category
            $parentName = end($rootPath);
            // fix to root category name
            if ($parentName == 'Default'){
                $parentName = 'Default Category';
            }

            // build the path that will be used to filter to create the new category
            $idsPath = '%1/';
            $idsPathToCompareWhenDuplicatedName = '1';
            foreach ($rootPath as $namePath){
                // fix to root category name
                if ($namePath == 'Default'){
                    $namePath = 'Default Category';
                }

                // filter category collection by name
                $filterCategoriesByName = $this->_categoryCollectionFactory->create()->addAttributeToFilter('name', $namePath);

                // check size of the collection
                if ($filterCategoriesByName->getSize()){
                    $collectionSize = $filterCategoriesByName->getSize();
                    // if there is only one category get the id of that one
                    if ($collectionSize < 2){
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
            $collectionFilterByName = $this->_categoryCollectionFactory->create()->addAttributeToSelect('*')->addAttributeToFilter('name', $parentName);

            // get parent category id for the new category
            $parentCategoryId = '';
            // get parent category path for the new category
            $parentCategoryPath = '';
            if ($collectionFilterByName->getSize()) {
                $size = $collectionFilterByName->getSize();
                if ($size > 1){
                    // if there are many categories with the same name, filter the collection by the category path to get the corresponding id
                    $collectionFilterByNameAndPath = $this->_categoryCollectionFactory->create()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('name', $parentName)
                        ->addAttributeToFilter('path', array('like' => $idsPath));
                    $parentCategoryId = $collectionFilterByNameAndPath->getFirstItem()->getId();
                    $parentCategoryPath = $collectionFilterByNameAndPath->getFirstItem()->getPath();
                } else {
                    // there is only one category with that name, so get the corresponding id
                    $parentCategoryId = $collectionFilterByName->getFirstItem()->getId();
                    $parentCategoryPath = $collectionFilterByName->getFirstItem()->getPath();
                }
            }

            // create the new category
            $this->createCategory($category, $parentCategoryPath, $parentCategoryId);

            // show status on console
            $this->_helper->showStatus($currentCategoryCount, count((array)$categoriesArrayData));
            $currentCategoryCount++;
        }

        // final message
        $this->_helper->setConsoleColor('yellow');
        $output->writeln("All categories have been imported!");
        $this->_helper->setConsoleColor('white');
    }

    /**
     * Create Category
     *
     * @param $row
     * @param $path
     * @param $parentId
     * @throws \Exception
     */
    public function createCategory($row, $path, $parentId)
    {
        switch ($row[11]){
            case 'Products only_AND_Static block only':
                $displayMode = \Magento\Catalog\Model\Category::DM_MIXED;
                break;
            case 'Products only':
                $displayMode = \Magento\Catalog\Model\Category::DM_PRODUCT;
                break;
            default:
                $displayMode = \Magento\Catalog\Model\Category::DM_PAGE;
                break;
        }
        $newCategory = $this->_categoryFactory->create();
        $newCategory->setName($row[0]);
        $newCategory->setIsActive($row[3] == 'Yes' ? 1 : 0);
        $newCategory->setPosition($row[2]);
        $newCategory->setIncludeInMenu($row[10] == 'Yes' ? 1 : 0);
        $newCategory->setUrlKey($row[4]);
        $newCategory->setParentId($parentId);
        $newCategory->setPath($path);
        $newCategory->setDisplayMode($displayMode);
        $mediaAttribute = array ('image', 'small_image', 'thumbnail');
        $newCategory->setImage($row[6], $mediaAttribute, true, false);
        $newCategory->setData('description', $row[5]);
        $newCategory->setData('meta_keywords', $row[8]);
        $newCategory->setData('meta_description', $row[9]);
        $newCategory->setData('is_anchor', $row[13] == 'Yes' ? 1 : 0);
        $newCategory->save();
    }

    /**
     * Find category id by parent id
     *
     * @param $idsPathToCompareWhenDuplicatedName
     * @param $collection
     * @return string
     */
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
}
