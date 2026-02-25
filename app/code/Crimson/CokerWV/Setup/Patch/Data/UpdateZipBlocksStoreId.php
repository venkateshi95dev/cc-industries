<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\ResourceModel\Block as BlockResource;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class UpdateZipBlocksStoreId implements DataPatchInterface
{

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly BlockResource $blockResource,
        private readonly StoreRepositoryInterface $storeRepositoryInterface,
        private readonly BlockRepositoryInterface $blockRepository
    ) {}

    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();
        $baseStoreId = $this->storeRepositoryInterface->get(\Crimson\ZipCokerWvConsolidation\Model\Config::ZIP_STORE_CODE)->getId();

        $blockIds = $this->getCMSBlockIdsToUpdate();
        if($blockIds){
            foreach ($blockIds as $blockId){
                $block = $this->blockRepository->getById($blockId['identifier']);
                $block->setStores([$baseStoreId]);
                $this->blockRepository->save($block);
            }
        }
        $this->moduleDataSetup->endSetup();
    }

    private function getCMSBlockIdsToUpdate()
    {
        $connection = $this->blockResource->getConnection();
        $select = $connection->select()
            ->from(['main_table' => $this->blockResource->getTable('cms_block_store')])
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
                ->from(['main_table' => $this->blockResource->getMainTable()])
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
