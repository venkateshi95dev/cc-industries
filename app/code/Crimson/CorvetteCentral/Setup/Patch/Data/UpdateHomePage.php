<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Crimson\CorvetteCentral\Model\Service\CmsService;
use Exception;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateHomePage implements DataPatchInterface
{
    const CMS_PAGE_TITLE = 'Corvette Central Home';
    const CMS_PAGE_ID = 'corvette_central_home';
    const CMS_PAGE_KEYWORDS = 'Corvette parts, Corvette accessories, Corvette restoration parts, Corvette performance parts, Corvette interior accessories, Corvette exhaust systems.';
    const CMS_PAGE_DATA_PATH = '/../../data/pages/home-updated.html';
    const CMS_LAYOUT_UPDATE = 'full-width-cms-base';
    const CMS_TEMPLATE_META_TITLE = 'Corvette Parts & Accessory Supplier | Corvette Central';
    const CMS_TEMPLATE_META_DESCRIPTION = 'Corvette parts supplier of restoration and performance Corvette parts for all generations.  America\'s #1 leading supplier and manufacturer of Corvette parts.';

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CmsService $cmsService
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CmsService $cmsService
    ) {
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();

        $this->cmsService->createOrUpdatePage(
            self::CMS_PAGE_ID,
            self::CMS_PAGE_TITLE,
            __DIR__ . self::CMS_PAGE_DATA_PATH,
            self::CMS_LAYOUT_UPDATE,
            self::CMS_TEMPLATE_META_TITLE,
            self::CMS_PAGE_KEYWORDS,
            self::CMS_TEMPLATE_META_DESCRIPTION,
            CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE
        );

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [
            MakeHomePageFullWidth::class
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
