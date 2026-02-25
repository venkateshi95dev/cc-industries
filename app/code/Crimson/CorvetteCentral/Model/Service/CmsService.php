<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Model\Service;

use Exception;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class CmsService
{
    /**
     * @param PageRepositoryInterface $pageRepository
     * @param PageFactory $pageFactory
     * @param StoreManagerInterface $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BlockRepositoryInterface $blockRepository
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        private readonly PageRepositoryInterface $pageRepository,
        private readonly PageFactory $pageFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly BlockFactory $blockFactory,
    ) {
    }

    /**
     * Create or update a CMS page
     *
     * @param string $identifier
     * @param string $title
     * @param string $contentPath
     * @param string $layout
     * @param string $metaTitle
     * @param string $metaKeywords
     * @param string $metaDescription
     * @param string $storeCode
     * @param string|null $bannerContentPath
     * @return void
     * @throws Exception
     */
    public function createOrUpdatePage(
        string $identifier,
        string $title,
        string $contentPath,
        string $layout,
        string $metaTitle,
        string $metaKeywords,
        string $metaDescription,
        string $storeCode,
        ?string $bannerContentPath = null
    ): void {
        $content = $this->getHtmlContent($contentPath);
        $bannerContent = $bannerContentPath ? $this->getHtmlContent($bannerContentPath) : '';
        $store = $this->storeManager->getStore($storeCode);
        $storeId = (int)$store->getId();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('identifier', $identifier)
            ->addFilter('store_id', $storeId)
            ->create();

        $pages = $this->pageRepository->getList($searchCriteria)->getItems();

        if (!empty($pages)) {
            $page = reset($pages);
        } else {
            $page = $this->pageFactory->create();
            $page->setIdentifier($identifier);
        }

        $page->setTitle($title)
            ->setPageLayout($layout)
            ->setContent($content)
            ->setCustomBanner($bannerContent)
            ->setMetaTitle($metaTitle)
            ->setMetaKeywords($metaKeywords)
            ->setMetaDescription($metaDescription)
            ->setIsActive(1)
            ->setStoreId($storeId);

        $this->pageRepository->save($page);
    }

    /**
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function createOrUpdateBlock(
        string $identifier,
        string $title,
        string $contentPath,
        string $storeCode
    ): void {
        $content = $this->getHtmlContent($contentPath);
        $store = $this->storeManager->getStore($storeCode);
        $storeId = $store->getId();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('identifier', $identifier)
            ->addFilter('store_id', $storeId)
            ->create();

        $blocks = $this->blockRepository->getList($searchCriteria)->getItems();

        /** @var BlockInterface $block */
        if (!empty($blocks)) {
            $block = reset($blocks);
        } else {
            $block = $this->blockFactory->create();
        }

        $block->setIdentifier($identifier)
            ->setTitle($title)
            ->setIsActive(1)
            ->setContent($content)
            ->setStoreId($storeId);

        $this->blockRepository->save($block);
    }

    /**
     * Create or update custom banner for CMS page
     *
     * @param string $identifier
     * @param string $storeCode
     * @param string $bannerContentPath
     * @return void
     * @throws Exception
     */
    public function createOrUpdateCustomBannerForCmsPage(
        string $identifier,
        string $storeCode,
        string $bannerContentPath
    ): void {
        $bannerContent = $this->getHtmlContent($bannerContentPath);
        $store = $this->storeManager->getStore($storeCode);
        $storeId = (int)$store->getId();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('identifier', $identifier)
            ->addFilter('store_id', $storeId)
            ->create();

        $pages = $this->pageRepository->getList($searchCriteria)->getItems();

        if (!empty($pages)) {
            $page = reset($pages);
            $page->setCustomBanner($bannerContent);
            $this->pageRepository->save($page);
        }
    }

    /**
     * Get HTML content from file
     *
     * @param string $path
     * @return string
     * @throws Exception
     */
    protected function getHtmlContent(string $path): string
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new Exception("File not found `$path`");
        }
        return (string)file_get_contents($path);
    }
}
