<?php
/**
 * @namespace   Crimson
 * @module      Category
 * @author      Chad Carlson
 * @email       ccarlson@crimsonagility.com
 * @date        06/25/2019
 * @brief       Command to fix meta_title on categories from .csv file located in /etc/data folder of module
 */
namespace Crimson\Category\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixMetalTitleCategories extends Command
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
        $this->setName('crimson:fix_meta_title_categories');
        $this->setDescription('Fix Meta Title from csv file located in /etc/data folder of module');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // initial message

        //$this->_helper->setConsoleColor('yellow');
        $output->writeln("Importing category meta title fixes from file...");

        // get root path
        $rootPath = $this->_directoryList->getRoot();
        // get file path
        $filename = $rootPath . '/app/code/Crimson/Category/Setup/data/categories-default.csv';
        // insert data from file to an array
        $categoriesArrayData = $this->_csvProcessor->getData($filename);
        // remove header data from array
        array_shift($categoriesArrayData);

        // start processing data
        $currentCategoryCount = 1;

        $foundMatch = 0;
        $nonMatchFound = 0;

        $test = [];

        foreach ($categoriesArrayData as $category){

            $catName = $category[0];
            $image = $category[6];
            $metaKeywords =  $category[8];
            $description = $category[5];

            $metaTitle = $category[7];

            // filter category name and image
            /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collectionFilterByName */
            $collectionFilterByName = $this->_categoryCollectionFactory->create();
            $collectionFilterByName->addAttributeToSelect('*')
                ->addAttributeToFilter('name', $catName)
                ->addAttributeToFilter('image', $image);

            if ($collectionFilterByName->count() == 1) {
                $collectionFilterByName->getFirstItem()->setStoreId(0)->setData('meta_title', $metaTitle)->save();
                $foundMatch++;
            } else {

                //try filtering by name and meta_keywords
                /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collectionFilterByName */
                $collectionFilterByName = $this->_categoryCollectionFactory->create();
                $collectionFilterByName->addAttributeToSelect('*')
                    ->addAttributeToFilter('name', $catName)
                    ->addAttributeToFilter('meta_keywords', $metaKeywords);

                if ($collectionFilterByName->count() == 1) {
                    $collectionFilterByName->getFirstItem()->setStoreId(0)->setData('meta_title', $metaTitle)->save();
                    $foundMatch++;
                } else {

                    if ($collectionFilterByName->count() == 0) {

                        //try to locate using just name
                        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collectionFilterByName */
                        $collectionFilterByName = $this->_categoryCollectionFactory->create();
                            $collectionFilterByName->addAttributeToSelect(['description', 'meta_keywords'])
                            ->addAttributeToFilter('name', $catName);

                        if ($collectionFilterByName->count() > 0 && $collectionFilterByName->count() <= 2) {

                            //$output->writeln('count: '.$collectionFilterByName->count());
                            $test[$catName][] = $metaTitle;

                            /** @var \Magento\Catalog\Model\Category $filteredCategory */
                            foreach ($collectionFilterByName as $filteredCategory) {
                                try {
                                    $existingCategory = $this->_categoryFactory->create();
                                    $existingCategory->load($filteredCategory->getId());

                                    $existingCategory->setStoreId(0);

                                    $existingCategory->setData('meta_title', $metaTitle);
                                    $existingCategory->save();

                                } catch (\Exception $e) {
                                    $output->writeln('Skipping Existing Category');
                                }
                            }
                            $foundMatch++;
                        } else {
                            $nonMatchFound++;
                        }
                    } else {
                        $nonMatchFound++;
                    }
                }
            }
            $currentCategoryCount++;
        }


        $output->writeln('meta_keywords: '.print_r($test, 1));
        $output->writeln('meta_keywords_count: '.count($test));

        $output->writeln("All categories have been imported!");
        $output->writeln("found: ".$foundMatch);
        $output->writeln("none found match: ".$nonMatchFound);


    }
}
