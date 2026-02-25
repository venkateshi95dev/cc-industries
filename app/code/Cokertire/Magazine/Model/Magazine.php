<?php
namespace Cokertire\Magazine\Model;

class Magazine extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'ct_magazine';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'ct_magazine';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'ct_magazine';


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cokertire\Magazine\Model\ResourceModel\Magazine');
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
