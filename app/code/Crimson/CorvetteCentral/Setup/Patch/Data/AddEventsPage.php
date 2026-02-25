<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Crimson\CorvetteCentral\Model\Service\CmsService;
use Exception;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddEventsPage implements DataPatchInterface
{
    const CMS_PAGE_TITLE = 'Corvette Events & Trade Shows';
    const CMS_PAGE_ID = 'show/schedule';
    const CMS_PAGE_KEYWORDS = 'Corvette parts, Corvette accessories, Corvette restoration parts, Corvette performance parts, Corvette interior accessories, Corvette exhaust systems.';
    const CMS_PAGE_DATA_PATH = '/../../data/pages/events.html';
    const CMS_PAGE_BANNER_PATH = '/../../data/cms-custom-banners/custom-banner-events.html';
    const CMS_LAYOUT_UPDATE = 'full-width-with-left-sidebar';
    const CMS_TEMPLATE_META_TITLE = 'Events Schedule | Corvette Central';
    const CMS_TEMPLATE_META_DESCRIPTION = 'Find dates for Corvette Clubs and our booth numbers at upcoming trade shows';

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CmsService $cmsService
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CmsService               $cmsService,
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
            CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE,
            __DIR__ . self::CMS_PAGE_BANNER_PATH
        );

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [
            AddCmsLeftBlock::class
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
