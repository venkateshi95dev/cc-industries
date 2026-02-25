<?php

namespace Crimson\MachInventoryImport\Cron;

use Crimson\MachBase\Model\MachConfig;
use Crimson\MachInventoryImport\Model\Config;
use Crimson\MachInventoryImport\Service\ImportMachInventory as ImportMachInventoryService;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ImportMachInventory
 * @package Crimson\MachInventoryImport\Cron
 */
class ImportMachInventory
{

    public function __construct(
        protected ImportMachInventoryService $importMachInventory,
        protected MachConfig $machConfig,
        protected StoreManagerInterface $storeManager,
        protected Config $importInventoryConfig
    ) {}

    public function execute(): void
    {
        try {
            $zipWebsiteId = $this->storeManager->getWebsite(MachConfig::ZIP_WEBSITE_CODE)->getId();
            if ($this->machConfig->isEnabled($zipWebsiteId) && $this->importInventoryConfig->isEnabled($zipWebsiteId)) {
                $this->importMachInventory->execute();
            }
        } catch (\Exception $e) {}
    }
}
