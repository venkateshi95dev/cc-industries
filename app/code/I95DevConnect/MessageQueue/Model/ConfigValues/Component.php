<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\ConfigValues;

use I95DevConnect\MessageQueue\Helper\Config;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class for Getting Magento component values
 */
class Component implements ArrayInterface
{
    /**
     * @var Config $config
     */
    public $config;

    /**
     *
     * @var LoggerInterface
     */
    public $logger;

    /**
     *
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(Config $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Getting Magento component values
     *
     * @return array
     */
    public function toOptionArray()
    {
        $componenetArray = [];
        $helper = $this->config;
        try {
            $configurationValues = $helper->getConfigValues()->getData();
            $isRMsOrGp = $configurationValues['component'];
            $componenetArray[$isRMsOrGp] = $isRMsOrGp;
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                LoggerInterface::CRITICAL
            );
        }

        return $componenetArray;
    }
}
