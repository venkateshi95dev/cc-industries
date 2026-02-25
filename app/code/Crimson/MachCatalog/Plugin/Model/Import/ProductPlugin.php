<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 4:00 PM
 * @brief       Products imported need to be run through mach update checks
 */

namespace Crimson\MachCatalog\Plugin\Model\Import;

use Crimson\MachCatalog\Model\Service\UpdateProducts;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;

/**
 * Class ProductPlugin
 * @package Crimson\MachCatalog\Plugin\Model\Import
 */
class ProductPlugin
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;
    /**
     * @var Action
     */
    protected $productAction;
    /**
     * @var string
     */
    protected $productEntityLinkField;
    /**
     * @var Product
     */
    protected $productResource;

    /**
     * ProductPlugin constructor.
     *
     * @param Action         $productAction
     * @param Product  $productResource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        Action $productAction,
        Product $productResource,
        MetadataPool $metadataPool
    ) {
        $this->productAction   = $productAction;
        $this->productResource = $productResource;
        $this->metadataPool    = $metadataPool;
    }

    /**
     * @param \Magento\CatalogImportExport\Model\Import\Product $subject
     * @param callable                                          $proceed
     * @param array                                             $entityRowsIn
     * @param array                                             $entityRowsUp
     *
     * @return mixed
     * @throws \Exception
     */
    public function aroundSaveProductEntity(
        \Magento\CatalogImportExport\Model\Import\Product $subject,
        callable $proceed,
        array $entityRowsIn,
        array $entityRowsUp
    ) {
        $result = $proceed($entityRowsIn, $entityRowsUp);

        $entityIds = $this->_extractInsertEntityIds($entityRowsIn);
        $entityIds = array_merge($entityIds, $this->_extractUpdateEntityIds($entityRowsUp));
        $entityIds = array_unique($entityIds);

        $this->_updateEntityIds($entityIds);

        return $result;
    }

    /**
     * @param array $entityRowsIn
     *
     * @return int[]
     */
    protected function _extractInsertEntityIds(array $entityRowsIn): array
    {
        if (empty($entityRowsIn)) {
            return [];
        }

        $skus = array_keys($entityRowsIn);

        return $this->productResource->getProductsIdsBySkus($skus);
    }

    /**
     * @param array $entityRowsUp
     *
     * @return array
     * @throws \Exception
     */
    protected function _extractUpdateEntityIds(array $entityRowsUp): array
    {
        if (empty($entityRowsUp)) {
            return [];
        }

        $entityLinkField = $this->_getEntityLinkField();

        $entityLinkIds = [];
        foreach ($entityRowsUp as $row) {
            if (!isset($row[$entityLinkField])) {
                continue;
            }

            $entityLinkIds = (int)$row[$entityLinkField];
        }

        //if we already have entity ids, we don't need to query them.
        if ($entityLinkField === 'entity_id') {
            return $entityLinkIds;
        }

        $connection = $this->productResource->getConnection();

        $select = $connection->select()
            ->from($this->productResource->getEntityTable(), ['entity_id'])
            ->where($entityLinkField . ' IN (?)', $entityLinkIds);

        $entityIds = $connection->fetchCol($select);

        if (!$entityIds) {
            return [];
        }

        return array_map('intval', $entityIds);
    }

    protected function _updateEntityIds(array $entityIds)
    {
        if (empty($entityIds)) {
            return;
        }

        $this->productAction->updateAttributes($entityIds, [
            UpdateProducts::ATTRIBUTE_UPDATED_FROM_MACH => 1,
        ], Store::DEFAULT_STORE_ID);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function _getEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->metadataPool
                ->getMetadata(ProductInterface::class)
                ->getLinkField();
        }

        return $this->productEntityLinkField;
    }
}
