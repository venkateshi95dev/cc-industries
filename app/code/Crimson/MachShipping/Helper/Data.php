<?php

namespace Crimson\MachShipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package Crimson\MachShipping\Helper
 */
class Data extends AbstractHelper
{
    /** @var StoreManagerInterface $_storeManager */
    protected $_storeManager;

    /**
     * Data constructor.
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @param $olderDate
     * @param null $youngerDate
     * @return float
     */
    public function calculateDateDiffInMinutes($olderDate, $youngerDate = null): float
    {
        try {
            $olderDate = new \Zend_Date($olderDate, 'yyyy-MM-dd HH:mm:ss');
            if (is_null($youngerDate)) {
                $youngerDate = new \Zend_Date();
            } else {
                $youngerDate = new \Zend_Date($youngerDate, 'yyyy-MM-dd HH:mm:ss');
            }
            $diff = $youngerDate->sub($olderDate)->toValue();

            return floor($diff / 60);
        } catch (\Zend_Date_Exception $exception) {
            return 1000.00;
        }
    }

}
