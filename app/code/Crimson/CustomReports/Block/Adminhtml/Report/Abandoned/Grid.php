<?php
declare(strict_types=1);

namespace Crimson\CustomReports\Block\Adminhtml\Report\Abandoned;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Reports\Block\Adminhtml\Shopcart\Abandoned\Grid as OriginalGrid;
use Crimson\CustomReports\Model\ResourceModel\Quote\CollectionFactory as CustomCollectionFactory;
use Magento\Reports\Model\ResourceModel\Quote\CollectionFactory as OriginalCollectionFactory;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Stdlib\Parameters;

class Grid extends OriginalGrid
{
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        OriginalCollectionFactory $quotesFactory,
        protected DecoderInterface $urlDecoder,
        protected Parameters $parameters,
        protected CustomCollectionFactory $customCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $quotesFactory, $urlDecoder, $parameters, $data);
    }

    protected function _prepareCollection(): \Magento\Backend\Block\Widget\Grid
    {
        $collection = $this->customCollectionFactory->create();
        $this->setCollection($collection);

        $filter = $this->getParam($this->getVarNameFilter(), []);
        if ($filter) {
            $filter = $this->urlDecoder->decode($filter);
            $this->parameters->fromString($filter);
            $data = $this->parameters->toArray();
        } else {
            $data = [];
        }

        $collection->prepareForAbandonedReport($this->_storeIds, $data);
        \Magento\Backend\Block\Widget\Grid::_prepareCollection();
        $this->getCollection()->resolveCustomerNames();
        $this->getCollection()->resolveItemsData();

        return $this;
    }

    protected function _prepareColumns(): Extended
    {
        parent::_prepareColumns();
        $this->removeColumn('skus');
        $this->removeColumn('items_count');
        $this->addColumnAfter(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
            ],
            'email'
        );


        $this->addColumnAfter(
            'name',
            [
                'header' => __('Product Name'),
                'index' => 'name',
            ],
            'sku'
        );
        $this->addColumnAfter(
            'qty_on_hand_at_add',
            [
                'header' => __('Qty On Hand (at add)'),
                'index' => 'qty_on_hand_at_add',
                'type' => 'number',
            ],
            'name'
        );
        $this->addColumnAfter(
            'is_stock_at_add',
            [
                'header' => __('Stock Status (at add)'),
                'index' => 'is_stock_at_add',
                'type' => 'options',
                'options' => [
                    '' => __(' '),
                    '1' => __('Yes'),
                    '0' => __('No'),
                ],
            ],
            'qty_on_hand_at_add'
        );
        return $this;
    }

    protected function _addColumnFilterToCollection($column): OriginalGrid
    {
        $customFilterColumns = [
            'sku',
            'name',
            'qty_on_hand_at_add',
            'is_stock_at_add'
        ];

        if (in_array($column->getId(), $customFilterColumns)) {
            $collection = $this->getCollection();
            $select = $collection->getSelect();
            $itemTable = $collection->getTable('quote_item');
            $condition = $column->getFilter()->getCondition();
            $collection->addActiveFilter($column->getIndex(), $condition);

            if (!isset($select->getPart('from')['filter_quote_item'])) {
                $select->join(
                    ['filter_quote_item' => $itemTable],
                    'main_table.entity_id = filter_quote_item.quote_id',
                    []
                );

                $where = $select->getPart(\Magento\Framework\DB\Select::WHERE);
                foreach ($where as $key => $whereCondition) {
                    if (strpos($whereCondition, 'store_id') !== false) {
                        $where[$key] = str_replace('`store_id`', '`main_table`.`store_id`', $whereCondition);
                    }
                }
                $select->setPart(\Magento\Framework\DB\Select::WHERE, $where);
                $select->group('main_table.entity_id');
            }

            $field = 'filter_quote_item.' . $column->getIndex();
            $select->where($collection->getConnection()->prepareSqlCondition($field, $condition));

            return $this;
        }

        return parent::_addColumnFilterToCollection($column);
    }
}
