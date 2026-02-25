<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/4/2019 1:57 PM
 * @brief
 */

namespace Crimson\MachOrder\Cron;

use Crimson\MachBase\Model\MachConfig;
use Crimson\MachOrder\Model\Service\UpdateOrdersFromMach as UpdateOrdersFromMachService;
use Crimson\MachOrder\Model\Config;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class UpdateOrdersFromMach
 * @package Crimson\MachOrder\Cron
 */
class UpdateOrdersFromMach
{

    public function __construct(
        protected UpdateOrdersFromMachService $updateOrdersFromMach,
        protected Config $config,
        protected MachConfig $machConfig,
        protected StoreManagerInterface $storeManager
    ) {}

    public function executeShortTermUpdate(): void
    {
        $zipWebsiteId = $this->storeManager->getWebsite(MachConfig::ZIP_WEBSITE_CODE)->getId();
        if (!$this->machConfig->isEnabled($zipWebsiteId)) {
            return;
        }

        $this->updateOrdersFromMach->execute($this->config->getShortTermMonths());
    }

    public function executeLongTermUpdate(): void
    {
        $zipWebsiteId = $this->storeManager->getWebsite(MachConfig::ZIP_WEBSITE_CODE)->getId();
        if (!$this->machConfig->isEnabled($zipWebsiteId)) {
            return;
        }

        $this->updateOrdersFromMach->execute($this->config->getLongTermMonths());
    }
}
