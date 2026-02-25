<?php
 /**
 * @package Magedelight_SubscribenowPro for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Block\Adminhtml\Label\Edit;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as SetCollectionFactory;
use Magedelight\Megamenu\Api\LabelRepositoryInterface;
use Magento\Framework\Json\DecoderInterface;

class Product extends Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    private $type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    private $status;

    /**
     * @var ProductVisibility
     */
    private $visibility;

    /**
     * @var SetCollectionFactory
     */
    private $setCollectionFactory;

    /**
     * @inheritDoc
     */
    protected $labelRepository;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        ProductType $type,
        ProductStatus $status,
        ProductVisibility $visibility,
        SetCollectionFactory $setsFactory,
        \Magento\Framework\Registry $coreRegistry,
        LabelRepositoryInterface $labelRepository,
        DecoderInterface $jsonDecoder,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->type = $type;
        $this->status = $status;
        $this->visibility = $visibility;
        $this->setCollectionFactory = $setsFactory;
        $this->coreRegistry = $coreRegistry;
        $this->jsonDecoder = $jsonDecoder;
        $this->labelRepository = $labelRepository;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('catalog_category_products');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    /**
     * @return array|null
     */
    public function getProductAssignedModel()
    {
        return $this->coreRegistry->registry('magedelight_megamenu_label');
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_category') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare product collection to be displayed in the grid.
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        if ($this->_getSelectedProducts()) {
            $this->setDefaultFilter(['in_category' => 1]);
        }
        $collection = $this->getProductCollection();
        $productIds = $this->getToBeAssignedProducts();

        /*$collection->addFieldToFilter([
            ['attribute' => 'is_subscription', 'eq' => 1],
        ]);*/

        if (count($productIds)) {
            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'in_category',
            [
                'type' => 'checkbox',
                'name' => 'in_category',
                'values' => $this->_getSelectedProducts(),
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price'
            ]
        );
        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->type->getOptionArray(),
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type'
            ]
        );
        /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
        $resource = $this->productFactory->create()->getResource();
        $sets = $this->setCollectionFactory->create()->setEntityTypeFilter(
            $resource->getTypeId()
        )->load()->toOptionHash();

        $this->addColumn(
            'set_name',
            [
                'header' => __('Attribute Set'),
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets,
                'header_css_class' => 'col-attr-name',
                'column_css_class' => 'col-attr-name'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->status->getOptionArray(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );

        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->visibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ProductsGrid', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = null;
        try {
            $subscriptiontemplate_id = $this->getRequest()->getParam('label_id');
            $templateCollection = $this->labelRepository->get($subscriptiontemplate_id);

            if(!empty($templateCollection->getData('product_assign'))):
                $products = array_values($this->jsonDecoder->decode($templateCollection->getData('product_assign'), true));
            endif;

            if ($products === null) {
                $products = $this->getProductAssignedModel()->getProductAssign();
                if ($products) {
                    $products = $this->jsonDecoder->decode($products);
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
            $products = [];
        }
        return $products;
    }

    /**
     * Retrieve collection of products to be assigned.
     * @return array
     */
    public function getToBeAssignedProducts()
    {
        $collection = $this->getProductCollection();
        $collection->addAttributeToFilter('md_menu_label',array('null' => true));

        /*//$collection->addFieldToFilter('is_subscription', ['eq' => 1]);
        $collection->addAttributeToFilter([
             ['attribute' => 'subscriptiontemplate_id','null' => true ],
             ['attribute' => 'subscriptiontemplate_id','eq' => 0 ]
        ]);*/

        $productIds = $collection->getColumnValues("entity_id");

        if(is_array($this->_getSelectedProducts())){
            $productIds = array_merge($productIds, $this->_getSelectedProducts());
        }
        
        return $productIds;
    }

    /**
     * Retrieve product collection.
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection()
    {
        $collection = $this->productFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'visibility'
        )->addAttributeToSelect(
            'status'
        )->addAttributeToSelect(
            'price'
        );

        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId > 0) {
            $collection->addStoreFilter($storeId);
        }

        return $collection;
    }
}
