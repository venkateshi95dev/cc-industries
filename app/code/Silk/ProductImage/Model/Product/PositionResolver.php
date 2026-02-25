<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Silk\ProductImage\Model\Product;

/**
 * Resolver to get product positions by ids assigned to specific document
 */
class PositionResolver extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('img_document', 'entity_id');
    }

    /**
     * Get document product positions
     *
     * @param int $documentId
     * @return array
     */
    public function getPositions(int $documentId): array
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['cpe' => $this->getTable('catalog_product_entity')],
            'entity_id'
        )->joinLeft(
            ['ccp' => $this->getTable('img_product')],
            'ccp.product_id=cpe.entity_id'
        )->where(
            'ccp.document_id = ?',
            $documentId
        )->order(
            'ccp.position ' . \Magento\Framework\DB\Select::SQL_ASC
        )->order(
            'ccp.product_id ' . \Magento\Framework\DB\Select::SQL_DESC
        );

        return array_flip($connection->fetchCol($select));
    }
}
