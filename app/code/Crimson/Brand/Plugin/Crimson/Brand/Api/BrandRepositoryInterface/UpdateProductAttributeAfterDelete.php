<?php

namespace Crimson\Brand\Plugin\Crimson\Brand\Api\BrandRepositoryInterface;

use Crimson\Brand\Api\BrandRepositoryInterface;
use Crimson\Brand\Api\Data\BrandInterface;

class UpdateProductAttributeAfterDelete
{
    /** @var \Magento\Catalog\Model\Product\Action */
    private $productAction;

    /** @var \Magento\Catalog\Model\ResourceModel\Product */
    private $productResource;

    public function __construct(
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Catalog\Model\ResourceModel\Product $productResource
    ) {
        $this->productAction = $productAction;
        $this->productResource = $productResource;
    }

    public function afterDelete(BrandRepositoryInterface $subject, $result, BrandInterface $brand)
    {
        $productIdsByStore = $this->getBrandProductIdsByStore($brand->getId());
        foreach ($productIdsByStore as $storeId => $productIds) {
            $this->productAction->updateAttributes($productIds, ['brands' => null], $storeId);
        }

        return $result;
    }

    public function getBrandProductIdsByStore(int $brandId): array
    {
        $brandAttribute = $this->productResource->getAttribute('brands');
        
        $connection = $this->productResource->getConnection();
        $select = $connection->select();
        $select->from(
            ['brands_attr' => $brandAttribute->getBackendTable()],
            ['store_id']
        );
        $select->joinInner(
            ['product' => $this->productResource->getEntityTable()],
            'product.' . $this->productResource->getLinkField() . ' = brands_attr.row_id',
            ['entity_id']
        );
        $select->where('brands_attr.attribute_id = ?', $brandAttribute->getAttributeId());
        $select->where('brands_attr.value = ?', $brandId);
        $results = $connection->fetchAll($select);

        $productIdsByStore = [];
        foreach ($results as $result) {
            if (!isset($productIdsByStore[$result['store_id']])) {
                $productIdsByStore[$result['store_id']] = [];
            }
            $productIdsByStore[$result['store_id']][] = $result['entity_id'];
        }
        return $productIdsByStore;
    }
}