<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Block\Adminhtml\Catalog\Product\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class CustomInfo extends Template
{
    /**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'product/custom_info.phtml';// phpcs:ignore

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var Product
     */
    public $productModel;

    /**
     * CustomInfo constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Product $productModel
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Product $productModel,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->productModel = $productModel;
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
    public function getCustomProductAttribute()
    {

        $id = $this->getProductId();
        $res = $this->productModel->load($id);
        return $res['item_discount_group'];
    }
}
