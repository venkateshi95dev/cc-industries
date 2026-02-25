<?php

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class GeneralConfiguration implements DataPatchInterface
{
    const STORE_INFO_NAME_PATH = 'general/store_information/name';
    const STORE_INFO_PHONE_PATH = 'general/store_information/phone';
    const STORE_HOURS_PATH = 'general/store_information/hours';
    const STORE_COUNTRYID_PATH = 'general/store_information/country_id';
    const STORE_REGIONID_PATH = 'general/store_information/region_id';
    const STORE_POSTCODE_PATH = 'general/store_information/postcode';
    const STORE_CITY_PATH = 'general/store_information/city';
    const STORE_STREET1_PATH = 'general/store_information/street_line1';
    const STORE_STREET2_PATH = 'general/store_information/street_line2';

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
            $this->configWriter->save(self::STORE_INFO_NAME_PATH, 'Corvette Central', ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::STORE_INFO_PHONE_PATH, '800-345-4122', ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::STORE_HOURS_PATH, 'Monday through Friday 8:00 AM – 5:00 PM ET', ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::STORE_COUNTRYID_PATH, 'US', ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::STORE_REGIONID_PATH, '33', ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::STORE_POSTCODE_PATH, '49125', ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::STORE_CITY_PATH, 'Sawyer', ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::STORE_STREET1_PATH, 'PO Box 16', ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
            $this->configWriter->save(self::STORE_STREET2_PATH, '13550 Three Oaks Rd', ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
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
