<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Exception;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Store\Model\StoreManagerInterface;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;

class AddHeaderLinksBlock implements DataPatchInterface
{
    const BLOCK_ID = 'c_central-header-links';
    const BLOCK_NAME = 'C. Central Header Links';
    const DATA_PATH = '/../../data/blocks/header-links.html';

    /**
     * Create Header Links block constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param BlockRepositoryInterface $blockRepository
     * @param BlockFactory $blockFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly BlockFactory             $blockFactory,
        private readonly StoreManagerInterface $storeManager
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

        $filePath = __DIR__ . self::DATA_PATH;
        $content = $this->getHtmlContent($filePath);
        $store = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE);

        try {
            $block = $this->blockRepository->getById(self::BLOCK_ID);
        } catch (LocalizedException $e) {
            /** @var BlockInterface $block */
            $block = $this->blockFactory->create();
            $block->setIdentifier(self::BLOCK_ID)
                ->setTitle(self::BLOCK_NAME);
        }

        $block->setContent($content);
        $block->setStoreId($store->getId());
        $this->blockRepository->save($block);

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
        return [];
    }

    /**
     * @param string $path
     * @return false|string
     * @throws Exception
     */
    private function getHtmlContent(string $path): false|string
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new Exception("File not found `$path`");
        }
        return file_get_contents($path);
    }
}
