<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace I95DevConnect\Returns\Block\Adminhtml\Rma;

class Grid extends \Magento\Rma\Block\Adminhtml\Rma\Grid
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    // @codingStandardsIgnoreLine
    protected $_resourceConn;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory $collectionFactory
     * @param \Magento\Rma\Model\RmaFactory $rmaFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConn
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory $collectionFactory,
        \Magento\Rma\Model\RmaFactory $rmaFactory,
        \Magento\Framework\App\ResourceConnection $resourceConn,
        array $data = []
    ) {
        $this->_resourceConn = $resourceConn;
        parent::__construct(
            $context,
            $backendHelper,
            $collectionFactory,
            $rmaFactory,
            $data
        );
    }

    /**
     * Prepare related item collection
     *
     * @return \Magento\Rma\Block\Adminhtml\Rma\Grid
     * @codingStandardsIgnoreStart
     */
    protected function _prepareCollection()
    {
        $this->_beforePrepareCollection();
        return parent::_prepareCollection();
    }

    /**
     * Configuring and setting collection
     *
     * @return $this
     */
    protected function _beforePrepareCollection()
    {
        if (!$this->getCollection()) {
            /** @var $collection \Magento\Rma\Model\ResourceModel\Rma\Grid\Collection */
            $collection = $this->_collectionFactory->create();
            $i95devRma = $this->_resourceConn->getTableName('i95dev_magento_rma');
            $collection->getSelect()->joinleft(['i95devRma' => $i95devRma], 'main_table.entity_id=i95devRma.return_id');
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

        return $this;
    }

    /**
     * Prepare massaction
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_ids');

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Close'),
                'url' => $this->getUrl($this->_getControllerUrl('close')),
                'confirm' => __(
                    'You have chosen to change status(es) of the selected RMA requests to Close.'
                    . ' Are you sure you want to continue?'
                )
            ]
        );

        return $this;
    }

    /**
     * Get Url to action
     *
     * @param  string $action action Url part
     * @return string
     */
    protected function _getControllerUrl($action = '')
    {
        return '*/*/' . $action;
    }
    // @codingStandardsIgnoreEnd

    /**
     * Retrieve row url
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl($this->_getControllerUrl('edit'), ['id' => $row->getId()]);
    }
}
