<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/21/2019 5:42 PM
 * @brief
 */

namespace Crimson\MachCatalog\Cron;

use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCatalog\Model\Service\UpdateProducts;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class UpdateMachUpdatedProducts
 * @package Crimson\MachCatalog\Cron
 */
class UpdateMachUpdatedProducts
{

    public function __construct(
        protected UpdateProducts $updateProducts,
        protected MachConfig $machConfig,
        protected StoreManagerInterface $storeManager
    ) {}

    /**
     * @throws LocalizedException
     */
    public function execute(): void
    {
        $zipWebsiteId = $this->storeManager->getWebsite(MachConfig::ZIP_WEBSITE_CODE)->getId();
        if (!$this->machConfig->isEnabled($zipWebsiteId)) {
            return;
        }

        $this->updateProducts->execute();
    }
}
