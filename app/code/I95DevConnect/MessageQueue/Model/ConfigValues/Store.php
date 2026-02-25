<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\ConfigValues;

use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class for getting magento store data
 */
class Store implements ArrayInterface
{
    /**
     * @var Data
     */
    public $data;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    public $store;

    /**
     *
     * @var LoggerInterface
     */
    public $logger;

    /**
     *
     * @param Data $data
     * @param \Magento\Store\Model\System\Store $store
     * @param LoggerInterface $logger
     */
    public function __construct(Data $data, \Magento\Store\Model\System\Store $store, LoggerInterface $logger)
    {
        $this->data = $data;
        $this->store = $store;
        $this->logger = $logger;
    }

    /**
     * Getting Magento stores array
     *
     * @return array
     */
    public function toOptionArray()
    {
        try {
            $storeArray = [];
            $storeArray = $this->store->getStoreValuesForForm(false, true);
            unset($storeArray[0]);
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                LoggerInterface::CRITICAL
            );
        }

        return $storeArray;
    }
}
