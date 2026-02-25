<?php
declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;

class UpdateEmailHeaderLogo implements DataPatchInterface
{
    public function __construct(
        private WriterInterface $configWriter,
        private StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @return void
     */
    public function apply() : void
    {
        try {
            $store = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE);
            $storeId = (int)$store->getId();

            $this->configWriter->save(
                'design/email/logo',
                'stores/' . $storeId . '/logo-white.png',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
                $storeId
            );
        } catch (\Exception $e) {}
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [UpdateTransactionalEmailLogo::class];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
