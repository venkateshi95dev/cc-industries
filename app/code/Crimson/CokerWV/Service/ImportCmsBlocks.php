<?php

namespace Crimson\CokerWV\Service;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Cms\Model\ResourceModel\Block as BlockResource;
use Magento\Cms\Model\BlockFactory;

class ImportCmsBlocks
{

    const CSV_FILE_PATH = '/import/coker_zip_data_migration/csm_blocks.json';

    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly File $file,
        private readonly StoreRepositoryInterface $storeRepositoryInterface,
        private readonly BlockResource $blockResource,
        private readonly BlockFactory $blockFactory,
    ) {}


    public function execute(): array
    {
        $result['message'] = "All CMS Blocks imported.";
        try {
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

            //removing current Coker y WV blocks
            $this->_removeCokerAndWVBlocksBeforeImport([$cokerTireStoreId, $cokerTireDefaultStoreId, $wvStoreId]);
        }

        // looping
        foreach ($rows as $key => $row) {
            if (empty($row->code) || empty($row->title) || empty($row->identifier)) {
                continue;
            }

            $cmsData = [
                'title'      => $row->title,
                'created_in' => $row->created_in,
                'updated_in' => $row->updated_in,
                'identifier' => $row->identifier,
                'content'    => $row->content,
                'creation_time' => $row->creation_time,
                'update_time'   => $row->update_time,
                'is_active'     => $row->is_active
            ];

            if ($row->code == "admin") {
                $cmsData['stores'] = [$cokerTireStoreId, $cokerTireDefaultStoreId, $wvStoreId];
            } else {
                $cmsData['stores'] = [
                    $this->storeRepositoryInterface->get($row->code)->getId()
                ];
            }

            //creating and saving
            $this->blockFactory->create()
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
    private function _removeCokerAndWVBlocksBeforeImport(array $storeIds): void
    {
        $connection = $this->blockResource->getConnection();
        $select = $connection->select()
            ->from(['main_table' => $this->blockResource->getTable('cms_block_store')])
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
                $this->blockResource->getMainTable(),
                $connection->quoteInto('row_id IN (?)', [$data])
            );
            $connection->commit();
        }
    }
}
