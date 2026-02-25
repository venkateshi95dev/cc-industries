<?php

namespace Crimson\Category\Service;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Framework\UrlInterface;
use Magento\Ui\Model\Export\ConvertToCsv;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList as ReadDirectoryList;

class GenerationCategoriesReport extends ConvertToCsv
{

    CONST CSV_FILE_NAME = "generation_by_categories_report.csv";
    CONST CATEGORIES_EXCLUDED_FROM_PATH = [1,2];
    CONST GENERATION_CATEGORIES = [
        "53-62 C1 Corvette Parts",
        "63-67 C2 Corvette Parts",
        "68-82 C3 Corvette Parts",
        "84-96 C4 Corvette Parts",
        "97-04 C5 Corvette Parts",
        "05-13 C6 Corvette Parts",
        "14-19 C7 Corvette Parts",
        "20-23 C8 Corvette Parts"
    ];

    protected array $_categoryData = [];

    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        protected CollectionFactory $collectionFactory,
        protected Category $categoryResource,
        protected ReadDirectoryList $readDirectoryList,
        protected CategoryRepositoryInterface $categoryRepositoryInterface,
        protected CategoryListInterface $categoryListInterface,
        protected SearchCriteriaBuilder $searchCriteriaBuilder,
        protected UrlInterface $urlInterface,
        $pageSize = 200
    )
    {
        parent::__construct($filesystem, $filter, $metadataProvider, $pageSize);
    }

    /**
     * @return array
     * @throws FileSystemException
     */
    public function generateCsvFile(): array
    {
        $file = 'export/' . $this->getReportFileName();
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->_getFileHeaders());

        //getting report data
        if (empty($this->_categoryData)) {
            $this->_categoryData = $this->_getReportData();
        }

        foreach ($this->_categoryData as $record) {
            $row = $this->_mapColumns($record);
            if (!$row) {
                continue;
            }

            //writing the category data row
            $stream->writeCsv($row);
        }

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
    }

    /**
     * @param array $record
     * @return array
     */
    protected function _mapColumns(array $record): array
    {
        try {
            $result = [];
            $result["structure"] = $this->_getCategoryPathNamesByPath($record['path']);
            $result["url path"]  = $this->urlInterface->getBaseUrl() . $record['request_path'];
            $result["name"]      = (string) $record['name'];

            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @param $idsPath
     * @return string
     */
    protected function _getCategoryPathNamesByPath($idsPath): string
    {
        if (empty($idsPath) || empty($this->_categoryData)) {
            return '';
        }

        $namePath = '';
        foreach (explode('/', $idsPath) as $id) {
            if (in_array($id, self::CATEGORIES_EXCLUDED_FROM_PATH)) {
                continue;
            }

            $output = array_search($id, array_column($this->_categoryData, 'entity_id'));
            if ($output === false) {
                $namePath .= '___>';
            } else {
                $namePath .= $this->_categoryData[$output]['name'] . '>';
            }
        }

        return rtrim($namePath, ">");
    }

    /**
     * @return array
     */
    protected function _getReportData(): array
    {
        $categoryIds = [];
        $this->searchCriteriaBuilder->addFilter('name', self::GENERATION_CATEGORIES, 'in');
        $categoriesSearchResult = $this->categoryListInterface->getList($this->searchCriteriaBuilder->create());
        foreach ($categoriesSearchResult->getItems() as $category) {
            $categoryIds = array_merge($categoryIds, $this->categoryResource->getAllChildren($category));
        }

        if (!$categoryIds) {
            return [];
        }

        $collection = $this->collectionFactory->create()
            ->joinUrlRewrite()
            ->addIdFilter($categoryIds);
        $connection = $this->categoryResource->getConnection();
        $select = $collection->getSelect()
            ->joinInner(
                ['c_c_e_v_name' => $this->categoryResource->getTable('catalog_category_entity_varchar')],
                'e.row_id = c_c_e_v_name.row_id AND c_c_e_v_name.attribute_id = 45');
        $select
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'entity_id'    => 'e.entity_id',
                'name'         => 'c_c_e_v_name.value',
                'request_path' => 'url_rewrite.request_path',
                'path'         => 'e.path',
            ])->order('e.path ASC');

        $result = $connection->fetchAll($select);
        if (!$result) {
            $result = [];
        }

        return $result;
    }


    /**
     * @return array
     */
    protected function _getFileHeaders(): array
    {
        return [
            "structure",
            "url path",
            "names",
        ];
    }

    /**
     * @return string
     */
    public function getReportFileName(): string
    {
        return self::CSV_FILE_NAME;
    }
}
