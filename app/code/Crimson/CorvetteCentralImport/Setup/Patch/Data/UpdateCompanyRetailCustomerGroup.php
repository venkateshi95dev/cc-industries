<?php
declare(strict_types=1);

namespace Crimson\CorvetteCentralImport\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area as AppArea;

class UpdateCompanyRetailCustomerGroup implements DataPatchInterface
{

    public function __construct(
        private ModuleDataSetupInterface   $moduleDataSetup,
        private GroupCollectionFactory     $groupCollectionFactory,
        private AppState                   $appState,
        private LoggerInterface         $logger
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
                    $groupCollection = $this->groupCollectionFactory->create();
                    $oldGroupId = (int)$groupCollection
                        ->addFieldToFilter('customer_group_code', ['in'=>['Default (Retail)','Retail']])
                        ->getFirstItem()
                        ->getId();

                    $newGroupId = (int)$this->groupCollectionFactory->create()
                        ->addFieldToFilter('customer_group_code', 'CC-RETAIL')
                        ->getFirstItem()
                        ->getId();

                    if (!$oldGroupId || !$newGroupId) {
                        throw new \RuntimeException(
                            sprintf(
                                'Customer group code not found. Old=%s, New=%s',
                                $oldGroupId ?: 'null',
                                $newGroupId ?: 'null'
                            )
                        );
                    }

                    $conn    = $this->moduleDataSetup->getConnection();
                    $cp     = $this->moduleDataSetup->getTable('company');
                    $sql = "UPDATE {$cp}
                            SET customer_group_id = {$newGroupId}
                            WHERE customer_group_id={$oldGroupId}";
                    $conn->query($sql);
                }
            );
        } catch (\Throwable $e) {
            $this->log('Can not save company retail group' . $e->getMessage());
        } finally {
            $this->moduleDataSetup->getConnection()->endSetup();
        }
    }

    private function log(string $msg): void
    {
        if ($this->logger) {
            $this->logger->info('[Crimson][UpdateCompanyRetailCustomerGroup] ' . $msg);
        }
    }

    public
    static function getDependencies(): array
    {
        return [];
    }

    public
    function getAliases(): array
    {
        return [];
    }
}
