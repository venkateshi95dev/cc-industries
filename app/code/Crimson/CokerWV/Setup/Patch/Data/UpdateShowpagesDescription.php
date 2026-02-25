<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Cokertire\Showpages\Model\ResourceModel\Showpages as ShowpagesResource;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpdateShowpagesDescription implements DataPatchInterface
{

    const CSV_FILE_PATH = '/import/coker_zip_data_migration/coker_showpages.json';
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly DirectoryList $directoryList,
        private readonly File $file,
        private readonly ShowpagesResource $showpagesResource
    ) {}

    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();
        $rows = [];
        try {
            $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH;
            if (!$this->file->isExists($csvPath)) {
                throw new \Exception("Error with the JSON file.");
            }
            $rows = json_decode($this->file->fileGetContents($csvPath));
        } catch (\Exception $e) {
        }

        $connection = $this->showpagesResource->getConnection();
        foreach ($rows as $row) {
            if (empty($row->identifier) || empty($row->show_description)) {
                continue;
            }
            $connection->update(
                $this->showpagesResource->getMainTable(),
                ['show_description' => $row->show_description],
                ['identifier = ?' => $row->identifier]
            );
        }
        $this->moduleDataSetup->endSetup();
    }

    private function getCMSBlockIdsToUpdate()
    {
        $connection = $this->showpagesResource->getConnection();
        $select = $connection->update();
        return $result;
    }
    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [
            CreateCokerWebiste::class,
            CreateWVWebiste::class
        ];
    }
}
