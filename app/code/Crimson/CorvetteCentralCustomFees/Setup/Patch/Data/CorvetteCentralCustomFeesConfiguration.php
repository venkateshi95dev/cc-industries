<?php

namespace Crimson\CorvetteCentralCustomFees\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Crimson\CorvetteCentral\Setup\Patch\Data\CreateCorvetteCentralWebiste;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class CorvetteCentralCustomFeesConfiguration implements DataPatchInterface
{
    const CUSTOM_FEES_CORE_CHARGE_ENABLE = 'custom_fees/core_charge/enabled';
    const CUSTOM_FEES_CRATE_FEE_ENABLE = 'custom_fees/crate_fee/enabled';
    const CUSTOM_FEES_FREIGHT_FEE_ENABLE = 'custom_fees/freight_fee/enabled';
    const CUSTOM_FEES_CANADIAN_FREIGHT_FEE_ENABLE = 'custom_fees/canadian_freight/enabled';
    const CUSTOM_FEES_TRUCK_FREIGHT_FEE_ENABLE = 'custom_fees/truck_freight/enabled';
    const CUSTOM_FEES_DROPSHIP_FEE_ENABLE = 'custom_fees/dropship_fee/enabled';

    public function __construct(
        private readonly WriterInterface $configWriter,
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
            $this->configWriter->save(self::CUSTOM_FEES_CORE_CHARGE_ENABLE, 1, ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::CUSTOM_FEES_CRATE_FEE_ENABLE, 1, ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::CUSTOM_FEES_FREIGHT_FEE_ENABLE, 1, ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::CUSTOM_FEES_CANADIAN_FREIGHT_FEE_ENABLE, 1, ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::CUSTOM_FEES_TRUCK_FREIGHT_FEE_ENABLE, 1, ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::CUSTOM_FEES_DROPSHIP_FEE_ENABLE, 1, ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
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
