<?php

namespace Crimson\CokerWV\Service;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;

class ImportAmastyFinder
{

    const SQL_FOLDER_PATH = '/import/coker_zip_data_migration/amasty_finder/';
    const SQL_FILES = [
        'finder',
        'dropdown',
        'finder_value',
        'map'
    ];

    public function __construct(
        private readonly DirectoryList    $directoryList,
        private readonly DeploymentConfig $deploymentConfig
    )
    {
    }

    public function execute(): array
    {
        $result['message'] = "Assigned stores and related product data for finder entities.";
        try {
            $this->insertData();
        }
        catch (\Exception $e){
            $result['message'] = __('Can not insert finder data '.$e->getMessage());
        }
        return $result;
    }

    private function insertData()
    {
        $dbConnection = new \mysqli(
            $this->deploymentConfig->get('db/connection/default/host'),
            $this->deploymentConfig->get('db/connection/default/username'),
            $this->deploymentConfig->get('db/connection/default/password'),
            $this->deploymentConfig->get('db/connection/default/dbname')
        );
        $sql = '';
        foreach (self::SQL_FILES as $filename) {
            $sqlPath = $this->directoryList
                    ->getPath(DirectoryList::VAR_DIR) . self::SQL_FOLDER_PATH . $filename . '.sql';
            $sql .= \file_get_contents($sqlPath) . "\r\n";
        }
        $dbConnection->multi_query($sql);
        $dbConnection->close();
    }
}
