<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Crimson\CorvetteCentral\Model\Service\CmsService;
use Exception;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateGrilleTeethUSAPageBanner implements DataPatchInterface
{
    const CMS_PAGE_ID = 'grilleteethusa';
    const CMS_PAGE_BANNER_PATH = '/../../data/cms-custom-banners/custom-banner-grille-teeth-usa-image-url-updated.html';

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

        $this->cmsService->createOrUpdateCustomBannerForCmsPage(
            self::CMS_PAGE_ID,
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
            UpdateImageUrlsInCmsPages::class
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
