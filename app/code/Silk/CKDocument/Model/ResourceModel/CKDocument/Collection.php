<?php
namespace Silk\CKDocument\Model\ResourceModel\CKDocument;

use Silk\CKDocument\Api\Data\CKDocumentInterface;

/**
 * CMS document collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'document_id';

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
    protected $_eventPrefix = 'cms_document_collection';

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
        $this->_init(\Silk\CKDocument\Model\CKDocument::class, \Silk\CKDocument\Model\ResourceModel\CKDocument::class);
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

}
