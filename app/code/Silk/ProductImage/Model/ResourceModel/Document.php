<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Silk\ProductImage\Model\ResourceModel;

use Silk\ProductImage\Api\Data\DocumentInterface;
use Silk\ProductImage\Model\Document as CmsDocument;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Cms document mysql resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Document extends AbstractDb
{
    /**
     * Store model
     *
     * @var null|Store
     */
    protected $_store = null;

    /**
     * Catalog products table name
     *
     * @var string
     */
    protected $_documentProductTable;
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;
    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
        $this->_eventManager = $eventManager;
    }

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
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(DocumentInterface::class)->getEntityConnection();
    }
    /**
     * Document product table name getter
     *
     * @return string
     */
    public function getDocumentProductTable()
    {
        if (!$this->_documentProductTable) {
            $this->_documentProductTable = $this->getTable('img_product');
        }
        return $this->_documentProductTable;
    }
    /**
     * Process document data before saving
     *
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $this->_saveImageProducts($object);
        return parent::_beforeSave($object);
    }

    /**
     * Save document products relation
     *
     * @param \Magento\Catalog\Model\Document $document
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _saveImageProducts($document)
    {
        $document->setIsChangedProductList(false);
        $id = $document->getId();
        /**
         * new document-product relationships
         */
        $products = $document->getPostedProducts();

        /**
         * Example re-save document
         */
        if ($products === null) {
            return $this;
        }

        /**
         * old document-product relationships
         */
        $oldProducts = $document->getProductsPosition();

        $insert = array_diff_key($products, $oldProducts);
        $delete = array_diff_key($oldProducts, $products);

        /**
         * Find product ids which are presented in both arrays
         * and saved before (check $oldProducts array)
         */
        $update = array_intersect_key($products, $oldProducts);
        $update = array_diff_assoc($update, $oldProducts);

        $connection = $this->getConnection();

        /**
         * Delete products from document
         */
        if (!empty($delete)) {
            $cond = ['product_id IN(?)' => array_keys($delete), 'document_id=?' => $id];
            $connection->delete($this->getDocumentProductTable(), $cond);
        }

        /**
         * Add products to document
         */
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $productId => $position) {
                $data[] = [
                    'document_id' => (int)$id,
                    'product_id' => (int)$productId,
                    'position' => (int)$position,
                ];
            }
            // echo "<pre>";
            // print_r($data);
            // die;
            $connection->insertMultiple($this->getDocumentProductTable(), $data);
        }

        /**
         * Update product positions in document
         */
        if (!empty($update)) {
            $newPositions = [];
            foreach ($update as $productId => $position) {
                $delta = $position - $oldProducts[$productId];
                if (!isset($newPositions[$delta])) {
                    $newPositions[$delta] = [];
                }
                $newPositions[$delta][] = $productId;
            }

            foreach ($newPositions as $delta => $productIds) {
                $bind = ['position' => new \Zend_Db_Expr("position + ({$delta})")];
                $where = ['document_id = ?' => (int)$id, 'product_id IN (?)' => $productIds];
                $connection->update($this->getDocumentProductTable(), $bind, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $productIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->_eventManager->dispatch(
                'document_change_products',
                ['document' => $document, 'product_ids' => $productIds]
            );

            $document->setChangedProductIds($productIds);
        }

        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $document->setIsChangedProductList(true);

            /**
             * Setting affected products to document for third party engine index refresh
             */
            $productIds = array_keys($insert + $delete + $update);
            $document->setAffectedProductIds($productIds);
        }
        return $this;
    }
    /**
     * Get positions of associated to document products
     *
     * @param \Magento\Catalog\Model\Document $document
     * @return array
     */
    public function getProductsPosition($document)
    {
        $select = $this->getConnection()->select()->from(
            $this->getDocumentProductTable(),
            ['product_id', 'position']
        )->where(
            "{$this->getTable('img_product')}.document_id = ?",
            $document->getId()
        );

        $bind = ['document_id' => (int)$document->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    public function loadByProductId($productId)
    {
        $select = $this->getConnection()->select()->from(
            ['main_table'=>$this->getMainTable()],
            ['*']
        )->joinInner(
            ['link_product' => $this->getDocumentProductTable()],
            'main_table.entity_id = link_product.document_id',
            []
        )->where('link_product.product_id = :product_id');
        $bind = ['product_id' => (int)$productId];
        return $this->getConnection()->fetchAll($select,$bind);
    }
}
