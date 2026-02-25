<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Block\Adminhtml\Product;

/**
 * @api
 * @since 100.1.0
 */
class Merchandiser extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     * @since 100.1.0
     */
    protected $_coreRegistry;
    /**
     * @var \Silk\ProductImage\Model\Position\Cache
     * @since 100.1.0
     */
    protected $_positionCache;
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Silk\ProductImage\Model\Position\Cache $cache
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Silk\ProductImage\Model\Position\Cache $cache,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_positionCache = $cache;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     * @since 100.1.0
     */
    public function getDialogUrl()
    {
        return $this->getUrl(
            'document/index/addproduct',
            [
                'cache_key' => $this->getPositionCacheKey(),
                'componentJson' => true
            ]
        );
    }
    /**
     * @return string
     * @since 100.1.0
     */
    public function getSavePositionsUrl()
    {
        return $this->getUrl('document/position/save');
    }

    /**
     * Get products positions url
     *
     * @return string
     * @since 100.1.0
     */
    public function getProductsPositionsUrl()
    {
        return $this->getUrl('document/position/get');
    }
    /**
     * @return string
     * @since 100.1.0
     */
    public function getPositionCacheKey()
    {
        return $this->_coreRegistry->registry($this->getPositionCacheKeyName());
    }
    /**
     * @return mixed
     * @since 100.1.0
     */
    public function getDocumentId()
    {
        return $this->getRequest()->getParam('document_id');
    }

        /**
     * @return string
     * @since 100.1.0
     */
    public function getPositionCacheKeyName()
    {
        return \Silk\ProductImage\Model\Position\Cache::POSITION_CACHE_KEY;
    }

    /**
     * @return string
     * @since 100.1.0
     */
    public function getPositionDataJson()
    {
        return \Zend_Json::encode($this->_positionCache->getPositions($this->getPositionCacheKey()));
    }
}
