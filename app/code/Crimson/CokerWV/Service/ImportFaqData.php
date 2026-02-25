<?php

namespace Crimson\CokerWV\Service;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;

class ImportFaqData
{

    const SQL_FOLDER_PATH = '/import/coker_zip_data_migration/aw_faq/';
    const SQL_FILES = [
        'aw_faq_category',
        'aw_faq_category_store',
        'aw_faq_article',
        'aw_faq_article_store'
    ];

    public function __construct(
        private readonly DirectoryList    $directoryList,
        private readonly DeploymentConfig $deploymentConfig
    )
    {
    }

    public function execute(): array
    {
        $result['message'] = "Assigned stores and related product data for faq entities.";
        try {
            $this->insertData();
        }
        catch (\Exception $e){

            $result['message'] = __('Can not insert blog data '.$e->getMessage());
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
