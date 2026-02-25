<?php

namespace Crimson\Catalog\Cron;

use Crimson\Catalog\Model\Service\Sections\Purchased\ProductCategoryAssignmentSectionPurchased;
use Crimson\Catalog\Model\Config as CatalogConfig;
use Crimson\Catalog\Logger\Logger;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SectionPurchased
 * @package Crimson\Catalog\Cron
 */
class SectionPurchased
{
    /**
     * @var ProductCategoryAssignmentSectionPurchased
     */
    protected $productCategoryAssignmentSectionPurchased;

    /**
     * @var CatalogConfig
     */
    protected $catalogConfig;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * SectionPurchased constructor.
     * @param ProductCategoryAssignmentSectionPurchased $productCategoryAssignmentSectionPurchased
     * @param CatalogConfig $catalogConfig
     * @param Logger $logger
     * @param StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        ProductCategoryAssignmentSectionPurchased $productCategoryAssignmentSectionPurchased,
        CatalogConfig $catalogConfig,
        Logger $logger,
        StoreManagerInterface $storeManagerInterface
    )
    {
        $this->productCategoryAssignmentSectionPurchased = $productCategoryAssignmentSectionPurchased;
        $this->catalogConfig = $catalogConfig;
        $this->logger = $logger;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        foreach ($this->storeManagerInterface->getWebsites() as $website) {
            if (!$this->catalogConfig->areCronsEnabled($website->getId())) {
                $this->logger->debug("= = = = Cron Jobs are disabled. Process finished.  = =WebSite: " . $website->getName() . " = =");
                continue;
            }

            $this->productCategoryAssignmentSectionPurchased->execute($website->getId());
        }
    }
}
