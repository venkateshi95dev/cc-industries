<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Model\ResourceModel\Document;

use Silk\ProductImage\Api\Data\DocumentInterface;

/**
 * CMS document collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'img_document_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'document_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Silk\ProductImage\Model\Document::class, \Silk\ProductImage\Model\ResourceModel\Document::class);
        $this->_map['fields']['document_id'] = 'main_table.document_id';
    }

    /**
     * Set first store flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }



    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {

        return parent::_afterLoad();
    }
    public function addProductFilter($productId)
    {
         $this->getSelect()
            ->joinInner(
                ['link_product' => $this->getTable('img_product')],
                'main_table.entity_id = link_product.document_id',
                []
            )->where('link_product.product_id = ?', $productId);
        return $this;
    }
}
