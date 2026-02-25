<?php

namespace Crimson\CokerWV\Service;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Theme\Model\ResourceModel\Theme;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite;

class ImportCmsPages
{

    const CSV_FILE_PATH = '/import/coker_zip_data_migration/csm_pages.json';

    protected array $_themes = [];

    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly File $file,
        private readonly Theme $themeResourceModel,
        private readonly StoreRepositoryInterface $storeRepositoryInterface,
        private readonly PageResource $pageResource,
        private readonly PageFactory $pageFactory,
        private readonly UrlRewrite $urlRewrite,

    ) {
        $this->_themes = $this->_getAllThemes();
    }


    public function execute(): array
    {
        $result['message'] = "All CMS Pages imported.";
        try {
            if (empty($this->_themes)) {
                throw new \Exception("No themes registered.");
            }

            $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH;
            if (!$this->file->isExists($csvPath)) {
                throw new \Exception("Error with the JSON file.");
            }

            $rows = json_decode($this->file->fileGetContents($csvPath));
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        if (!empty($rows)) {
            //getting needed store ids
            $cokerTireStoreId        = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_STORE_CODE)->getId();
            $cokerTireDefaultStoreId = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_DEFAULT_STORE_CODE)->getId();
            $wvStoreId               = $this->storeRepositoryInterface->get(WVStoreInterface::WV_STORE_CODE)->getId();

            //removing current Coker y WV pages
            $this->_removeCokerAndWVPagesBeforeImport([$cokerTireStoreId, $cokerTireDefaultStoreId, $wvStoreId]);

            //removing Url rewrites for CMS Pages for these stores
            $this->_removeCokerAndWVPagesYrlRewritesBeforeImport([$cokerTireStoreId, $cokerTireDefaultStoreId, $wvStoreId]);
        }

        // looping
        foreach ($rows as $key => $row) {
            if (empty($row->code) || empty($row->title) || empty($row->identifier)) {
                continue;
            }

            $themeId = '';
            if (!empty($row->custom_theme) && !empty($row->theme_code)) {
                $themeId = $this->_getThemeIdOnZIP($row->theme_code);
            }

            $cmsData = [
                'title'      => $row->title,
                'created_in' => $row->created_in,
                'updated_in' => $row->updated_in,
                'page_layout' => $row->page_layout,
                'meta_keywords' => $row->meta_keywords,
                'meta_description' => $row->meta_description,
                'identifier' => $row->identifier,
                'content_heading' => $row->content_heading,
                'content' => $row->content,
                'creation_time' => $row->creation_time,
                'update_time' => $row->update_time,
                'is_active' => $row->is_active,
                'sort_order' => $row->sort_order,
                'layout_update_xml' => $row->layout_update_xml,
                'custom_theme' => $themeId,
                'custom_root_template' => $row->custom_root_template,
                'custom_layout_update_xml' => $row->custom_layout_update_xml,
                'custom_theme_from' => $row->custom_theme_from,
                'custom_theme_to' => $row->custom_theme_to,
                'meta_title' => $row->meta_title,
                'website_root' => $row->website_root,
                'amasty_hreflang_uuid' => $row->amasty_hreflang_uuid,
                'layout_update_selected' => $row->layout_update_selected,
                'robots' => $row->robots,
                'canonical' => $row->canonical,
            ];

            if ($row->code == "admin") {
                $cmsData['stores'] = [$cokerTireStoreId, $cokerTireDefaultStoreId, $wvStoreId];
            } else {
                $cmsData['stores'] = [
                    $this->storeRepositoryInterface->get($row->code)->getId()
                ];
            }

            //creating and saving
            $this->pageFactory->create()
                ->setData($cmsData)
                ->save();
        }

        return $result;
    }

    /**
     * @param array $storeIds
     * @return void
     * @throws LocalizedException
     */
    private function _removeCokerAndWVPagesYrlRewritesBeforeImport(array $storeIds): void
    {
        $condition = [
            'store_id IN (?)' => $storeIds,
            'entity_type = ?' => 'cms-page',
        ];
        $connection = $this->urlRewrite->getConnection();
        $connection->beginTransaction();
        $connection->delete(
            $this->urlRewrite->getMainTable(),
            $condition
        );
        $connection->commit();
    }

    /**
     * @param array $storeIds
     * @return void
     * @throws LocalizedException
     */
    private function _removeCokerAndWVPagesBeforeImport(array $storeIds): void
    {
        $connection = $this->pageResource->getConnection();
        $select = $connection->select()
            ->from(['main_table' => $this->pageResource->getTable('cms_page_store')])
            ->where('store_id IN (?)', $storeIds)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['main_table.row_id'])
        ;
        $data = $connection->fetchAll($select);
        if (!$data) {
            $data = [];
        }

        if (!empty($data)) {
            $connection->beginTransaction();
            $connection->delete(
                $this->pageResource->getMainTable(),
                $connection->quoteInto('row_id IN (?)', [$data])
            );
            $connection->commit();
        }
    }

    /**
     * @param $cokerWVThemeCode
     * @return int|null
     */
    private function _getThemeIdOnZIP($cokerWVThemeCode): ?int
    {
        if (!$cokerWVThemeCode) {
            return null;
        }

        $found = array_filter($this->_themes,function($v,$k) use ($cokerWVThemeCode){
            return $v['code'] == $cokerWVThemeCode;
        },ARRAY_FILTER_USE_BOTH);
        $found = array_values($found);

        return !empty($found[0]['theme_id']) ? (int) $found[0]['theme_id'] : null;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function _getAllThemes(): array
    {
        $select = $this->themeResourceModel->getConnection()->select()
            ->from(['main_table' => $this->themeResourceModel->getMainTable()]);
        $data = $this->themeResourceModel->getConnection()->fetchAll($select);
        if (!$data) {
            $data = [];
        }

        return $data;
    }
}
