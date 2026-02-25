<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace I95DevConnect\Returns\Block\Adminhtml\Order\View\Tab;

/**
 * Order RMA Grid
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Rma extends \Magento\Rma\Block\Adminhtml\Order\View\Tab\Rma
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    // @codingStandardsIgnoreLine
    protected $_resourceConn;

    /**
     * @var \Magento\Framework\Registry
     */
    // @codingStandardsIgnoreLine
    protected $_coreRegistry = null;

    /**
     * Rma constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory $collectionFactory
     * @param \Magento\Rma\Model\RmaFactory $rmaFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Rma\Helper\Data $rmaHelper
     * @param \Magento\Framework\App\ResourceConnection $resourceConn
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory $collectionFactory,
        \Magento\Rma\Model\RmaFactory $rmaFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Rma\Helper\Data $rmaHelper,
        \Magento\Framework\App\ResourceConnection $resourceConn,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->rmaHelper = $rmaHelper;
        $this->_resourceConn = $resourceConn;
        parent::__construct(
            $context,
            $backendHelper,
            $collectionFactory,
            $rmaFactory,
            $coreRegistry,
            $rmaHelper,
            $data
        );
    }

    /**
     * Initialize order rma
     *
     * @return void
     * @codingStandardsIgnoreStart
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('order_rma');
        $this->setUseAjax(true);
    }

    /**
     * Configuring and setting collection
     *
     * @return $this
     */
    protected function _beforePrepareCollection()
    {
        $orderId = null;

        if ($this->getOrder() && $this->getOrder()->getId()) {
            $orderId = $this->getOrder()->getId();
        } elseif ($this->getOrderId()) {
            $orderId = $this->getOrderId();
        }
        if ($orderId) {
            /** @var $collection \Magento\Rma\Model\ResourceModel\Rma\Grid\Collection */
            $collection = $this->_collectionFactory->create()->addFieldToFilter('order_id', $orderId);
            $i95devRma = $this->_resourceConn->getTableName('i95dev_magento_rma');
            $collection->getSelect()->joinleft(['i95devRma'=>$i95devRma], 'main_table.entity_id=i95devRma.return_id');
            $this->setCollection($collection);
        }
        return $this;
    }

    /**
     * Prepare grid columns
     *
     * @return \Magento\Rma\Block\Adminhtml\Rma\Grid|void
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->addColumnAfter(
            'target_return_id',
            [
                'header' => __('Target ReturnId'),
                'index' => 'target_return_id',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ],
            'status'
        );

        $this->addColumnAfter(
            'target_receive_id',
            [
                'header' => __('Target ReceiveId'),
                'index' => 'target_receive_id',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ],
            'target_return_id'
        );
        unset($this->_columns['order_increment_id']);
        unset($this->_columns['order_date']);
    }

    /**
     * Get Url to action
     *
     * @param string $action action Url part
     * @return string
     */
    protected function _getControllerUrl($action = '')
    {
        return 'adminhtml/rma/' . $action;
    }

    /**
     * Get Url to action to reload grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/rma/rmaOrder', ['_current' => true]);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Returns');
    }

    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle() // NOSONAR
    {
        return __('Returns');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return $this->rmaHelper->canCreateRma($this->getOrder());
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
