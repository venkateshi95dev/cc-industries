<?php

namespace Crimson\Shipping\Model\System\Config\Backend\Shipping;

use Crimson\Shipping\Model\ResourceModel\Carrier\TableratezipflatrateFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Tableratezipflatrate
 * @package Crimson\Shipping\Model\System\Config\Backend\Shipping
 */
class Tableratezipflatrate extends Value
{

    /**
     * @var TableratezipflatrateFactory
     */
    protected $_tablerateFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        TableratezipflatrateFactory $tablerateFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_tablerateFactory = $tablerateFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return Value
     * @throws LocalizedException
     */
    public function afterSave(): Value
    {
        /** @var \Crimson\Shipping\Model\ResourceModel\Carrier\Tableratezipflatrate $tableZipFlatRate*/
        $tableZipFlatRate = $this->_tablerateFactory->create();
        $tableZipFlatRate->uploadAndImport($this);

        return parent::afterSave();
    }
}
