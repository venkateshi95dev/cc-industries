<?php
namespace Cokertire\Distributors\Model;

class Distributors extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     * 
     * @var string
     */
    const CACHE_TAG = 'cokertire_distributors';

    /**
     * Cache tag
     * 
     * @var string
     */
    protected $_cacheTag = 'cokertire_distributors';

    /**
     * Event prefix
     * 
     * @var string
     */
    protected $_eventPrefix = 'cokertire_distributors';


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cokertire\Distributors\Model\ResourceModel\Distributors');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
