<?php

namespace Crimson\CokerWV\Service;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Cokertire\Distributors\Model\ResourceModel\Distributors as DistributorsResource;
use Cokertire\Distributors\Model\DistributorsFactory;
use Cokertire\Distributors\Model\ResourceModel\Request as RequestResource;
use Cokertire\Distributors\Model\RequestFactory;

class ImportDistributors
{

    const DISTRIBUTORS_FILE_PATH = '/import/coker_zip_data_migration/cokertire/distributors.json';
    const REQUEST_FILE_PATH = '/import/coker_zip_data_migration/cokertire/distributors_request.json';

    public function __construct(
        private readonly DirectoryList        $directoryList,
        private readonly File                 $file,
        private readonly DistributorsResource $distributorsResource,
        private readonly DistributorsFactory  $distributorsFactory,
        private readonly RequestFactory       $requestFactory,
        private readonly RequestResource      $requestResource
    )
    {
    }


    public function execute(): array
    {
        $result['message'] = "All Cokertire Distributors data imported.";
        try {
            $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . self::DISTRIBUTORS_FILE_PATH;
            if (!$this->file->isExists($csvPath)) {
                throw new \Exception("Error with the JSON file.");
            }
            $rows_distributors = json_decode($this->file->fileGetContents($csvPath));
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        try {
            $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . self::REQUEST_FILE_PATH;
            if (!$this->file->isExists($csvPath)) {
                throw new \Exception("Error with the JSON file.");
            }
            $rows_requests = ($this->file->fileGetContents($csvPath));
            $rows_requests = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $rows_requests));

        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        if (!empty($rows_distributors) && !empty($rows_requests)) {
            $this->_cleanBeforeImport();
        }
        $this->importDistributors($rows_distributors);
        $this->importDistributorsRequest($rows_requests);
        return $result;
    }

    private function importDistributors($rows)
    {
        // looping
        foreach ($rows as $key => $row) {
            if (empty($row->distributorsid)) {
                continue;
            }
            $data = [
                'company' => $row->company,
                'contactName' => $row->contactName,
                'country' => $row->country,
                'address1' => $row->address1,
                'zip1' => $row->zip1,
                'address2' => $row->address2,
                'zip2' => $row->zip2,
                'status' => $row->status,
                'phone1' => $row->phone1,
                'phone2' => $row->phone2,
                'fax' => $row->fax,
                'languages' => $row->languages,
                'website' => $row->website,
                'email' => $row->email,
            ];
            //creating and saving
            $this->distributorsFactory->create()
                ->setData($data)
                ->save();
        }
    }

    private function importDistributorsRequest($rows)
    {
        // looping
        foreach ($rows as $key => $row) {
            if (empty($row->id)) {
                continue;
            }
            $data = [
                'name' => $row->name,
                'email' => $row->email,
                'telephone' => $row->telephone,
                'country' => $row->country,
                'business' => $row->business,
                'comments' => $row->comments,
                'unique_id' => $row->unique_id,
                'date_sent' => $row->date_sent
            ];
            //creating and saving
            $this->requestFactory->create()
                ->setData($data)
                ->save();
        }
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function _cleanBeforeImport(): void
    {
        $connection = $this->distributorsResource->getConnection();
        $connection->beginTransaction();
        $connection->delete(
            $this->distributorsResource->getMainTable(),
            $connection->quoteInto('status IN (?)', ['0', '1'])
        );
        $connection->commit();

        $connection_request = $this->requestResource->getConnection();
        $connection_request->beginTransaction();
        $connection_request->delete(
            $this->requestResource->getMainTable(),
            $connection->quoteInto('id > ?', 0)
        );
        $connection_request->commit();
    }
}
