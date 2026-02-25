<?php

namespace Cokertire\Showpages\Model;

class Showpages extends \Magento\Framework\Model\AbstractModel
{
    const BASE_MEDIA_PATH = 'showpages/showpages/images';
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'coker_showpages';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'coker_showpages';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'coker_showpages';


    public $_storeManager;
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cokertire\Showpages\Model\ResourceModel\Showpages');
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
