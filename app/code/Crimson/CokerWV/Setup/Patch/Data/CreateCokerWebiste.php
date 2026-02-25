<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;

class CreateCokerWebiste implements DataPatchInterface
{

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly WebsiteFactory           $websiteFactory,
        private readonly GroupFactory             $groupFactory,
        private readonly StoreFactory             $storeFactory,
        private readonly AppState                 $appState,
        private readonly CategoryCollectionFactory $categoryCollectionFactory
    )
    {}

    /**
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, function () {
            $website = $this->websiteFactory->create()->addData([
                'code' => CokerStoreInterface::COKER_WEBSITE_CODE,
                'name' => CokerStoreInterface::COKER_WEBSITE_NAME,
                'sort_order' => 3
            ]);
            $website->save();

            $storeGroup = $this->groupFactory->create()->addData([
                'website_id' => $website->getId(),
                'code' => CokerStoreInterface::COKER_GROUP_CODE,
                'name' => CokerStoreInterface::COKER_GROUP_NAME,
                'root_category_id' => $this->getCategory(),
            ]);
            $storeGroup->save();

            //Stores
            $storeCoker = $this->storeFactory->create()->addData([
                'website_id' => $website->getId(),
                'group_id' => $storeGroup->getId(),
                'code' => CokerStoreInterface::COKER_STORE_CODE,
                'name' => CokerStoreInterface::COKER_STORE_NAME,
                'is_active' => 1,
            ]);
            $storeCoker->save();

            $storeCokerDefault = $this->storeFactory->create()->addData([
                'website_id' => $website->getId(),
                'group_id' => $storeGroup->getId(),
                'code' => CokerStoreInterface::COKER_DEFAULT_STORE_CODE,
                'name' => CokerStoreInterface::COKER_DEFAULT_STORE_NAME,
                'is_active' => 1,
            ]);
            $storeCokerDefault->save();
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
            ->addAttributeToFilter('name', CreateCokerCategory::COKER_ROOT_CATEGORY_NAME)
            ->setPageSize(1);

        return (int) $collection->getFirstItem()->getId();
    }

    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [
            CreateCokerCategory::class
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
