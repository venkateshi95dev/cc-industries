<?php
/**
 * Created By : Silk
 */
namespace Silk\ProductImage\Block\Adminhtml\Product\Merchandiser;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;

class Productgrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Silk\ProductImage\Model\Products
     * @since 100.1.0
     */
    protected $_products;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;


    /**
     * @param \Magento\Backend\Block\Template\Context    $context
     * @param \Magento\Backend\Helper\Data               $backendHelper
     * @param \Magento\Framework\Registry                $coreRegistry
     * @param \Magento\Framework\Module\Manager          $moduleManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Visibility|null                            $visibility
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Status $status = null,
        Visibility $visibility = null,
        \Silk\ProductImage\Model\Products $products,
        array $data = []
    ) {
        $this->_products = $products;
        $this->coreRegistry = $coreRegistry;
        $this->moduleManager = $moduleManager;
        $this->_storeManager = $storeManager;
        $this->visibility = $visibility ?: ObjectManager::getInstance()->get(Visibility::class);
        $this->status = $status ?: ObjectManager::getInstance()->get(Status::class);
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * [_construct description]
     * @return [type] [description]
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('img_product_products');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('entity_id')) {
            $this->setDefaultFilter(['in_products' => 1]);
        } else {
            $this->setDefaultFilter(['in_products' => 0]);
        }
        $this->setSaveParametersInSession(true);
    }

    /**
     * [get store id]
     *
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }


    /**
     * Get cache key for product position in grid.
     *
     * @return string
     * @since 100.1.0
     */
    protected function _getPositionCacheKey()
    {
        return $this->getPositionCacheKey() ?? $this->getParentBlock()->getPositionCacheKey();
    }
    /**
     * @return array|null
     */
    public function getDocument()
    {
        return $this->coreRegistry->registry('img_document');
    }

    protected function _prepareCollection()
    {
        $this->_products->setCacheKey($this->_getPositionCacheKey());
        $productPositions = $this->_backendSession->getDocumentProductPositions();
        $collection = $this->_products->getCollectionForGrid(
            (int) $this->getRequest()->getParam('document_id', 0),
            (int) $this->getRequest()->getParam('store'),
            $productPositions
        );

        if (!$collection) {
            return $this;
        }
        $collection->addAttributeToSelect('visibility')
            ->addAttributeToSelect('status');

        $collection->clear();
        $this->setCollection($collection);

        $this->_preparePage();

        $idx = ($collection->getCurPage() * $collection->getPageSize()) - $collection->getPageSize();

        foreach ($collection as $item) {
            $item->setPosition($idx);
            $idx++;
        }

        return $this;
    }


    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        // $this->addColumn(
        //     'in_products',
        //     [
        //         'type' => 'checkbox',
        //         'html_name' => 'products_id',
        //         'required' => true,
        //         'values' => $this->_getSelectedProducts(),
        //         'align' => 'center',
        //         'index' => 'entity_id',
        //     ]
        // );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'width' => '50px',
                'index' => 'entity_id',
                'type' => 'number',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type',
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku',
            ]
        );
        $store = $this->_getStore();
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

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->status->getOptionArray()
            ]
        );
        $this->getColumnSet()->setSortable(false);
        $this->setFilterVisibility(false);
        return parent::_prepareColumns();
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if ($products === null && $this->getDocument()) {
            $products = $this->getDocument()->getProductsPosition();
            return array_keys($products);
        }
        return $products;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/index/grids', ['_current' => true]);
    }

}