<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Exception;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;

class AddFooterLinksBlock implements DataPatchInterface
{
    const BLOCK_ID = 'c_central-footer-links';
    const BLOCK_NAME = 'C. Central Footer Links';
    const DATA_PATH = '/../../data/blocks/footer-links.html';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly BlockFactory $blockFactory,
        private readonly StoreManagerInterface $storeManager
    ) {}

    /**
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $filePath = __DIR__ . self::DATA_PATH;
        $content = $this->getHtmlContent($filePath);
        $store = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE);

        $innerBlock = $this->createFooterSocial($store);

        $content = $this->replaceBlockId($content, (string)$innerBlock->getId());

        $this->createBlock(self::BLOCK_ID, self::BLOCK_NAME, $content, (int)$store->getId());

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @throws Exception
     */
    private function getHtmlContent(string $path): string
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new Exception("File not found `$path`");
        }
        return file_get_contents($path);
    }

    /**
     * @throws LocalizedException
     */
    private function createBlock(string $identifier, string $title, string $content, int $storeId): BlockInterface
    {
        try {
            $block = $this->blockRepository->getById($identifier);
        } catch (LocalizedException $e) {
            $block = $this->blockFactory->create();
            $block->setIdentifier($identifier)
                ->setTitle($title);
        }

        $block->setContent($content);
        $block->setStoreId($storeId);
        return $this->blockRepository->save($block);
    }

    /**
     * @throws NoSuchEntityException
     * @throws Exception
     */
    private function createFooterSocial(StoreInterface $store): BlockInterface
    {
        $blockId = 'c_central-footer-social';
        $blockName = 'C. Central Footer Social';
        $blockPath = __DIR__ . '/../../data/blocks/footer-social.html';

        $blockContent = $this->getHtmlContent($blockPath);

        return $this->createBlock($blockId, $blockName, $blockContent, (int)$store->getId());
    }

    private function replaceBlockId(string $content, string $newBlockId): string
    {
        return preg_replace('/block_id="[^"]+"/', "block_id=\"$newBlockId\"", $content);
    }
}
