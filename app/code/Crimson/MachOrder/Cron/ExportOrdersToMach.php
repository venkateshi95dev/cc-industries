<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 10:31 AM
 * @brief
 */

namespace Crimson\MachOrder\Cron;

use Crimson\MachBase\Model\MachConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ExportOrdersToMach
 * @package Crimson\MachOrder\Cron
 */
class ExportOrdersToMach
{

    public function __construct(
        protected MachConfig $machConfig,
        protected \Crimson\MachOrder\Model\Service\ExportOrdersToMach $exportOrdersToMach,
        protected StoreManagerInterface $storeManager
    ) {}

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(): void
    {
        $zipWebsiteId = $this->storeManager->getWebsite(MachConfig::ZIP_WEBSITE_CODE)->getId();
        if (!$this->machConfig->isEnabled($zipWebsiteId)) {
            return;
        }

        $this->exportOrdersToMach->execute();
    }
}
