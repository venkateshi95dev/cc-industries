<?php

namespace Crimson\CokerWV\Service;

use Amasty\Mostviewed\Model\Backend\Pack\Initialization as PackInitialization;
use Amasty\Mostviewed\Model\Repository\PackRepository;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Cms\Model\ResourceModel\Block as BlockResource;
use Magento\Cms\Model\BlockFactory;
use Cokertire\Showpages\Model\ShowpagesFactory;
use Cokertire\Showpages\Model\ResourceModel\Showpages as ShowpagesResource;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ImportComboPack
{

    const SKU_FILE_PATH = '/import/coker_zip_data_migration/combo_pack/product_sku_map.json';
    const DATA_FILE_PATH = '/import/coker_zip_data_migration/combo_pack/amasty_mostviewed_pack.json';

    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly File $file,
        private readonly Product $product,
        private readonly PackInitialization $packInitialization,
        private readonly PackRepository $packRepository,
        private readonly StoreRepositoryInterface $storeRepositoryInterface
    ) {}


    private function mapProducts()
    {
        $skuPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::SKU_FILE_PATH;
        if (!$this->file->isExists($skuPath)) {
            throw new \Exception("Error with the JSON file.");
        }

        $rows = json_decode($this->file->fileGetContents($skuPath));
        $productMap = [];
        foreach ($rows as $key => $row) {
            if (empty($row->entity_id) || empty($row->sku)) {
                continue;
            }
            $currentId = $this->product->getIdBySku($row->sku);
            if($currentId){
                $productMap[$row->entity_id] = [
                  'sku' => $row->sku,
                  'product_id' => $currentId
                ];
            }
        }
        return $productMap;
    }

    public function execute(): array
    {
        $result['message'] = "All combo pack data imported.";
        $productMap = $this->mapProducts();
        $cokerTireStoreId = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_STORE_CODE)->getId();
        //var_dump($productMap);die;
        try {
            $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::DATA_FILE_PATH;
            if (!$this->file->isExists($csvPath)) {
                throw new \Exception("Error with the JSON file.");
            }

            $rows = json_decode($this->file->fileGetContents($csvPath));
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        // looping
        foreach ($rows as $key => $row) {
            $productInfo = json_decode($row->products_info,true);
            $childProducts = [];
            foreach ($productInfo as $id=>$product_data){
                $newData = $productMap[$id];
                $childProducts[] = [
                    'entity_id' => $newData['product_id'],
                    'quantity' => $product_data['quantity'],
                    'discount_amount' => $product_data['discount_amount'],
                    'position' => null
                ];
            }
            $parentData = null;
            if(!empty($row->product_id)){
                $parentId = $productMap[$row->product_id]['product_id'];
                $parentData = [0 => ['entity_id' => $parentId]];
            }
            $data = [
                'pack_id' => '',
                'name' => $row->name,
                'priority' => $row->priority,
                'block_title' => $row->block_title,
                'discount_amount' => $row->discount_amount,
                'date_from' => $row->date_from,
                'date_to' => $row->date_to,
                'apply_condition' => $row->apply_condition,
                'stores' =>
                    array (
                        0 => $cokerTireStoreId,
                    ),
                'customer_group_ids' =>
                    array (
                        0 => '32000',
                    ),
                'status' => $row->status,
                'apply_for_parent' => $row->apply_for_parent,
                'discount_type' => $row->discount_type,
                'cart_message' => $row->cart_message,
                'product_ids' =>
                    array (
                        'child_products_container' => $childProducts
                    ),
                'parent_products_container' => $parentData
            ];
            try {
                $model = $this->packInitialization->execute(0, $data);
                $this->packRepository->save($model);
            }
            catch (\Exception $exception)
            {
                echo $exception->getMessage();
            }
        }
        return $result;
    }
}
