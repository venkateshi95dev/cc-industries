<?php

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class PDPStockMessagesConfiguration implements DataPatchInterface
{
    const OOS_NO_DROPSHIP_PATH = 'catalog/crimson/out_of_stock_no_dropship';
    const SIMPLE_INSTOCK_PATH = 'catalog/crimson/stock_ok';

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
            $this->configWriter->save(self::OOS_NO_DROPSHIP_PATH,
                '<span class="label label-important"><i class="fa fa-plus-circle"></i></span> Estimated to Ship within 2-3 weeks.',
                ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::SIMPLE_INSTOCK_PATH,
                '<span class="label label-success"><i class="fa fa-check-circle"></i>In Stock</span>',
                ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
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
