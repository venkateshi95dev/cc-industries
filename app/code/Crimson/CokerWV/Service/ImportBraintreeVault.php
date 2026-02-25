<?php

namespace Crimson\CokerWV\Service;

use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Sales\Model\ResourceModel\Order\Payment;
use Magento\Vault\Model\ResourceModel\PaymentToken;

class ImportBraintreeVault
{

    CONST CSV_FILE_PATH_BRAINTREE = '/import/coker_zip_data_migration/braintree_vault.json';
    CONST CSV_FILE_PATH_BRAINTREE_PAYMENT = '/import/coker_zip_data_migration/braintree_vault_token_payment.csv';
    CONST BATCH_SIZE    = 1000;

    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly File $file,
        private readonly Customer $customerResource,
        private readonly PaymentToken $paymentTokenResource,
        private readonly ResourceConnection $resource,
        private readonly Payment $paymentResource,
        private readonly Csv $csvReader,
    ) {}

    public function execute(): array
    {
        $result['message'] = '';
        try {
            $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH_BRAINTREE;
            $csvPath2 = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH_BRAINTREE_PAYMENT;
            if (!$this->file->isExists($csvPath) || !$this->file->isExists($csvPath2)) {
                throw new \Exception("Error with the CSV file.");
            }

            $rows = json_decode($this->file->fileGetContents($csvPath));
            $rowsVaultPayment = $this->csvReader->getData($csvPath2);
            if (empty($rows) || empty($rowsVaultPayment)) {
                throw new \Exception("No data to import.");
            }

            // remove header data from array
            array_shift($rowsVaultPayment);
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        // looping
        foreach (array_chunk($rows, ImportBraintreeVault::BATCH_SIZE, true) as $currentRows) {
            echo "Processing the batch vault_payment_token of " . ImportBraintreeVault::BATCH_SIZE . " rows" . "\n";
            $this->_processCurrentRows($currentRows);
        }

        echo "_______________**********NEXT STEP*************________________ " . "\n";

        // looping
        foreach (array_chunk($rowsVaultPayment, ImportBraintreeVault::BATCH_SIZE, true) as $currentRowsVaultPayment) {
            echo "Processing the batch vault_payment_token_order_payment_link of " . ImportBraintreeVault::BATCH_SIZE . " rows" . "\n";
            $paymentTokenIds = $this->_getOldVaultTokenIds($currentRowsVaultPayment);
            $orderPaymentIds = $this->_getOrderPaymentIds($currentRowsVaultPayment);
            $this->_processCurrentRowsVaultPayment($currentRowsVaultPayment, $paymentTokenIds, $orderPaymentIds);
        }

        return $result;
    }

    private function _getOrderPaymentIds($orderPaymentIds): array
    {
        $orderPaymentIds = array_column($orderPaymentIds, 0);
        $orderPaymentIds = array_unique($orderPaymentIds);
        $connection = $this->paymentResource->getConnection();
        $select = $connection->select()
            ->from($this->paymentResource->getMainTable())
            ->where('old_payment_id IN (?)', $orderPaymentIds)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['old_payment_id', 'entity_id']);

        return $connection->fetchPairs($select);
    }

    private function _getOldVaultTokenIds($paymentTokenIds): array
    {
        $paymentTokenIds = array_column($paymentTokenIds, 1);
        $paymentTokenIds = array_unique($paymentTokenIds);
        $connection = $this->paymentTokenResource->getConnection();
        $select = $connection->select()
            ->from($this->paymentTokenResource->getMainTable())
            ->where('old_entity_id IN (?)', $paymentTokenIds)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['old_entity_id', 'entity_id']);

        return $connection->fetchPairs($select);
    }

    private function _processCurrentRowsVaultPayment($currentRowsVaultPayment, $paymentTokenIds, $orderPaymentIds): void
    {
        $dataToInsert = [];
        foreach ($currentRowsVaultPayment as $rowData) {
            if (empty($paymentTokenIds[$rowData[1]]) || empty($orderPaymentIds[$rowData[0]])) {
                continue;
            }

            $dataToInsert[] = [
                'order_payment_id' => $orderPaymentIds[$rowData[0]],
                'payment_token_id' => $paymentTokenIds[$rowData[1]],
            ];
        }

        if (!empty($dataToInsert)) {
            echo "Inserting data " . count($dataToInsert) . " rows." . "\n";
            $this->resource->getConnection()->insertMultiple(
                $this->resource->getTableName('vault_payment_token_order_payment_link'),
                $dataToInsert
            );
        }
    }

    private function _processCurrentRows(array $currentRows): void
    {
        $dataToInsert = [];
        foreach ($currentRows as $rowData) {
            $dataToInsert[] = [
                'customer_id'         => !empty($rowData->customer_id) ? $this->_getCustomerId($rowData->email, $rowData->code) : null,
                'public_hash'         => $rowData->public_hash,
                'payment_method_code' => $rowData->payment_method_code,
                'type'                => $rowData->type,
                'created_at'          => $rowData->created_at,
                'expires_at'          => $rowData->expires_at,
                'gateway_token'       => $rowData->gateway_token,
                'details'             => $rowData->details,
                'is_active'           => $rowData->is_active,
                'is_visible'          => $rowData->is_visible,
                'old_entity_id'       => $rowData->entity_id,
            ];
        }

        if (!empty($dataToInsert)) {
            echo "Inserting data " . count($dataToInsert) . " rows." . "\n";
            $this->paymentTokenResource->getConnection()->insertMultiple(
                $this->paymentTokenResource->getMainTable(),
                $dataToInsert
            );
        }
    }


    private function _getCustomerId($email, $websiteCode)
    {
        if (!$email || !$websiteCode) {
            return null;
        }

       if ($websiteCode == 'base') {
           $websiteId = 3;
       } elseif ($websiteCode == 'wv') {
           $websiteId = 6;
       } else {
           return null;
       }

        $connection = $this->customerResource->getConnection();
        $select = $connection->select()
            ->from($this->customerResource->getTable('customer_entity'))
            ->where('email = ?', $email)
            ->where('website_id = ?', $websiteId)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['entity_id']);

        $customerId = $connection->fetchOne($select);

        return $customerId ? (int) $customerId : null;
    }
}
