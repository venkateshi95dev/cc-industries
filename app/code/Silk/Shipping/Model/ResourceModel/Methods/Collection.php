<?php

namespace Silk\Shipping\Model\ResourceModel\Methods;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Silk\Shipping\Model\Methods', 'Silk\Shipping\Model\ResourceModel\Methods');
    }

    /**
     * Filter collection by enabled
     */
    public function addStatusFilter()
    {
        return $this->addFieldToFilter('method_status', 1);
    }

    /**
     * Filter collection by specified store ids
     *
     * @param array|int $storeIds
     * @return $this
     */
    public function addStoreFilter($storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }
        $this->getSelect()->where('main_table.store_id IN (?)', $storeIds);
        return $this;
    }
}
