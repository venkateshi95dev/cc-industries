<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/19/2019 9:48 AM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Cron;

use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCatalogRequest\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CatalogRequestExport
 * @package Crimson\MachCatalogRequest\Cron
 */
class CatalogRequestExport
{

    public function __construct(
        protected MachConfig $machConfig,
        protected Config $catalogRequestConfig,
        protected \Crimson\MachCatalogRequest\Model\Service\CatalogRequestExport $catalogRequestExport,
        protected StoreManagerInterface $storeManager
    ) {}

    public function execute(): void
    {
        $zipWebsiteId = $this->storeManager->getWebsite(MachConfig::ZIP_WEBSITE_CODE)->getId();
        if ($this->machConfig->isEnabled($zipWebsiteId) && $this->catalogRequestConfig->isEnabled($zipWebsiteId)) {
            $this->catalogRequestExport->execute();
        }
    }
}
