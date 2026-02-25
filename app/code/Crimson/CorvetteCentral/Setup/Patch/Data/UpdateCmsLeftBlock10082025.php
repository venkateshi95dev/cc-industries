<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Crimson\CorvetteCentral\Model\Service\CmsService;
use Exception;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateCmsLeftBlock10082025 implements DataPatchInterface
{
    const BLOCK_ID = 'cms_left_block';
    const BLOCK_NAME = 'C. Central CMS Left Block';
    const DATA_PATH = '/../../data/blocks/cc-cms-left-block.html';

    /**
     * Create Header Links block constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup,
     * @param CmsService $cmsService
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CmsService               $cmsService,
    ) {
    }

    /**
     * Do Upgrade.
     *
     * @return void
     * @throws Exception
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->cmsService->createOrUpdateBlock(
            self::BLOCK_ID,
            self::BLOCK_NAME,
            __DIR__ . self::DATA_PATH,
            CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [AddCmsLeftBlock::class];
    }
}
