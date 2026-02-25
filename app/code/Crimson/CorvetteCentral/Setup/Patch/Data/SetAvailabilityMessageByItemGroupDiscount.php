<?php
declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\Catalog\Model\Config;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class SetAvailabilityMessageByItemGroupDiscount implements DataPatchInterface
{
    public function __construct(
        private readonly WriterInterface $configWriter,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        try {
            $value = '{"_1761082718425_425":{"item_discount_group":"DISC","message":"Temporarily Discontinued"},"_1761082722352_352":{"item_discount_group":"010","message":"This product cannot be ordered online"}}';
            $ccWebsiteId = $this->storeManager->getWebsite(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_CODE)->getId();
            $this->configWriter->save(Config::XPATH_AVAILABILITY_MESSAGES, $value, ScopeInterface::SCOPE_WEBSITE, $ccWebsiteId);
            $this->configWriter->save(Config::XPATH_AVAILABILITY_MESSAGES, $value, ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
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
