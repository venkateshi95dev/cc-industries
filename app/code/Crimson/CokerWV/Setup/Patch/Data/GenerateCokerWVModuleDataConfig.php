<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Crimson\CokerWV\Api\WVStoreInterface;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class GenerateCokerWVModuleDataConfig implements DataPatchInterface
{
    const CSV_FILE_PATH = '/import/coker_zip_data_migration/coker_wv_module_data_config.csv';

    public function __construct(
        private readonly ModuleDataSetupInterface                              $moduleDataSetup,
        private readonly ScopeConfigInterface                                  $scopeConfig,
        private readonly \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        private readonly \Magento\Framework\File\CsvFactory                    $csvReaderFactory,
        private readonly DirectoryList                                         $directoryList,
        private readonly \Magento\Store\Model\StoreManagerInterface            $storeManager
    )
    {
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {

        $cokerWebsiteId = $this->storeManager->getWebsite(CokerStoreInterface::COKER_WEBSITE_CODE)->getId();
        $WVWebsiteId = $this->storeManager->getWebsite(WVStoreInterface::WV_WEBSITE_CODE)->getId();
        $cokerTireStoreId = $this->storeManager->getStore(CokerStoreInterface::COKER_STORE_CODE)->getId();

        $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . self::CSV_FILE_PATH;
        $csvReader = $this->csvReaderFactory->create();
        $rows = $csvReader->getData($csvPath);

        foreach ($rows as $num => $data) {
            if ($num > 0) {
                $configData = [
                    'scope' => $data[0],
                    'scope_id' => $data[1],
                    'path' => $data[2],
                    'value' => $data[3] != 'NULL' ? str_replace('&comma;', ',', $data[3]) : null
                ];
                $scopeId = 0;
                $scope = 'websites';
                if ($configData['scope'] == 'website') {
                    if ($configData['scope_id'] == '1')
                        $scopeId = $cokerWebsiteId;
                    elseif ($configData['scope_id'] == '11')
                        $scopeId = $WVWebsiteId;
                } elseif ($configData['scope'] == 'stores') {
                    $scope = 'stores';
                    $scopeId = $cokerTireStoreId;
                }
                if ($scopeId > 0) {
                    $this->configWriter->save($configData['path'], $configData['value'], $scope, $scopeId);
                } else {
                    // If scopeId = 0, write separated config for each website instead of overwrite default config
                    $this->configWriter->save($configData['path'], $configData['value'], 'websites', $cokerWebsiteId);
                    $this->configWriter->save($configData['path'], $configData['value'], 'websites', $WVWebsiteId);
                }
            }
        }
    }


    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [
            CreateCokerWebiste::class,
            CreateWVWebiste::class
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
