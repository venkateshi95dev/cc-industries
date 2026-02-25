<?php
namespace Cokertire\Distributors\Model\ResourceModel\Distributors;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * ID Field Name
     * 
     * @var string
     */
    protected $_idFieldName = 'distributorsid';

    /**
     * Event prefix
     * 
     * @var string
     */
    protected $_eventPrefix = 'cokertire_distributors_collection';

    /**
     * Event object
     * 
     * @var string
     */
    protected $_eventObject = 'cokertire_distributors_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cokertire\Distributors\Model\Distributors', 'Cokertire\Distributors\Model\ResourceModel\Distributors');
    }



}
