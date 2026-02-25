<?php

namespace Crimson\CokerWV\Service;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\ResourceModel\Address;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class ImportCustomerAddresses
{

    const CSV_FILE_PATH_COKER_TIRE = '/import/coker_zip_data_migration/customer_addresses_coker_tire.csv';
    const CSV_FILE_PATH_WV         = '/import/coker_zip_data_migration/customer_addresses_wv.csv';
    const BATCH_SIZE    = 1000;

    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly File $file,
        private readonly Csv $csvReader,
        private readonly Address $resourceAddress,
        private readonly StoreManagerInterface $storeManager,
        private readonly Customer $customerResource
    ) {}


    public function execute(string $webiste): array
    {
        $result['message'] = "All Customer Addresses were imported. " . $webiste;
        try {
            if ($webiste == CokerStoreInterface::COKER_WEBSITE_CODE) {
                $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH_COKER_TIRE;
            } elseif ($webiste == WVStoreInterface::WV_WEBSITE_CODE) {
                $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH_WV;
            } else {
                throw new \Exception("No Website has been passed.");
            }

            if (!$this->file->isExists($csvPath)) {
                throw new \Exception("Error with the CSV file.");
            }

            $rows = $this->csvReader->getData($csvPath);
            if (empty($rows)) {
                throw new \Exception("No addresses to import.");
            }
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        $cokerWebsiteId = [
            $this->storeManager->getWebsite($webiste)->getId(),
        ];
        $mainTable  = $this->resourceAddress->getTable('customer_address_entity');
        $connection = $this->resourceAddress->getConnection();

        //gettting emails
        array_shift($rows);
        $cokerEmails = array_column($rows, 0);
        $cokerEmails = array_unique($cokerEmails);
        $cokerEmailsZipIds = $this->_getCustomerIdsByEmails($cokerWebsiteId, $cokerEmails);

        //default addresses association
        $defaultAddress = [];

        // looping
        foreach (array_chunk($rows, ImportCustomerAddresses::BATCH_SIZE, true) as $currentRows) {
            echo "Processing the batch of " . ImportCustomerAddresses::BATCH_SIZE . " addresses" . "\n";

            $addressesData = [];
            foreach ($currentRows as $row) {
                if (empty($row[0]) || empty($row[1]) || empty($row[4])) {
                    continue;
                }

                if (empty($cokerEmailsZipIds[$row[0]])) {
                    continue;
                }

                $addressesData[] = [
                    'increment_id' => $row[3],
                    'parent_id'    => $cokerEmailsZipIds[$row[0]],
                    'created_at'   => $row[5],
                    'updated_at'   => $row[6],
                    'is_active'    => $row[7],
                    'city'         => $row[8],
                    'company'      => $row[9],
                    'country_id'   => $row[10],
                    'fax'          => $row[11],
                    'firstname'    => $row[12],
                    'lastname'     => $row[13],
                    'middlename'   => $row[14],
                    'postcode'     => $row[15],
                    'prefix'       => $row[16],
                    'region'       => $row[17],
                    'region_id'    => $row[18],
                    'street'       => $row[19],
                    'suffix'       => $row[20],
                    'telephone'    => $row[21],
                    'vat_id'       => $row[22],
                    'vat_is_valid'        => $row[23],
                    'vat_request_date'    => $row[24],
                    'vat_request_id'      => $row[25],
                    'vat_request_success' => $row[26]
                ];
            }

            if (!empty($addressesData)) {
                $connection->insertMultiple($mainTable, $addressesData);
            }
        }

        return $result;
    }

    /**
     * @param array $websiteId
     * @param array $cokerEmails
     * @return array
     */
    private function _getCustomerIdsByEmails(array $websiteId, array $cokerEmails): array
    {
        if (!$cokerEmails) {
            return [];
        }

        $connection = $this->resourceAddress->getConnection();
        $select = $connection->select()
            ->from(['main_table' => $this->resourceAddress->getTable('customer_entity')])
            ->where('email IN (?)', $cokerEmails)
            ->where('website_id IN (?)', $websiteId)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['main_table.entity_id', 'main_table.email', 'main_table.website_id'])
        ;
        $customers = $connection->fetchAll($select);
        if (!$customers) {
            $customers = [];
        }

        $data = [];
        foreach ($customers as $customer) {
            $data[$customer['email']] = (int)$customer['entity_id'];
        }

        return $data;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function removeCokerAndWVAddressesBeforeImport(): void
    {
        $cokerWVWebsiteIds = [
            $this->storeManager->getWebsite(CokerStoreInterface::COKER_WEBSITE_CODE)->getId(),
            $this->storeManager->getWebsite(WVStoreInterface::WV_WEBSITE_CODE)->getId(),
        ];
        $connection = $this->customerResource->getConnection();
        $select = $connection->select()
            ->from(['main_table' => $this->customerResource->getTable('customer_entity')])
            ->where('website_id IN (?)', $cokerWVWebsiteIds)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['main_table.entity_id'])
        ;
        $customers = $connection->fetchCol($select);
        if (!empty($customers)) {
            $connection->beginTransaction();
            $connection->delete(
                $this->customerResource->getTable('customer_address_entity'),
                $connection->quoteInto('parent_id IN (?)', $customers)
            );
            $connection->commit();
        }
    }
}
