<?php

namespace Crimson\CorvetteCentralOfflinePayment\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Crimson\CorvetteCentral\Setup\Patch\Data\CreateCorvetteCentralWebiste;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigCODPaymentMethodData implements DataPatchInterface
{
    const PATH_COD_TITLE = 'payment/cashondelivery/title';
    const PATH_COD_HIDDEN = 'payment/cashondelivery/hidden';
    const PATH_COD_ACTIVE = 'payment/cashondelivery/active';
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
            $this->configWriter->save(self::PATH_COD_TITLE, 'Offline-CC', ScopeInterface::SCOPE_WEBSITE, $ccWebsiteId);
            $this->configWriter->save(self::PATH_COD_ACTIVE, 1, ScopeInterface::SCOPE_WEBSITE, $ccWebsiteId);
            $this->configWriter->save(self::PATH_COD_HIDDEN, 1, ScopeInterface::SCOPE_WEBSITE, $ccWebsiteId);
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
