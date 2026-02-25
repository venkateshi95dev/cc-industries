<?php
namespace Cokertire\Disclaimers\Model;

class Disclaimers extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     * 
     * @var string
     */
    const CACHE_TAG = 'cokertire_disclaimers';

    /**
     * Cache tag
     * 
     * @var string
     */
    protected $_cacheTag = 'cokertire_disclaimers';

    /**
     * Event prefix
     * 
     * @var string
     */
    protected $_eventPrefix = 'cokertire_disclaimers';


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cokertire\Disclaimers\Model\ResourceModel\Disclaimers');
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
