<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/4/2019 2:23 PM
 * @brief
 */

namespace Crimson\MachOrder\Cron;

use Crimson\MachBase\Model\MachConfig;
use Crimson\MachOrder\Model\Config;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class UpdateScheduledOrders
 * @package Crimson\MachOrder\Cron
 */
class UpdateScheduledOrders
{

    public function __construct(
        protected \Crimson\MachOrder\Model\Service\UpdateScheduledOrders $updateScheduledOrders,
        protected Config $config,
        protected MachConfig $machConfig,
        protected StoreManagerInterface $storeManager
    ) {}

    public function execute(): void
    {
        $zipWebsiteId = $this->storeManager->getWebsite(MachConfig::ZIP_WEBSITE_CODE)->getId();
        if (!$this->machConfig->isEnabled($zipWebsiteId)) {
            return;
        }

        $this->updateScheduledOrders->execute();
    }
}
