<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Helper Class returns the configuration of connector
 */
class Config extends AbstractHelper
{
    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * scopeConfig for system Congiguration
     *
     * @var string
     */
    public $scopeConfig;

    /**
     * @var ResourceConnection
     */
    public $resource;

    /**
     * @var AdapterInterface
     */
    public $connection;

    /**
     * MageCustomerApi
     *
     * @var Data
     */
    public $data;

    /**
     *
     * @var DataObject
     */
    public $obj;

    /**
     *
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resource
     * @param Data $data
     * @param DataObject $obj
     * @param Context $context
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resource,
        Data $data,
        DataObject $obj,
        Context $context
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->resource = $resource;
        $this->connection = $resource->getConnection('write');
        $this->data = $data;
        $this->obj = $obj;
        parent::__construct($context);
    }

    /**
     * Will return the component from connector configurations
     *
     * @return DataObject
     */
    public function getConfigValues()
    {
        try {
            $this->obj->setData('component', 'NAV');
        } catch (LocalizedException $ex) {
            $this->data->createLog(__METHOD__, $ex->getMessage(), "i95devException", 'critical');
        }
        return $this->obj;
    }
}
