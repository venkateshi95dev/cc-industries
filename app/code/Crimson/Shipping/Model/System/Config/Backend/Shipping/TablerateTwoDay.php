<?php
/**
 * @namespace   Crimson
 * @module      Shipping
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/25/2019
 */
namespace Crimson\Shipping\Model\System\Config\Backend\Shipping;

use Crimson\Shipping\Model\ResourceModel\Carrier\TableratetwodayFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class TablerateTwoDay
 * @package Crimson\Shipping\Model\System\Config\Backend\Shipping
 */
class TablerateTwoDay extends Value
{
    /**
     * @var TableratetwodayFactory
     */
    protected $_tablerateFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        TableratetwodayFactory $tablerateFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_tablerateFactory = $tablerateFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return TablerateTwoDay
     * @throws LocalizedException
     */
    public function afterSave(): TablerateTwoDay
    {
        /** @var \Crimson\Shipping\Model\ResourceModel\Carrier\Tableratetwoday $tableRateTwoday */
        $tableRateTwoday = $this->_tablerateFactory->create();
        $tableRateTwoday->uploadAndImport($this);

        return parent::afterSave();
    }
}
