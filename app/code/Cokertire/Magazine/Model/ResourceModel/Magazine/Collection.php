<?php
namespace Cokertire\Magazine\Model\ResourceModel\Magazine;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'magazine_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'ct_magazine_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'ct_magazine_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cokertire\Magazine\Model\Magazine', 'Cokertire\Magazine\Model\ResourceModel\Magazine');
    }



}
