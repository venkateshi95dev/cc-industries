<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Exception;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;

class AddHomePage implements DataPatchInterface
{
    const CMS_PAGE_TITLE = 'Corvette Central Home';
    const CMS_PAGE_ID = 'corvette_central_home';
    const CMS_PAGE_KEYWORDS = 'Corvette parts, Corvette accessories, Corvette restoration parts, Corvette performance parts, Corvette interior accessories, Corvette exhaust systems.';
    const CMS_PAGE_DATA_PATH = '/../../data/pages/home.html';
    const CMS_LAYOUT_UPDATE = 'full-width-cms-base';
    const CMS_TEMPLATE_META_TITLE = 'Corvette Parts & Accessory Supplier | Corvette Central';
    const CMS_TEMPLATE_META_DESCRIPTION = 'Corvette parts supplier of restoration and performance Corvette parts for all generations.  America\'s #1 leading supplier and manufacturer of Corvette parts.';

    /**
     * @param PageRepositoryInterface $pageRepository
     * @param PageFactory $pageFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StoreManagerInterface $storeManager
     * @param WriterInterface $configWriter
     */
    public function __construct(
        private readonly PageRepositoryInterface  $pageRepository,
        private readonly PageFactory              $pageFactory,
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly StoreManagerInterface    $storeManager,
        private readonly WriterInterface          $configWriter
    ) {
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

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();

        $filePath = __DIR__ . self::CMS_PAGE_DATA_PATH;
        $content = $this->getHtmlContent($filePath);
        $store = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE);

        try {
            $page = $this->pageRepository->getById(self::CMS_PAGE_ID);
        } catch (LocalizedException $e) {
            $page = $this->pageFactory->create();
            $page->setIdentifier(self::CMS_PAGE_ID)
                ->setTitle(self::CMS_PAGE_TITLE)
                ->setPageLayout(self::CMS_LAYOUT_UPDATE)
                ->setMetaTitle(self::CMS_TEMPLATE_META_TITLE)
                ->setMetaKeywords(self::CMS_PAGE_KEYWORDS)
                ->setMetaDescription(self::CMS_TEMPLATE_META_DESCRIPTION);
        }

        $page->setContent($content);
        $page->setStoreId($store->getId());

        $savedPage = $this->pageRepository->save($page);

        $this->configWriter->save('web/default/cms_home_page', $savedPage->getId(), ScopeInterface::SCOPE_STORE, $store->getId());

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @param string $path
     * @return false|string
     * @throws Exception
     */
    protected function getHtmlContent(string $path): false|string
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new Exception("File not found `$path`");
        }
        return file_get_contents($path);
    }
}
