<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace I95DevConnect\Returns\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Order RMA Grid
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Rma extends \Magento\Rma\Block\Adminhtml\Customer\Edit\Tab\Rma
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
    protected $_coreRegistry;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Rma constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory $collectionFactory
     * @param \Magento\Rma\Model\RmaFactory $rmaFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\App\ResourceConnection $resourceConn
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory $collectionFactory,
        \Magento\Rma\Model\RmaFactory $rmaFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\ResourceConnection $resourceConn,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->customerRepository = $customerRepository;
        $this->_resourceConn = $resourceConn;
        parent::__construct(
            $context,
            $backendHelper,
            $collectionFactory,
            $rmaFactory,
            $coreRegistry,
            $customerRepository,
            $data
        );
    }

    /**
     * Initialize customer edit tab rma
     *
     * @return void
     * @codingStandardsIgnoreStart
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('customer_edit_tab_rma');
        $this->setUseAjax(true);
    }

    /**
     * Prepare massaction
     *
     * @return \Magento\Rma\Block\Adminhtml\Customer\Edit\Tab\Rma
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Configuring and setting collection
     *
     * @return $this
     */
    protected function _beforePrepareCollection()
    {
        $customerId = null;
        $customer = $this->customerRepository->getById(
            $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        );
        if ($customer && $customer->getId()) {
            $customerId = $customer->getId();
        } elseif ($this->getCustomerId()) {
            $customerId = $this->getCustomerId();
        }
        if ($customerId) {
            /** @var $collection \Magento\Rma\Model\ResourceModel\Rma\Grid\Collection */
            $collection = $this->_collectionFactory->create()->addFieldToFilter('customer_id', $customerId);
            $i95devRma = $this->_resourceConn->getTableName('i95dev_magento_rma');
            $collection->getSelect()->joinleft(['i95devRma'=>$i95devRma], 'main_table.entity_id=i95devRma.return_id');
            $this->setCollection($collection);
        }
        return $this;
    }

    /**
     * Prepare grid columns
     *
     * @return \Magento\Rma\Block\Adminhtml\Rma\Grid
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
    }

    /**
     * Get Url to action
     *
     * @param  string $action action Url part
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
        return $this->getUrl('adminhtml/rma/rmaCustomer', ['_current' => true]);
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
     * Check if can show tab
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        return (bool)$customerId;
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

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl() // NOSONAR
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded() // NOSONAR
    {
        return false;
    }
}
