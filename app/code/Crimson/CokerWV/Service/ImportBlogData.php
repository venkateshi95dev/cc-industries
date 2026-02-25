<?php

namespace Crimson\CokerWV\Service;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;

class ImportBlogData
{

    const SQL_FOLDER_PATH = '/import/coker_zip_data_migration/blog/';
    const SQL_FILES = [
        'magefan_blog_category',
        'magefan_blog_post',
        'magefan_blog_post_category',
        'magefan_blog_tag',
        'magefan_blog_post_tag',
        'magefan_blog_comment'
    ];

    public function __construct(
        private readonly DirectoryList    $directoryList,
        private readonly DeploymentConfig $deploymentConfig
    )
    {
    }

    public function execute(): array
    {
        $result['message'] = "Assigned stores and related product data for blog entities.";
        try {
            $this->insertBlogData();
        }
        catch (\Exception $e){

            $result['message'] = __('Can not insert blog data '.$e->getMessage());
        }
        return $result;
    }

    private function insertBlogData()
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
