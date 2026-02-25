<?php

namespace Crimson\CokerWV\Service;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Weee\Model\ResourceModel\Tax as TaxResource;
use Magento\Weee\Model\TaxFactory;

class ImportWeeeTax
{

    const CSV_FILE_PATH = '/import/coker_zip_data_migration/weee_tax.json';

    public function __construct(
        private readonly DirectoryList            $directoryList,
        private readonly File                     $file,
        private readonly \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        private readonly Product $product,
        private readonly TaxResource             $taxResource,
        private readonly TaxFactory              $taxFactory
    )
    {
    }


    public function execute(): array
    {
        $result['message'] = "All Weee tax imported.";
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
            $attributeId = $this->eavAttribute->getIdByCode(
                \Magento\Catalog\Model\Product::ENTITY,
                'tire_tax'
            );
            //clean weee_tax table
            $this->_cleanBeforeImport();
        }

        // looping
        foreach ($rows as $key => $row) {
            if (empty($row->sku)) {
                continue;
            }
            $productId = $this->product->getIdBySku($row->sku);
            if(!$productId){
                continue;
            }
            $data = [
                'website_id' => 0,
                'entity_id' => $productId,
                'country' => $row->country,
                'state' => $row->state,
                'value' => $row->value,
                'attribute_id' => $attributeId
            ];

            try {
                //creating and saving
                $this->taxFactory->create()
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
     * @return void
     * @throws LocalizedException
     */
    private function _cleanBeforeImport(): void
    {
        $connection = $this->taxResource->getConnection();
        $select = $connection->select()
            ->from(['main_table' => $this->taxResource->getTable('weee_tax')])
            ->where('website_id = 0')
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['main_table.value_id']);
        $data = $connection->fetchAll($select);
        if (!$data) {
            $data = [];
        }

        if (!empty($data)) {
            $connection->beginTransaction();
            $connection->delete(
                $this->taxResource->getMainTable(),
                $connection->quoteInto('value_id IN (?)', [$data])
            );
            $connection->commit();
        }
    }
}
