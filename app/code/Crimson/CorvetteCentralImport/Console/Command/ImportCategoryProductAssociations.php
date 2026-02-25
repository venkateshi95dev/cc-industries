<?php

namespace Crimson\CorvetteCentralImport\Console\Command;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\CategoryProduct;
use Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\File\Csv;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\App\Emulation;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite;
use PHPUnit\Util\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;

class ImportCategoryProductAssociations extends Command
{

    CONST CC_PARENT_CATEGORY_ID  = 'cc_main_category_id';
    CONST CSV_FILE_NAME  = 'COKG-Product level Category Assignment 2025-06-30.csv';
    CONST CSV_FILE_PATH  = "/var/import/ImportCCProducts/CategoryAssociation/";

    protected array $categories = [];
    protected ?int $storeId = null;
    protected ?int $websiteId = null;

    public function __construct(
        protected Csv $csvProcessor,
        protected DirectoryList $directoryList,
        protected CategoryFactory $categoryFactory,
        protected CollectionFactory $categoryCollectionFactory,
        protected UrlRewrite $urlRewrite,
        protected StoreRepositoryInterface $storeRepositoryInterface,
        protected CategoryRepositoryInterface $categoryRepository,
        protected Emulation $appEmulation,
        protected CategoryProcessor $categoryProcessor,
        protected CategoryLinkManagementInterface $categoryLinkManagementInterface,
        protected ProductResource $productResource,
        protected CategoryProduct $categoryProductResource,
        protected State $appState
    ) {
        parent::__construct();
        $store = $this->storeRepositoryInterface->get(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE);
        $this->storeId = $store->getId();
        $this->websiteId = $store->getWebsiteId();
    }

    protected function configure(): void
    {
        $this->setName('crimson:import:category_product_associations');
        $this->setDescription('Import CC category-product associations from CSV file');
        $this->addArgument(
            self::CC_PARENT_CATEGORY_ID,
            InputArgument::REQUIRED,
            'CC Main Category ID.'
        );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ccMainCategoryId = $input->getArgument(self::CC_PARENT_CATEGORY_ID);
        if ((int)$ccMainCategoryId <= 0) {
            $output->writeln("<error>Wrong category ID.</error>");
            return Cli::RETURN_FAILURE;
        }

        $this->initCategories($ccMainCategoryId);

        $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        $this->appEmulation->startEnvironmentEmulation($this->storeId, Area::AREA_ADMINHTML);

        // get files name
        $filename = $this->directoryList->getRoot() . self::CSV_FILE_PATH . self::CSV_FILE_NAME;
        // insert data from file to an array
        $categoriesArrayData = $this->csvProcessor->getData($filename);
        // remove header data from array
        array_shift($categoriesArrayData);

        $associationsData = [];
        foreach (array_chunk($categoriesArrayData, 1000, true) as $currentRows) {
            foreach ($currentRows as $row) {
                $associationsData[$row[0]] = array_unique(
                    array_merge(
                        $associationsData[$row[0]] ?? [],
                        $this->upsertCategories($row[3], "~@~", $output)),
                    SORT_REGULAR
                );
            }
        }

        $skuIds = $this->_getSkuIds(array_keys($associationsData));
        if (empty($skuIds)) {
            return;
        }

        foreach (array_chunk($associationsData, 1000, true) as $currentAssocRows) {
            foreach ($currentAssocRows as $sku => $currentAssocRow) {
                if (empty($currentAssocRow) || empty($skuIds[$sku])) {
                    continue;
                }

                $this->_prepareAndInsertCategoryLink((int) $skuIds[$sku], $currentAssocRow);
            }
        }

        // final message
        $output->writeln("All product-category associations have been imported!");
        $this->appEmulation->stopEnvironmentEmulation();

        return Cli::RETURN_SUCCESS;
    }

    private function _prepareAndInsertCategoryLink(int $productId, array $categoryIds): void
    {
        $result = [];
        foreach ($categoryIds as $categoryId) {
            $result[] = [
                'category_id' => (int) $categoryId,
                'product_id' => $productId,
            ];
        }

        try {
            $connection = $this->categoryProductResource->getConnection();
            $connection->insertMultiple(
                $this->categoryProductResource->getMainTable(),
                $result
            );
        } catch (\Exception $e)
        {
            print_r($e->getMessage());
        }
    }

    private function _getSkuIds(array $productSkus): array
    {
        $connection = $this->productResource->getConnection();
        $select = $connection
            ->select()
            ->from(['c_p_e' => $this->productResource->getTable('catalog_product_entity')])
            ->joinInner(
                ['c_p_w' => $this->productResource->getTable('catalog_product_website')],
                $connection->quoteInto('c_p_w.product_id = c_p_e.entity_id AND c_p_w.website_id = ?', $this->websiteId)
            )
            ->reset(\Zend_Db_Select::COLUMNS)
            ->where('c_p_e.sku IN (?)', $productSkus)
            ->columns([
                'sku'       =>'c_p_e.sku',
                'entity_id' =>'c_p_e.entity_id'
            ]);

        $data = $connection->fetchPairs($select);
        if (!$data) {
            $data = [];
        }

        return $data;
    }

    public function upsertCategories($categoriesString, $categoriesSeparator, $output): array
    {
        $categoriesIds = [];
        $categories = $categoriesString !== null ? explode($categoriesSeparator, $categoriesString) : [];

        foreach ($categories as $category) {
            try {
                $categoriesIds[] = $this->upsertCategory($category);
            } catch (\Exception $e) {
                $output->writeln("<error>".$e->getMessage()."</error>");
            }
        }

        return $categoriesIds;
    }

    protected function upsertCategory($categoryPath)
    {
        $index = '';
        if ($categoryPath !== null) {
           $categoryPathTmp = str_replace("__/__", "__&__", $this->standardizeString($categoryPath));
           $categoryPathTmp = str_replace('/', '\\/', $categoryPathTmp);
            $index = str_replace('__&__', '/', $categoryPathTmp);
            $index = "corvette central/" . $index;
        }

        if (!isset($this->categories[$index])) {
            throw new Exception('The category does not exist: ' . $index);
        }

        return $this->categories[$index];
    }

    protected function initCategories($ccMainCategoryId)
    {
        if (empty($this->categories)) {
            $collection = $this->categoryCollectionFactory->create();
            $collection
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('url_path')
                ->setStoreId($this->storeId);
            $collection->addFieldToFilter('path', ['like' => '1/'.$ccMainCategoryId.'%']);

            foreach ($collection as $category) {
                $structure = explode('/', $category->getPath());
                $pathSize = count($structure);

                if ($pathSize > 1) {
                    $path = [];
                    for ($i = 1; $i < $pathSize; $i++) {
                        $name = $collection->getItemById((int)$structure[$i])->getName();
                        $path[] = $name !== null ? $this->quoteDelimiter($name) : '';
                    }
                    /** @var string $index */
                    $index = $this->standardizeString(
                        implode('/', $path)
                    );
                    $this->categories[$index] = $category->getId();
                }
            }
        }

        return $this;
    }

    private function quoteDelimiter($string)
    {
        return str_replace('/', '\\/', $string);
    }

    private function standardizeString($string)
    {
        return mb_strtolower($string);
    }
}
