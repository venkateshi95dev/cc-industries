<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Crimson\CokerWV\Api\WVStoreInterface;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class GenerateCokerWVGeneralConfig implements DataPatchInterface
{
    const CSV_FILE_PATH = '/import/coker_zip_data_migration/coker_general_config.csv';

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

        $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH;
        $csvReader = $this->csvReaderFactory->create();
        $rows = $csvReader->getData($csvPath);

        foreach ($rows as $num => $data) {
            if ($num > 0) {
                $scopeId = 0;
                if ($data[0] == '2')
                    $scopeId = $cokerWebsiteId;
                elseif ($data[0] == '3')
                    $scopeId = $WVWebsiteId;
                $path = $data[1];
                $value = $data[2] != 'NULL' ? $data[2] : null;
                if ($scopeId > 0) {
                    $this->configWriter->save($path, $value, 'websites', $scopeId);
                } else {
                    // If scopeId = 0, write separated config for each website instead of overwrite default config

                    $this->configWriter->save($path, $value, 'websites', $cokerWebsiteId);
                    $this->configWriter->save($path, $value, 'websites', $WVWebsiteId);
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
