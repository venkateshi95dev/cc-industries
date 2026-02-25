<?php
declare(strict_types=1);

namespace Crimson\Reports\Block\Adminhtml\Shopcart\Abandoned;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Reports\Block\Adminhtml\Shopcart\Abandoned\Grid as OriginalGrid;
use Crimson\Reports\Model\Grid\Filter\SkuCallback;

class Grid extends OriginalGrid
{

    /**
     * @inheritdoc
     */
    protected function _prepareCollection(): \Magento\Backend\Block\Widget\Grid
    {
        parent::_prepareCollection();
        $this->getCollection()->resolveSkus();
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns(): Extended
    {
        parent::_prepareColumns();
        $this->addColumnAfter(
            'skus',
            [
                'header' => __('SKUs'),
                'index' => 'skus',
                'type' => 'text',
                'sortable' => false,
                'header_css_class' => 'col-skus',
                'column_css_class' => 'col-skus'
            ],
            'email'
        );
        return $this;
    }

    protected function _addColumnFilterToCollection($column): OriginalGrid
    {
        if ($column->getId() == 'skus') {
            SkuCallback::applyFilter($this->getCollection(), $column);
            return $this;
        }
        return parent::_addColumnFilterToCollection($column);
    }
}
