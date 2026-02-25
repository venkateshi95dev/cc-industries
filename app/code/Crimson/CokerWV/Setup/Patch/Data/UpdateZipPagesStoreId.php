<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class UpdateZipPagesStoreId implements DataPatchInterface
{

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly PageResource $pageResource,
        private readonly StoreRepositoryInterface $storeRepositoryInterface,
        private readonly PageRepositoryInterface $pageRepository
    ) {}

    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();
        $baseStoreId = $this->storeRepositoryInterface->get(\Crimson\ZipCokerWvConsolidation\Model\Config::ZIP_STORE_CODE)->getId();

        $pageIds = $this->getCMSPageIdsToUpdate();
        if($pageIds){
            foreach ($pageIds as $pageId){
                $page = $this->pageRepository->getById($pageId['identifier']);
                $page->setStores([$baseStoreId]);
                $this->pageResource->save($page);
            }
        }
        $this->moduleDataSetup->endSetup();
    }

    private function getCMSPageIdsToUpdate()
    {
        $connection = $this->pageResource->getConnection();
        $select = $connection->select()
            ->from(['main_table' => $this->pageResource->getTable('cms_page_store')])
            ->where('store_id = ?', 0)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['main_table.row_id'])
        ;
        $data = $connection->fetchAll($select);
        if (!$data) {
            $data = [];
        }

        $result = [];
        if (!empty($data)) {
            $select2 = $connection->select()
                ->from(['main_table' => $this->pageResource->getMainTable()])
                ->where('row_id IN (?)', $data)
                ->reset(\Zend_Db_Select::COLUMNS)
                ->columns(['main_table.identifier'])
            ;
            $result = $connection->fetchAll($select2);
        }
        return $result;
    }
    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [
            CreateCokerWebiste::class,
            CreateWVWebiste::class
        ];
    }
}
