<?php

namespace Cokertire\Showpages\Model\ResourceModel\Showpages;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'showpages_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'coker_showpages_grid_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'coker_showpages_grid_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cokertire\Showpages\Model\Showpages', 'Cokertire\Showpages\Model\ResourceModel\Showpages');
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);
        return $countSelect;
    }

}
