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
use Cokertire\Showpages\Model\ShowpagesFactory;
use Cokertire\Showpages\Model\ResourceModel\Showpages as ShowpagesResource;

class ImportShowpagesData
{

    const CSV_FILE_PATH = '/import/coker_zip_data_migration/coker_showpages.json';

    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly File $file,
        private readonly ShowpagesResource $showpagesResource,
        private readonly ShowpagesFactory $showpagesFactory
    ) {}


    public function execute(): array
    {
        $result['message'] = "All Showpages data imported.";
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
            $this->_removeShowpagesBeforeImport();
        }
        // looping
        foreach ($rows as $key => $row) {
            if (empty($row->showpages_id) || empty($row->show_title) || empty($row->identifier)) {
                continue;
            }

            $data = [
                'show_title_meta'      => $row->show_title_meta,
                'show_desc_meta' => $row->show_desc_meta,
                'show_keywords_meta' => $row->show_keywords_meta,
                'show_title' => $row->show_title,
                'show_space'    => $row->show_space,
                'show_date' => $row->show_date,
                'show_date_end'   => $row->show_date_end,
                'show_venue'     => $row->show_venue,
                'show_coordinates'     => $row->show_coordinates,
                'show_city'     => $row->show_city,
                'show_state'     => $row->show_state,
                'show_skus'     => $row->show_skus,
                'instagram'     => $row->instagram,
                'offer_date_end'     => $row->offer_date_end,
                'thumb_img'     => $row->thumb_img,
                'view_img'     => $row->view_img,
                'identifier'     => $row->identifier,
                'status'     => $row->status,
                'dateofmodification'     => $row->dateofmodification,
                'update_time'     => $row->update_time
            ];

            //creating and saving
            $this->showpagesFactory->create()
                ->setData($data)
                ->save();
        }

        return $result;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function _removeShowpagesBeforeImport(): void
    {
        $connection = $this->showpagesResource->getConnection();
        $select = $connection->select()
            ->from(['main_table' => $this->showpagesResource->getTable('showpages')])
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['main_table.showpages_id'])
        ;
        $data = $connection->fetchAll($select);
        if (!$data) {
            $data = [];
        }

        if (!empty($data)) {
            $connection->beginTransaction();
            $connection->delete(
                $this->showpagesResource->getMainTable(),
                $connection->quoteInto('showpages_id IN (?)', [$data])
            );
            $connection->commit();
        }
    }
}
