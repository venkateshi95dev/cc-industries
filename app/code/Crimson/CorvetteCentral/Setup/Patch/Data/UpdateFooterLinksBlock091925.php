<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Crimson\CorvetteCentral\Model\Service\CmsService;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use Exception;

class UpdateFooterLinksBlock091925 implements DataPatchInterface
{
    const BLOCK_NAME = 'C. Central Footer Links';
    const BLOCK_ID = 'c_central-footer-links';
    const DATA_PATH = '/../../data/blocks/footer-links-updated.html';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CmsService $cmsService,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        try {
            $this->cmsService->createOrUpdateBlock(
                self::BLOCK_ID,
                self::BLOCK_NAME,
                __DIR__ . self::DATA_PATH,
                CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE
            );
        } catch (Exception $e) {
            $this->logger->error("Patch UpdateFooterLinksBlock091925: Wasn't able to update the footer links");
            $this->logger->error($e->getMessage());
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [
            UpdateFooterLinksBlock070725::class
        ];
    }
}
