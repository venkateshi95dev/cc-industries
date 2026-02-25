<?php
namespace Cokertire\Disclaimers\Model\ResourceModel\Disclaimers;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * ID Field Name
     * 
     * @var string
     */
    protected $_idFieldName = 'disclaimers_id';

    /**
     * Event prefix
     * 
     * @var string
     */
    protected $_eventPrefix = 'cokertire_disclaimers_collection';

    /**
     * Event object
     * 
     * @var string
     */
    protected $_eventObject = 'cokertire_disclaimers_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cokertire\Disclaimers\Model\Disclaimers', 'Cokertire\Disclaimers\Model\ResourceModel\Disclaimers');
    }



}
