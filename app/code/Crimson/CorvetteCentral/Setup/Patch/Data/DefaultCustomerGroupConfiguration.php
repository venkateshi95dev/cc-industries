<?php

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class DefaultCustomerGroupConfiguration implements DataPatchInterface
{
    const DEFAULT_CUSTOMER_GROUP_PATH = 'customer/create_account/default_group';

    public function __construct(
        private readonly WriterInterface $configWriter,
        private GroupCollectionFactory      $groupCollectionFactory,
        private readonly StoreManagerInterface $storeManager
    )
    {
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        try {
            $ccWebsiteId = $this->storeManager->getWebsite(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_CODE)->getId();
            $newGroupId = (int)$this->groupCollectionFactory->create()
                ->addFieldToFilter('customer_group_code', 'CC-RETAIL')
                ->getFirstItem()
                ->getId();
            $this->configWriter->save(self::DEFAULT_CUSTOMER_GROUP_PATH, $newGroupId, ScopeInterface::SCOPE_WEBSITE, $ccWebsiteId);
            $this->configWriter->save(self::DEFAULT_CUSTOMER_GROUP_PATH, $newGroupId, ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
        } catch (\Exception $exception) {
            return;
        }
    }


    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [
            CreateCorvetteCentralWebiste::class
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
