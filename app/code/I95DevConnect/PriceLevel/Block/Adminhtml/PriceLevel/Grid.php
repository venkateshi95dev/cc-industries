<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Block\Adminhtml\PriceLevel;

use Exception;
use I95DevConnect\PriceLevel\Model\PriceLevelDataFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;

/**
 * Customer Group Grid
 */
class Grid extends Extended
{
    public const HEADER = 'header';
    public const INDEX = 'index';
    public const HEADER_CSS_CLASS = 'header_css_class';
    public const COL_ID = 'col-id';
    public const COLUMN_CSS_CLASS = 'column_css_class';
    public const TYPE = 'type';

    /**
     * @var PriceLevelDataFactory
     */
    public $collectionFactory;

    /**
     * Class constructor to include all the dependencies
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param PriceLevelDataFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        PriceLevelDataFactory $collectionFactory,
        array $data = []
    ) {

        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    // @codingStandardsIgnoreStart
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('pricelevelGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
    }
    // @codingStandardsIgnoreEnd

    /**
     * Prepare grid collection object
     *
     * @return $this
     */
    // @codingStandardsIgnoreStart
    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create()->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
    // @codingStandardsIgnoreEnd

    /**
     * Prepare default grid columns
     *
     * @return $this
     * @throws Exception
     */
    // @codingStandardsIgnoreStart
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $columnsData = [
            [
                self::HEADER => __('ID'),
                self::TYPE => 'number',
                self::INDEX => 'pricelevel_id'
            ],
            [
                self::HEADER => __('Price Level'),
                self::TYPE => 'text',
                self::INDEX => 'pricelevel_code',
            ],
            [
                self::HEADER => __('Price Level Description'),
                self::TYPE => 'text',
                self::INDEX => 'description',
            ]
        ];

        foreach ($columnsData as $columnData) {
            $this->addColumn(
                $columnData[self::INDEX],
                [
                    self::HEADER => __($columnData[self::HEADER]),
                    self::TYPE => $columnData['type'],
                    self::INDEX => $columnData[self::INDEX]
                ]
            );
        }

        return $this;
    }
    // @codingStandardsIgnoreEnd
}
