<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Exception;

class CreateCmsCategories implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly StoreManagerInterface $storeManager,
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly BlockFactory $blockFactory,
    ) {}

    /**
     * @throws CouldNotSaveException
     * @throws Exception
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        try {
            $store = $this->storeManager->getStore(
                CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE
            );

            $basePath = __DIR__ . '/../../data/blocks/megamenu';

            for ($index = 1; $index <= 12; $index++) {
                $identifier = sprintf('c_central_megamenu_%d', $index);
                $title = sprintf('C. Central MegaMenu %d', $index);
                $filePath = sprintf('%s/block-megamenu-%d.html', $basePath, $index);

                $content = $this->getContent($filePath);

                $this->createBlock(
                    $identifier,
                    $title,
                    $content,
                    (string)$store->getId()
                );
            }
        } catch (Exception $e) {
            throw $e;
        } finally {
            $this->moduleDataSetup->getConnection()->endSetup();
        }
    }

    /**
     * @throws LocalizedException
     */
    private function createBlock(
        string $identifier,
        string $title,
        string $content,
        string $storeId
    ): void {
        try {
            $block = $this->blockRepository->getById($identifier);
        } catch (LocalizedException $e) {
            $block = $this->blockFactory->create();
            $block->setIdentifier($identifier);
        }

        $block->setTitle($title)
            ->setContent($content)
            ->setIsActive(true)
            ->setStoreId((int)$storeId);

        $this->blockRepository->save($block);
    }

    /**
     * Reads the contents of the JSON/HTML file.
     * @throws Exception
     */
    private function getContent(string $path): string
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new Exception("File not found: {$path}");
        }

        return file_get_contents($path);
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
