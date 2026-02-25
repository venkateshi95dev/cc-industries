<?php
declare(strict_types=1);

namespace Crimson\CorvetteCentralImport\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Framework\App\Area as AppArea;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class UpdateCustomerRetailCustomerGroup implements DataPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface    $moduleDataSetup,
        private CustomerRepositoryInterface $customerRepository,
        private SearchCriteriaBuilder       $searchCriteriaBuilder,
        private GroupCollectionFactory      $groupCollectionFactory,
        private AppState                    $appState,
        private StoreManagerInterface $storeManager,
        private LoggerInterface             $logger
    )
    {
    }

    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();
        try {
            $this->appState->emulateAreaCode(
                AppArea::AREA_ADMINHTML,
                function () {
                    $ccStoreId = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();
                    $oldGroupId = (int)$this->groupCollectionFactory->create()
                        ->addFieldToFilter('customer_group_code', ['in'=>['Default (Retail)','Retail']])
                        ->getFirstItem()
                        ->getId();

                    $newGroupId = (int)$this->groupCollectionFactory->create()
                        ->addFieldToFilter('customer_group_code', 'CC-RETAIL')
                        ->getFirstItem()
                        ->getId();

                    if ($oldGroupId === 0 || $newGroupId === 0) {
                        throw new \RuntimeException(
                            sprintf(
                                'Customer group not found: Default (Retail)=%s, CC-RETAIL=%s',
                                $oldGroupId,
                                $newGroupId
                            )
                        );
                    }

                    $conn    = $this->moduleDataSetup->getConnection();
                    $cp     = $this->moduleDataSetup->getTable('customer_entity');
                    $sql = "UPDATE {$cp}
                    SET group_id  = {$newGroupId}
                    WHERE group_id ={$oldGroupId} AND store_id={$ccStoreId}";
                    $conn->query($sql);
                });
        } catch (\Throwable $e) {
            $this->log('Can not save customers retail group '. $e->getMessage());
        } finally {
            $this->moduleDataSetup->getConnection()->endSetup();
        }
    }

    private function log(string $msg): void
    {
        if ($this->logger) {
            $this->logger->info('[Crimson][UpdateCustomerRetailCustomerGroup] ' . $msg);
        }
    }

    public static function getDependencies(): array
    {
        return [UpdateCompanyRetailCustomerGroup::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
