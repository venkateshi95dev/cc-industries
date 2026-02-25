<?php

namespace Crimson\CokerWV\Service;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Newsletter\Model\ResourceModel\Subscriber as SubscriberResource;

class ImportNewsletterSubscribers
{

    CONST CSV_FILE_PATH_COKER         = '/import/coker_zip_data_migration/newsletter_subscribers_coker.csv';
    CONST CSV_FILE_PATH_COKER_DEFAULT = '/import/coker_zip_data_migration/newsletter_subscribers_coker_default.csv';
    CONST CSV_FILE_PATH_COKER_WV      = '/import/coker_zip_data_migration/newsletter_subscribers_wv.csv';
    CONST BATCH_SIZE    = 1000;

    public function __construct(
        private readonly Csv $csvReader,
        private readonly DirectoryList $directoryList,
        private readonly File $file,
        private readonly StoreRepositoryInterface $storeRepositoryInterface,
        private readonly SubscriberResource $subscriberResource,
        private readonly Customer $customerSource,
    ) {}

    /**
     * @param string $storeCode
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(string $storeCode): array
    {
        $result['message'] = "All Subscribers imported. " . $storeCode;
        try {
            if ($storeCode == CokerStoreInterface::COKER_DEFAULT_STORE_CODE) {
                $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH_COKER_DEFAULT;
            } elseif ($storeCode == CokerStoreInterface::COKER_STORE_CODE) {
                $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH_COKER;
            } elseif ($storeCode == WVStoreInterface::WV_STORE_CODE) {
                $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH_COKER_WV;
            } else {
                throw new \Exception("No Store code has been passed.");
            }
            if (!$this->file->isExists($csvPath)) {
                throw new \Exception("Error with the CSV file.");
            }

            $rows = $this->csvReader->getData($csvPath);
            if (empty($rows)) {
                throw new \Exception("No data to import.");
            }
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        // remove header data from array
        array_shift($rows);

        //getting needed store ids
        $storeId = $this->storeRepositoryInterface->get($storeCode)->getId();
        $storeCodeId = [
            $storeCode => $storeId
        ];

        //removing current for store
        $this->_removeSubscribersBeforeImport($storeCodeId);

        //get customers ids in ZIP by emails coming from Coker
        $cokerEmails = array_column($rows, 4);
        $cokerEmails = array_unique($cokerEmails);
        $zipIdsByCokerEmails = $this->_getZIPCustomerIdsByCokerEmails($cokerEmails, $storeId);

        // looping
        foreach (array_chunk($rows, ImportNewsletterSubscribers::BATCH_SIZE, true) as $currentRows) {
            echo "Processing the batch of " . ImportNewsletterSubscribers::BATCH_SIZE . " subscribers" . "\n";

            $subscriberData = [];
            foreach ($currentRows as $row) {
                if (empty($row[4]) || empty($row[7])) {
                    continue;
                }

                $customerId = array_search($row[4], array_column($zipIdsByCokerEmails, 'email', 'entity_id'));
                $subscriberData[] = [
                    'store_id'                => (int)$storeId,
                    'change_status_at'        => $row[2],
                    'customer_id'             => $customerId ? (int)$customerId : null,
                    'subscriber_email'        => $row[4],
                    'subscriber_status'       => (int)$row[5],
                    'subscriber_confirm_code' => $row[6],
                ];
            }

            if (!empty($subscriberData)) {
                $this->subscriberResource
                    ->getConnection()
                    ->insertMultiple(
                        $this->subscriberResource->getMainTable(),
                        $subscriberData
                    );
            }
        }

        return $result;
    }

    /**
     * @param array $dataFromCoker
     * @param $storeId
     * @return array
     */
    private function _getZIPCustomerIdsByCokerEmails(array $dataFromCoker, $storeId): array
    {
        if (!$dataFromCoker) {
            return [];
        }

        $connection = $this->customerSource->getConnection();
        $select = $connection->select()
            ->from(['main_table' => $this->customerSource->getTable('customer_entity')])
            ->where('email IN (?)', $dataFromCoker)
            ->where('store_id = ?', $storeId)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['main_table.entity_id', 'main_table.email'])
        ;
        $data = $connection->fetchAll($select);
        if (!$data) {
            $data = [];
        }

        return $data;
    }

    /**
     * @param $storeId
     * @return void
     * @throws LocalizedException
     */
    private function _removeSubscribersBeforeImport($storeId): void
    {
        if (!empty($storeId)) {
            $connection = $this->subscriberResource->getConnection();
            $connection->beginTransaction();
            $connection->delete(
                $this->subscriberResource->getMainTable(),
                $connection->quoteInto('store_id = ?', $storeId)
            );
            $connection->commit();
        }
    }
}
