<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Block\Adminhtml\Discountcalculation;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use I95DevConnect\DiscountGroups\Model\DiscountcalculationFactory;
use I95DevConnect\DiscountGroups\Helper\Data as DiscountGroupsHelper;

class Grid extends Extended
{
    /**
     * @var DiscountcalculationFactory
     */
    protected $collectionFactory;

    /**
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param DiscountcalculationFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        DiscountcalculationFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct() // phpcs:ignore
    {
        parent::_construct();
        $this->setId('i95DevDiscountCalculationGrid');
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
            'sales_type',
            [
            'header' => __('Sales Type'),
            'type' => 'options',
            'index' => 'sales_type',
            'frame_callback' => [$this, 'getSalesType'],
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
            'options' => [
                DiscountGroupsHelper::CUSTOMER => "Customer",
                DiscountGroupsHelper::CUSTOMERDISCOUNTGROUP => "Customer Discount Group",
                DiscountGroupsHelper::ALLCUSTOMERS => "All Customers",
                DiscountGroupsHelper::CAMPAIGN => "Campaign"
            ]
                ]
        );

        $this->addColumn(
            'sales_code',
            [
            'header' => __('Sales Code'),
            'type' => 'text',
            'index' => 'sales_code',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
                ]
        );

        $this->addColumn(
            'type',
            [
            'header' => __('Type'),
            'type' => 'options',
            'index' => 'type',
            'frame_callback' => [$this, 'getType'],
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
            'options' => [
                DiscountGroupsHelper::ITEM => "Item",
                DiscountGroupsHelper::ITEMDISCOUNTGROUP => "Item Discount Group"
            ]
                ]
        );

        $this->addColumn(
            'code',
            [
            'header' => __('Code'),
            'type' => 'text',
            'index' => 'code',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
                ]
        );

        $this->addColumn(
            'qty',
            [
            'header' => __('Quantity'),
            'type' => 'text',
            'index' => 'qty',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
                ]
        );

        $this->addColumn(
            'price',
            [
            'header' => __('Discount (%)'),
            'type' => 'text',
            'index' => 'price',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
                ]
        );

        $this->addColumn(
            'start_dt',
            [
            'header' => __('Start Date'),
            'type' => 'date',
            'index' => 'start_dt',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
                ]
        );

        $this->addColumn(
            'end_dt',
            [
            'header' => __('End Date'),
            'type' => 'date',
            'index' => 'end_dt',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
                ]
        );
        return $this;
    }

    /**
     * Get Sales type
     *
     * @param string $value
     * @param object $row
     * @return string
     */
    public function getSalesType($value, $row)
    {
        return '<span class="status_' . $row->getID() . '" >' . $value . "</span>";
    }

    /**
     * Get type
     *
     * @param string $value
     * @param object $row
     * @return string
     */
    public function getType($value, $row) //NOSONAR
    {
        return '<span class="status_' . $row->getID() . '" >' . $value . "</span>";
    }
}
