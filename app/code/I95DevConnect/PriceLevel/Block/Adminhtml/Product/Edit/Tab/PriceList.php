<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Block\Adminhtml\Product\Edit\Tab;

use Exception;
use I95DevConnect\PriceLevel\Model\ItemPriceListDataFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Directory\Model\Currency;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

/**
 * Price List Grid for Product in Admin Login
 * @api
 */

class PriceList extends Extended
{
    public const HEADER = 'header';
    public const INDEX = 'index';
    public const HEADER_CSS_CLASS = 'header_css_class';
    public const COL_ID = 'col-id';
    public const COLUMN_CSS_CLASS = 'column_css_class';
    public const PRICELEVEL = 'pricelevel';

    /**
     * @var Registry|null
     */
    public $coreRegistry = null;

    /**
     * @var ProductFactory
     */
    public $productFactory;

    /**
     *
     * @var ItemPriceListDataFactory
     */
    public $priceListFactory;

    /**
     * Class constructor to include all the dependencies
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param ProductFactory $productFactory
     * @param Registry $coreRegistry
     * @param ItemPriceListDataFactory $priceListFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ProductFactory $productFactory,
        Registry $coreRegistry,
        ItemPriceListDataFactory $priceListFactory,
        array $data = []
    ) {

        $this->productFactory = $productFactory;
        $this->coreRegistry = $coreRegistry;
        $this->priceListFactory = $priceListFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        parent::_construct();
        $this->setId('pricelist_product_grid');
        $this->setDefaultSort(self::PRICELEVEL);
        $this->setUseAjax(true);
    }
    // @codingStandardsIgnoreEnd

    /**
     * Retrieve currently edited product model
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('current_product');
    }

    /**
     * Prepare collection
     *
     * @return Extended
     */
    // @codingStandardsIgnoreStart
    protected function _prepareCollection()
    {
        $collection = $this->priceListFactory->create()->getCollection()
                      ->addFieldToFilter('sku', $this->getProduct()->getSku());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    // @codingStandardsIgnoreEnd

    /**
     * Add columns to grid
     *
     * @return $this
     * @throws Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    // @codingStandardsIgnoreStart
    protected function _prepareColumns()
    {
        $this->addColumn(
            self::PRICELEVEL,
            [
            self::HEADER => __('Price Level'),
            'sortable' => true,
            self::INDEX => self::PRICELEVEL,
            self::HEADER_CSS_CLASS => self::COL_ID,
            self::COLUMN_CSS_CLASS => self::COL_ID,
                ]
        );
        $this->addColumn(
            'qty',
            [
            self::HEADER => __('Qty'),
            self::INDEX => 'qty',
            'frame_callback' => [$this, 'formatedQty'],
            self::HEADER_CSS_CLASS => 'col-name',
            self::COLUMN_CSS_CLASS => 'col-name'
                ]
        );

        $this->addColumn(
            'price',
            [
            self::HEADER => __('Price'),
            'type' => 'currency',
            'currency_code' => (string) $this->_scopeConfig->getValue(
                Currency::XML_PATH_CURRENCY_BASE,
                ScopeInterface::SCOPE_WEBSITE
            ),
            self::INDEX => 'price',
            self::HEADER_CSS_CLASS => 'col-type',
            self::COLUMN_CSS_CLASS => 'col-type'
                ]
        );

        return parent::_prepareColumns();
    }
    // @codingStandardsIgnoreEnd

    /**
     * Retrieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('pricelevel/pricelist/grid', ['_current' => true]);
    }

    /**
     * Format quantity display in Grid
     *
     * @param string $value
     * @return string
     */
    public function formatedQty($value)
    {
        return $value . ' ' . __('and above');
    }
}
