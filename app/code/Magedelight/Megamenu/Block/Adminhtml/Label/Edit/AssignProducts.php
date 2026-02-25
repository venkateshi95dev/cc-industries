<?php
 /**
 * @package Magedelight_SubscribenowPro for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
namespace Magedelight\Megamenu\Block\Adminhtml\Label\Edit;

class AssignProducts extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'label/products/assign_products.phtml';

    /**
     * @var \Magedelight\Megamenu\Block\Adminhtml\Label\Edit\Product
     */
    private $blockGrid;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * AssignProducts constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {

        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \Magedelight\Megamenu\Block\Adminhtml\Label\Edit\Product::class,
                'product.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return string
     */
    public function getProductsJson()
    {
        $assignedProducts = $this->getProductAssignedModel()->getProductAssign();
        if (isset($assignedProducts) && $assignedProducts != '') {
            return $assignedProducts;
        }
        return '{}';
    }

    public function getProductAssignedModel()
    {
        return $this->registry->registry('magedelight_megamenu_label');
    }
}
