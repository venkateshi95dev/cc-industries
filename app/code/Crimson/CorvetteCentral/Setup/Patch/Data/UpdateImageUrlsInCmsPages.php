<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Psr\Log\LoggerInterface;

class UpdateImageUrlsInCmsPages implements DataPatchInterface
{
    /**
     * List of CMS page IDs to update
     * You can modify this list as needed
     */
    private const CMS_PAGE_IDS = [
        'show/schedule',
        'carlisle',
        'bloomingtongold',
        'wheel-offset',
        'wholesale',
        'warranty',
        'shipping-2',
        'prop65',
        'gallery/events',
        'gallery/custom-vettes',
        'gallery/concept-57',
        'gallery/2020',
        'gallery/2014',
        'gallery/2013',
        'gallery/2012',
        'gallery/2011',
        'gallery/2010',
        'gallery/2009',
        'gallery/2008',
        'gallery/2007',
        'gallery/2006',
        'gallery/2005',
        'gallery/2004',
        'gallery/2003',
        'gallery/2002',
        'gallery/2001',
        'gallery/2000',
        'gallery/1999',
        'gallery/1998',
        'gallery/1997',
        'gallery/1996',
        'gallery/1995',
        'gallery/1994',
        'gallery/1993',
        'gallery/1992',
        'gallery/1991',
        'gallery/1990',
        'gallery/1989',
        'gallery/1988',
        'gallery/1987',
        'gallery/1986',
        'gallery/1985',
        'gallery/1984',
        'gallery/1982',
        'gallery/1981',
        'gallery/1980',
        'gallery/1979',
        'gallery/1978',
        'gallery/1977',
        'gallery/1976',
        'gallery/1975',
        'gallery/1974',
        'gallery/1973',
        'gallery/1972',
        'gallery/1971',
        'gallery/1970',
        'gallery/1969',
        'gallery/1968',
        'gallery/1967',
        'gallery/1966',
        'gallery/1965',
        'gallery/1964',
        'gallery/1963',
        'gallery/1962',
        'gallery/1961',
        'gallery/1960',
        'gallery/1959',
        'gallery/1958',
        'gallery/1957',
        'gallery/1956',
        'gallery/1955',
        'gallery/1954',
        'gallery/1953',
        'gallery',
        'return-policy',
        'payment',
        'grilleteethusa',
        'discount/military',
        'discounts/s2f',
        'discounts',
        'concept_57',
        'faqtutorials',
        'contactus',
        'privacy-policy',
        'history',
    ];

    /**
     * @param PageRepositoryInterface $pageRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly PageRepositoryInterface $pageRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @inheritDoc
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();

        try {
            $store = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE);
            $storeId = (int)$store->getId();

            foreach (self::CMS_PAGE_IDS as $pageIdentifier) {
                $this->updateImageUrlsInPage($pageIdentifier, $storeId);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error updating image URLs in CMS pages: ' . $e->getMessage());
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * Update image URLs in a CMS page
     *
     * @param string $pageIdentifier
     * @param int $storeId
     * @return void
     */
    private function updateImageUrlsInPage(string $pageIdentifier, int $storeId): void
    {
        try {
            // Search for the page
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('identifier', $pageIdentifier)
                ->addFilter('store_id', $storeId)
                ->create();

            $pages = $this->pageRepository->getList($searchCriteria)->getItems();

            if (empty($pages)) {
                $this->logger->warning("CMS page with identifier '{$pageIdentifier}' not found.");
                return;
            }

            $page = reset($pages);
            $content = $page->getContent();
            $bannerContent = $page->getCustomBanner();

            // Replace image URLs
            if ($content){
                $updatedContent = preg_replace(
                    '/src="{{media url=wysiwyg\/(?!corvettecentral\/)/i',
                    'src="{{media url=wysiwyg/corvettecentral/',
                    $content
                );
                $page->setContent($updatedContent);
            }

            if ($bannerContent){
                $updatedBannerContent = preg_replace(
                    '/src="{{media url=wysiwyg\/(?!corvettecentral\/)/i',
                    'src="{{media url=wysiwyg/corvettecentral/',
                    $bannerContent
                );
                $page->setCustomBanner($updatedBannerContent);
            }

            if ($content || $bannerContent){
                $this->pageRepository->save($page);
                $this->logger->info("Updated image URLs in CMS page: {$pageIdentifier}");
            } else{
                $this->logger->info("There is no image URLs to be updated for CMS page: {$pageIdentifier}");
            }
        } catch (LocalizedException $e) {
            $this->logger->error("Error updating CMS page '{$pageIdentifier}': " . $e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
