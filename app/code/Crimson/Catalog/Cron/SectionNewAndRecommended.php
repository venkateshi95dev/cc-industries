<?php

namespace Crimson\Catalog\Cron;

use Crimson\Catalog\Model\Service\Sections\NewAndRecommended\ProductCategoryAssignmentSectionNew;
use Crimson\Catalog\Model\Config as CatalogConfig;
use Crimson\Catalog\Logger\Logger;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SectionNewAndRecommended
 * @package Crimson\Catalog\Cron
 */
class SectionNewAndRecommended
{
    /**
     * @var ProductCategoryAssignmentSectionNew
     */
    protected $productsForHomepageSectionNew;

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
     * SectionNewAndRecommended constructor.
     * @param ProductCategoryAssignmentSectionNew $productsForHomepageSectionNew
     * @param CatalogConfig $catalogConfig
     * @param Logger $logger
     * @param StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        ProductCategoryAssignmentSectionNew $productsForHomepageSectionNew,
        CatalogConfig $catalogConfig,
        Logger $logger,
        StoreManagerInterface $storeManagerInterface
    )
    {
        $this->productsForHomepageSectionNew = $productsForHomepageSectionNew;
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

            $this->productsForHomepageSectionNew->execute($website->getId());
        }
    }
}
