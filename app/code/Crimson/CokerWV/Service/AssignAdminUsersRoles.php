<?php

namespace Crimson\CokerWV\Service;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Csv;
use Magento\User\Model\ResourceModel\User;
use Magento\User\Model\UserFactory;
use Magento\Framework\Filesystem\Driver\File;

class AssignAdminUsersRoles
{

    const CSV_FILE_PATH = '/import/coker_zip_data_migration/admin_users_roles.csv';
    CONST CSV_FILE_INVALID_MESSAGE = "Error with the CSV file.";

    public function __construct(
        private readonly Csv $csvReader,
        private readonly DirectoryList $directoryList,
        private readonly User $userResourceModel,
        private readonly File $file,
        private readonly UserFactory $_userFactory
    ) {}

    /**
     * @return array
     * @throws FileSystemException
     */
    public function execute(): array
    {
        $result = [];

        try {
            $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH;
            if (!$this->file->isExists($csvPath)) {
                throw new \Exception(self::CSV_FILE_INVALID_MESSAGE);
            }
            $rows = $this->csvReader->getData($csvPath);
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        // remove header data from array
        array_shift($rows);
        // looping
        foreach ($rows as $key => $row) {
            $userData = $this->userResourceModel->loadByUsername($row[1]);
            if ($userData['email'] == $row[0]) {
                try {
                    $user = $this->_userFactory->create()->load($userData['user_id']);
                    $user->setRoleId($row[2]);
                    $user->save();
                } catch (\Exception $e) {
                    $result['message'] = $e->getMessage() . " -- username from CSV: " . $row[1];
                    break;
                }
            }
        }

        return $result;
    }
}
