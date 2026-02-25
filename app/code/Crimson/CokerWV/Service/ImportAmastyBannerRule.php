<?php

namespace Crimson\CokerWV\Service;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Api\StoreRepositoryInterface;
use Amasty\PromoBanners\Model\ResourceModel\Rule as RuleResource;
use Amasty\PromoBanners\Model\RuleFactory;

class ImportAmastyBannerRule
{

    const CSV_FILE_PATH = '/import/coker_zip_data_migration/amasty_banner_rule.json';

    public function __construct(
        private readonly DirectoryList            $directoryList,
        private readonly File                     $file,
        private readonly StoreRepositoryInterface $storeRepositoryInterface,
        private readonly RuleResource             $ruleResource,
        private readonly RuleFactory              $ruleFactory
    )
    {
    }


    public function execute(): array
    {
        $result['message'] = "All Amasty Banners Rule imported.";
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
            //getting needed store ids
            $cokerTireStoreId = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_STORE_CODE)->getId();
            $wvStoreId = $this->storeRepositoryInterface->get(WVStoreInterface::WV_STORE_CODE)->getId();
            $mappingStores = [
              '2' => $cokerTireStoreId,
              '8' => $wvStoreId
            ];
            //removing current Coker y WV blocks
            $this->_cleanBeforeImport([$cokerTireStoreId, $wvStoreId]);
        }

        // looping
        foreach ($rows as $key => $row) {
            if (empty($row->id) || empty($row->rule_name)) {
                continue;
            }

            $oStores = explode(',',$row->stores);
            $nStores = [];
            foreach ($oStores as $store){
                $nStores[] = $mappingStores[$store];
            }


            $data = [
                'rule_name' => $row->rule_name,
                'is_active' => $row->is_active,
                'sort_order' => $row->sort_order,
                'from_date' => $row->from_date,
                'to_date' => $row->to_date,
                'banner_position' => $row->banner_position,
                'banner_img' => $row->banner_img,
                'banner_link' => $row->banner_link,
                'banner_title' => $row->banner_title,
                'cms_block' => $row->cms_block,
                'conditions_serialized' => $row->conditions_serialized,
                'show_on_products' => $row->show_on_products,
                'banner_type' => $row->banner_type,
                'html_text' => $row->html_text,
                'actions_serialized' => $row->actions_serialized,
                'stores' => implode(',',$nStores),
                'cust_groups' => '0,2,3,21'
            ];

            try {
                //creating and saving
                $this->ruleFactory->create()
                    ->setData($data)
                    ->save();
            }
            catch (\Exception $e){
                throw $e;
            }
        }

        return $result;
    }

    /**
     * @param array $storeIds
     * @return void
     * @throws LocalizedException
     */
    private function _cleanBeforeImport(array $storeIds): void
    {
        $connection = $this->ruleResource->getConnection();
        $select = $connection->select()
            ->from(['main_table' => $this->ruleResource->getTable('amasty_banner_rule')])
            ->where('stores = ?', implode(',', $storeIds))
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['main_table.id']);
        $data = $connection->fetchAll($select);
        if (!$data) {
            $data = [];
        }

        if (!empty($data)) {
            $connection->beginTransaction();
            $connection->delete(
                $this->ruleResource->getMainTable(),
                $connection->quoteInto('id IN (?)', [$data])
            );
            $connection->commit();
        }
    }
}
