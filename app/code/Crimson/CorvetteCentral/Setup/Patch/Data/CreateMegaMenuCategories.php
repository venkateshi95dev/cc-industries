<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magedelight\Megamenu\Model\ResourceModel\Menu as MenuResource;
use Magedelight\Megamenu\Model\ResourceModel\MenuItems as MenuItemsResource;
use Magedelight\Megamenu\Model\Menu as MenuModel;
use Magedelight\Megamenu\Model\MenuItems as MenuItemsModel;
use Exception;

class CreateMegaMenuCategories implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly StoreManagerInterface $storeManager,
        private readonly MenuResource $menuResource,
        private readonly MenuItemsResource $menuItemsResource,
        private readonly MenuModel $menuModel,
        private readonly MenuItemsModel $menuItemsModel,
        private readonly WriterInterface $configWriter,
    ) {}

    /**
     * @throws Exception
     */
    public function apply(): void
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        try {
            $store = $this->storeManager->getStore(
                CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE
            );
            $storeId = (int)$store->getId();

            $menuId = $this->createMenuFromJson($storeId);

            $this->importMenuItemsFromJson($menuId);
        } finally {
            $connection->endSetup();
        }
    }

    private function createMenuFromJson(int $storeId): int
    {
        $menuFile = __DIR__ . '/../../data/megamenu_menus_cms.json';
        $menuData = $this->getDecodedJson($menuFile);

        $menuModel = clone $this->menuModel;

        unset($menuData['menu_id']);

        $menuModel->setData($menuData);
        $this->menuResource->save($menuModel);

        $menuId = (int)$menuModel->getId();
        if (!$menuId) {
            throw new Exception('Failed to create Mega Menu');
        }

        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('megamenu_menus_store'),
            [
                'menu_id'  => $menuId,
                'store_id' => $storeId
            ]
        );

        $this->configWriter->save(
            'magedelight/general/primary_menu',
            (string)$menuId,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );

        return $menuId;
    }

    private function importMenuItemsFromJson(int $menuId): void
    {
        $itemsFile = __DIR__ . '/../../data/megamenu_menu_items_cms.json';
        $items = $this->getDecodedJson($itemsFile);

        foreach ($items as $itemData) {
            $model = clone $this->menuItemsModel;

            unset(
                $itemData['item_id'],
                $itemData['menu_id']
            );

            $itemData['menu_id'] = $menuId;

            $model->setData($itemData);
            $this->menuItemsResource->save($model);
        }
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
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
