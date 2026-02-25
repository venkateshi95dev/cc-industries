<?php

namespace Crimson\MachInventoryImport\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 * @package Crimson\MachInventoryImport\Model
 */
class Config
{

    CONST CSV_FILE_FULL_PATH_AND_NAME         = "/var/import/ImportMachInventory/inventory_magento2.csv";
    CONST CSV_FILES_FULL_PATH                 = "/var/import/ImportMachInventory/";
    const FILE_WILD_CARD_NAME_CSV_FILES       = 'inventory_magento2_';
    const FILE_ALL                            = -1;
    CONST CSV_FILE_PROCESSED_PATH_NAME        = "/var/import/ImportMachInventory/Processed/";
    CONST CSV_FILE_PROCESSED_NAME             = "inventory_magento2_";
    CONST CSV_FILE_INVALID_ROW_ERROR          = "Invalid row data";
    const XPATH_MACH_INVENTORY_IMPORT_ENABLED = 'mach/mach_inventory_import/enabled';
    const ROW_SKU = 'sku';
    const ROW_QTY = 'qty';
    const XPATH_MACH_INVENTORY_IMPORT_THRESHOLD_MINIMUM = 'mach/mach_inventory_import/threshold_minimum';
    const INSERT_DATA_ROW_THRESHOLD_MINIMUM_DEFAULT     = 5000;

    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected StoreManagerInterface $storeManager
    ) {}

    /**
     * @param null $websiteId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isEnabled($websiteId = null): bool
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return $this->scopeConfig->isSetFlag(self::XPATH_MACH_INVENTORY_IMPORT_ENABLED, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @param null $websiteId
     * @return int
     * @throws NoSuchEntityException
     */
    public function getThresholdMinValue($websiteId = null): int
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        $minimum = $this->scopeConfig->getValue(self::XPATH_MACH_INVENTORY_IMPORT_THRESHOLD_MINIMUM, ScopeInterface::SCOPE_WEBSITE, $websiteId);

        return $minimum ? (int) $minimum : self::INSERT_DATA_ROW_THRESHOLD_MINIMUM_DEFAULT;
    }

    /**
     * @return string
     */
    public function getProcessedFilePath(): string
    {
       return self::CSV_FILE_PROCESSED_PATH_NAME . self::CSV_FILE_PROCESSED_NAME . time() . ".csv";

    }
}
