<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Crimson\CokerWV\Api\WVStoreInterface;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class GenerateCokerWVDesignConfig implements DataPatchInterface
{
    const CSV_FILE_PATH = '/import/coker_zip_data_migration/coker_wv_design_config.csv';

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

        $cokerTireStoreId        = $this->storeManager->getStore(CokerStoreInterface::COKER_STORE_CODE)->getId();
        $cokerTireDefaultStoreId = $this->storeManager->getStore(CokerStoreInterface::COKER_DEFAULT_STORE_CODE)->getId();
        $wvStoreId               = $this->storeManager->getStore(WVStoreInterface::WV_STORE_CODE)->getId();


        $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH;
        $csvReader = $this->csvReaderFactory->create();
        $rows = $csvReader->getData($csvPath);

        foreach ($rows as $num => $data) {
            if ($num > 0) {
                if ($data[0] == '1')
                    $scopeId = $cokerTireDefaultStoreId;
                elseif ($data[0] == '2')
                    $scopeId = $cokerTireStoreId;
                else
                    $scopeId = $wvStoreId;
                $path = $data[1];
                $value = $data[2] != 'NULL' ? str_replace('&comma;',',',$data[2]) : null;
                if ($scopeId > 0) {
                    $this->configWriter->save($path, $value, 'stores', $scopeId);
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
