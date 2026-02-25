<?php

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;
use Crimson\CorvetteCentral\Setup\Patch\Data\CreateCorvetteCentralCategory;

class CreateCorvetteCentralWebiste implements DataPatchInterface
{

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly WebsiteFactory           $websiteFactory,
        private readonly GroupFactory             $groupFactory,
        private readonly StoreFactory             $storeFactory,
        private readonly AppState                 $appState,
        private readonly CategoryCollectionFactory $categoryCollectionFactory
    ) {}

    /**
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, function () {
            $website = $this->websiteFactory->create()->addData([
                'code' => CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_CODE,
                'name' => CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_NAME,
                'sort_order' => 6
            ]);
            $website->save();

            $storeGroup = $this->groupFactory->create()->addData([
                'website_id' => $website->getId(),
                'code' => CorvetteCentralStoreInterface::CORVETTE_CENTRAL_GROUP_CODE,
                'name' => CorvetteCentralStoreInterface::CORVETTE_CENTRAL_GROUP_NAME,
                'root_category_id' => $this->getCategory(),
            ]);
            $storeGroup->save();

            //Store
            $storeCoker = $this->storeFactory->create()->addData([
                'website_id' => $website->getId(),
                'group_id' => $storeGroup->getId(),
                'code' => CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE,
                'name' => CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_NAME,
                'is_active' => 1,
            ]);
            $storeCoker->save();
        });

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCategory(): int
    {
        $collection = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', CreateCorvetteCentralCategory::CORVETTE_CENTRAL_ROOT_CATEGORY_NAME)
            ->setPageSize(1);

        return (int) $collection->getFirstItem()->getId();
    }

    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [
            CreateCorvetteCentralCategory::class
        ];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
