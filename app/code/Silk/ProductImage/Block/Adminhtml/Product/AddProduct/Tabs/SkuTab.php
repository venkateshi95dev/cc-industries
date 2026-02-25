<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Silk\ProductImage\Block\Adminhtml\Product\AddProduct\Tabs;

/**
 * @api
 * @since 100.0.2
 */
class SkuTab extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getMassAssignUrl()
    {
        return $this->getUrl(
            'document/products/massassign',
            [
                'document_id' => $this->getRequest()->getParam('id'),
                'componentJson' => true
            ]
        );
    }
}
