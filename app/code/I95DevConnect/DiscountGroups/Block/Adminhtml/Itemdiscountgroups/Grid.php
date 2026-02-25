<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Block\Adminhtml\Itemdiscountgroups;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use I95DevConnect\DiscountGroups\Model\ItemdiscountgroupFactory;

class Grid extends Extended
{
    /**
     * @var ItemdiscountgroupFactory
     */
    protected $collectionFactory;

    /**
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param ItemdiscountgroupFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ItemdiscountgroupFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct() // phpcs:ignore
    {
        parent::_construct();
        $this->setId('i95DevItemDiscountGroupGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
    }

    /**
     * Prepare grid collection object
     *
     * @return $this
     */
    protected function _prepareCollection() // phpcs:ignore
    {
        $collection = $this->collectionFactory->create()->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare default grid column
     *
     * @return $this
     */
    protected function _prepareColumns() // phpcs:ignore
    {
        parent::_prepareColumns();

        $this->addColumn(
            'id',
            [
            'header' => __('ID'),
            'type' => 'number',
            'index' => 'id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
                ]
        );

        $this->addColumn(
            'idg_code',
            [
            'header' => __('Code'),
            'type' => 'text',
            'index' => 'idg_code',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
                ]
        );

        $this->addColumn(
            'idg_description',
            [
            'header' => __('Description'),
            'type' => 'text',
            'index' => 'idg_description',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
                ]
        );
        return $this;
    }
}
