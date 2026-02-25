<?php

declare(strict_types=1);

namespace Crimson\Sales\Setup\Patch\Data;

use Exception;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Psr\Log\LoggerInterface;

class AddStartAReturnToFooterLinks implements DataPatchInterface
{
    const FOOTER_BLOCK_ID = 6;
    const FOOTER_LINKS_BLOCK_CONTENT = '/../../data/footer-links.html';

    public function __construct(
        protected ModuleDataSetupInterface $moduleDataSetup,
        protected BlockRepositoryInterface $blockRepository,
        protected StoreRepositoryInterface $storeRepository,
        protected State                    $appState,
        protected LoggerInterface          $logger
    ) {}

    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [CreateStartAReturnPage::class];
    }

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();

        try {
            $this->appState->setAreaCode(Area::AREA_FRONTEND);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        try {
            $block = $this->blockRepository->getById(self::FOOTER_BLOCK_ID);
            if (!$block || !$block->getId()) {
                throw new Exception('Block not found!');
            }

            $blockContent = $this->getFileContent(__DIR__ . self::FOOTER_LINKS_BLOCK_CONTENT);
            $block->setContent($blockContent);

            $this->blockRepository->save($block);

        } catch (Exception $e) {
            $this->logger->error("Wasn't able to apply Data Patch 'AddStartAReturnToFooterLinks'.");
            $this->logger->error($e->getMessage());
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @param string $path
     * @return string
     * @throws Exception
     */
    protected function getFileContent(string $path): string
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new Exception("File not found `$path`");
        }
        return (string)file_get_contents($path);
    }
}
