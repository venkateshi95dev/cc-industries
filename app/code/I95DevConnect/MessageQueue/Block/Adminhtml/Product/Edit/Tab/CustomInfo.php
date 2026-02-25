<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Block\Adminhtml\Product\Edit\Tab;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;

/**
 * Block for displaying target information in product
 * @api
 */
class CustomInfo extends Template
{
    /**
     * @var string
     */
    public $_template = 'I95DevConnect_MessageQueue::product/tab/edit/custom_info.phtml';// phpcs:ignore

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var Product
     */
    public $productModel;

    /**
     * @var Data
     */
    public $helperData;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Product $productModel
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Product $productModel,
        Data $helperData,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->productModel = $productModel;
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve product id
     *
     * @return string|null
     */
    public function getProductId()
    {
        $product = $this->coreRegistry->registry('current_product');
        return $product->getId();
    }

    /**
     * Get product custom attribute
     *
     * @return string
     */
    public function getCustomAttribute()
    {
        $targetProductStatus = null;
        if ($this->helperData->isEnabled()) {
            $id = $this->getProductId();
            $res = $this->productModel->load($id);
            $targetProductStatus = $res['targetproductstatus'];
        }
        return $targetProductStatus;
    }
}
