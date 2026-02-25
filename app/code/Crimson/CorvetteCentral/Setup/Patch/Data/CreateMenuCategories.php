<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magedelight\Megamenu\Model\ResourceModel\Menu as MenuResource;
use Magedelight\Megamenu\Model\ResourceModel\MenuItems as MenuItemsResource;
use Magedelight\Megamenu\Model\Menu as MenuModel;
use Magedelight\Megamenu\Model\MenuItems as MenuItemsModel;
use Exception;

class CreateMenuCategories implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CategoryFactory $categoryFactory,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly WebsiteRepositoryInterface $websiteRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly BlockFactory $blockFactory,
        private readonly MenuResource $menuResource,
        private readonly MenuItemsResource $menuItemsResource,
        private readonly MenuModel $menuModel,
        private readonly MenuItemsModel $menuItemsModel,
        private readonly WriterInterface $configWriter,
        private readonly Json $jsonSerializer
    ) {}

    /**
     * @throws CouldNotSaveException
     * @throws Exception
     */
    public function apply(): void
    {

        $this->moduleDataSetup->getConnection()->startSetup();

        try {
            $filePath = __DIR__ . '/../../data/categories.json';
            $jsonData = $this->getContent($filePath);
            $categoriesData = $this->jsonSerializer->unserialize($jsonData);
            $store = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE);
            $menuCategories = $categoriesData['menu'] ?? [];

            $parentCategoryId = $this->getCorvetteCentralRootCategoryId();
            $menuId = $this->createMegaMenu($store->getId());
            $index = 1;

            foreach ($menuCategories as $topLevel) {
                $categoryName = $topLevel['name'] ?? '';

                $this->createCategoryLevel(
                    $categoryName,
                    $topLevel['subcategories'] ?? [],
                    $parentCategoryId,
                    $menuId,
                    $index
                );

                $this->createTitleBlock($store, $categoryName, $index);
                $this->createContentBlock($store, $categoryName, $index);
                $index++;
            }
        } catch (Exception $e) {
            throw new Exception('Failed to create categories: ' . $e->getMessage());
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Retrieves the root category ID assigned to the "Corvette Central" website.
     * @throws Exception
     */
    private function getCorvetteCentralRootCategoryId(): int
    {
        try {
            $website = $this->websiteRepository->get(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_CODE);
            $storeGroup = $website->getDefaultGroup();
            return (int) $storeGroup->getRootCategoryId();
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve the Corvette Central root category ID: ' . $e->getMessage());
        }
    }

    /**
     * Creates one top-level category, then recurses through its subcategories.
     * @throws CouldNotSaveException
     * @throws Exception
     */
    private function createCategoryLevel(string $categoryName, array $subcategories, int $parentCategoryId, int $menuId, int $index): void
    {
        if (!$categoryName) {
            return;
        }

        $category = $this->categoryFactory->create();
        $category->setName($categoryName)
            ->setUrlKey($this->getPathUrl($categoryName))
            ->setMetaKeywords($categoryName)
            ->setParentId($parentCategoryId)
            ->setDisplayMode(Category::DM_PAGE)
            ->setIsActive(true)
            ->setIncludeInMenu(true);

        $category = $this->categoryRepository->save($category);

        $this->createMegaMenuItems((int)$category->getId(), $categoryName, $menuId, $index);

        foreach ($subcategories as $subCategoryName) {
            if (!$subCategoryName) {
                continue;
            }

            $subCategory = $this->categoryFactory->create();
            $subCategory->setName($subCategoryName)
                ->setUrlKey($this->getPathUrl($subCategoryName))
                ->setMetaKeywords($subCategoryName)
                ->setParentId($category->getId())
                ->setIsActive(true)
                ->setIncludeInMenu(true);

            $this->categoryRepository->save($subCategory);
        }
    }

    /**
     * @throws Exception
     */
    private function createTitleBlock(StoreInterface $store, string $name, int $index): void
    {
        $blockId = sprintf('c_central_megamenu_%d_title', $index);
        $blockName = sprintf('C. Central MegaMenu %s Title ', $this->getOrdinalSuffix($index)). $name;
        $blockPath = __DIR__ . '/../../data/blocks/megamenu-title.html';

        $blockContent = $this->getContent($blockPath);

        $formattedTitle = $this->formatCategoryTitle($name);
        $blockContent = preg_replace('/%1%/', $formattedTitle, $blockContent);

        $this->createBlock($blockId, $blockName, $blockContent, (string)$store->getId());
    }

    /**
     * @throws Exception
     */
    private function createContentBlock(StoreInterface $store, string $name, int $index): void
    {
        $blockId = sprintf('c_central_megamenu_%d_left', $index);
        $blockName = sprintf('C. Central MegaMenu %s Left ', $this->getOrdinalSuffix($index)). $name;
        $blockPath = __DIR__ . '/../../data/blocks/megamenu-left.html';

        $blockContent = $this->getContent($blockPath);

        $this->createBlock($blockId, $blockName, $blockContent, (string)$store->getId());
    }

    /**
     * Returns the ordinal suffix for a given number.
     */
    private function getOrdinalSuffix(int $number): string
    {
        if ($number % 100 >= 11 && $number % 100 <= 13) {
            return $number . 'th';
        }

        return match ($number % 10) {
            1 => $number . 'st',
            2 => $number . 'nd',
            3 => $number . 'rd',
            default => $number . 'th',
        };
    }

    /**
     * Formats category titles.
     */
    private function formatCategoryTitle(string $categoryTitle): string
    {
        if (!preg_match('/C(\d) \((\d{2})-(\d{2})\)/', $categoryTitle, $matches)) {
            return $categoryTitle;
        }

        $generation = $matches[1];
        $startYear = (int)$matches[2];
        $endYear = (int)$matches[3];

        $currentYear = (int) date('y');

        $fullStartYear = ($startYear <= $currentYear ? 2000 : 1900) + $startYear;
        $fullEndYear = ($endYear <= $currentYear ? 2000 : 1900) + $endYear;

        return sprintf('C%d (%d-%d)', $generation, $fullStartYear, $fullEndYear);
    }

    /**
     * @throws LocalizedException
     */
    private function createBlock(string $identifier, string $title, string $content, string $storeId): void
    {
        try {
            $block = $this->blockRepository->getById($identifier);
        } catch (LocalizedException $e) {
            $block = $this->blockFactory->create();
            $block->setIdentifier($identifier)
                ->setTitle($title);
        }

        $block->setContent($content)->setTitle($title);
        $block->setStoreId((int)$storeId);
        $this->blockRepository->save($block);
    }

    /**
     * @throws LocalizedException
     */
    private function createMegaMenu($storeId): int
    {
        $connection = $this->moduleDataSetup->getConnection();

        $menuFilePath = __DIR__ . '/../../data/megamenu_menus.json';
        $menuData = $this->getDecodedJson($menuFilePath);

        $menuData = array_filter($menuData, fn($value) => $value !== null);
        $menuModel = clone $this->menuModel;
        $menuModel->setData($menuData);

        $this->menuResource->save($menuModel);
        $menuId = (int) $menuModel->getId();

        if ($menuId) {
            $connection->insert(
                $this->moduleDataSetup->getTable('megamenu_menus_store'),
                [
                    'menu_id'  => $menuId,
                    'store_id' => $storeId
                ]
            );
        }

        $this->configWriter->save('magedelight/general/primary_menu', $menuId, ScopeInterface::SCOPE_STORES, $storeId);

        return $menuId;
    }

    /**
     * @throws LocalizedException
     */
    private function createMegaMenuItems(int $categoryEntityId, string $menuName, int $menuId, int $index): void
    {
        $menuItemsFilePath = __DIR__ . '/../../data/megamenu_menu_items.json';

        $itemData = $this->getDecodedJson($menuItemsFilePath);
        if (empty($itemData)) {
            throw new LocalizedException(__('Invalid menu item template data in %1', $menuItemsFilePath));
        }

        $itemData = array_filter($itemData, fn($value) => $value !== null);

        $menuItemModel = clone $this->menuItemsModel;
        $menuItemModel->setData($itemData);
        $menuItemModel->setData('item_name', $menuName);
        $menuItemModel->setData('sort_order', $index);
        $menuItemModel->setData('menu_id', $menuId);
        $menuItemModel->setData('object_id', $categoryEntityId);

        if (!empty($itemData['category_columns']) && is_string($itemData['category_columns'])) {
            $categoryColumns = json_decode($itemData['category_columns'], true);

            if (is_array($categoryColumns)) {
                foreach ($categoryColumns as &$column) {
                    if ($column['type'] === 'header') {
                        $column['value'] = sprintf('c_central_megamenu_%d_title', $index);
                    }
                    if ($column['type'] === 'left') {
                        $column['value'] = sprintf('c_central_megamenu_%d_left', $index);
                    }
                }
                $menuItemModel->setData('category_columns', json_encode($categoryColumns, JSON_UNESCAPED_UNICODE));
            }
        }

        $this->menuItemsResource->save($menuItemModel);
    }


    /**
     * Converts a category name to a URL-safe string.
     * @throws Exception
     */
    private function getPathUrl(string $pathUrl): string
    {
        if (!$pathUrl) {
            throw new Exception("Category URL name cannot be empty.");
        }

        $search = [' ', '/', '"', '\''];
        $replace = ['-', '-', '', ''];

        return str_replace($search, $replace, strtolower($pathUrl));
    }

    /**
     * @throws LocalizedException
     * @throws Exception
     */
    private function getDecodedJson(string $filePath): array
    {
        $jsonData = $this->getContent($filePath);

        try {
            $decodedData = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            throw new LocalizedException(__('Invalid JSON format in %1: %2', $filePath, $e->getMessage()));
        }

        return is_array($decodedData) ? $decodedData : [];
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
        return [
            CreateCorvetteCentralWebiste::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
