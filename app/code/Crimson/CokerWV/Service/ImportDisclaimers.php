<?php

namespace Crimson\CokerWV\Service;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Cokertire\Disclaimers\Model\DisclaimersFactory;
use Cokertire\Disclaimers\Model\ResourceModel\Disclaimers;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;



class ImportDisclaimers
{
    const REQUEST_FILE_PATH = '/import/coker_zip_data_migration/cokertire/disclaimers.json';

    public function __construct(
        private readonly DirectoryList        $directoryList,
        private readonly File                 $file,
        private readonly Disclaimers $disclaimersResource,
        private readonly DisclaimersFactory  $disclaimersFactory,
        private readonly AttributeSetCollectionFactory $_attributeSetCollection
    )
    {
    }


    public function execute(): array
    {
        $result['message'] = "All Cokertire Disclaimers data imported.";

        try {
            $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . self::REQUEST_FILE_PATH;
            if (!$this->file->isExists($csvPath)) {
                throw new \Exception("Error with the JSON file.");
            }
            $rows_requests = json_decode($this->file->fileGetContents($csvPath));

        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        if (!empty($rows_requests)) {
            $this->_cleanBeforeImport();
        }
        $count = $this->importData($rows_requests);
        $result['message'] .= $count. " records";
        return $result;
    }

    private function importData($rows)
    {
        $numRow = 0;
        foreach ($rows as $key => $row) {
            if (empty($row->attribute_set_name)) {
                continue;
            }
            $attrSetId = $this->getAttributeSetId($row->attribute_set_name);
            $data = [
                'attribute_set' => $attrSetId,
                'disclaimer' => $row->disclaimer,
                'status' => $row->status,
            ];
            //creating and saving
            $this->disclaimersFactory->create()
                ->setData($data)
                ->save();
            $numRow++;
        }
        return $numRow;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function _cleanBeforeImport(): void
    {
        $connection = $this->disclaimersResource->getConnection();
        $connection->beginTransaction();
        $connection->delete(
            $this->disclaimersResource->getMainTable(),
            $connection->quoteInto('status IN (?)', ['0', '1', '2'])
        );
        $connection->commit();

    }

    private function getAttributeSetId($attributeSetName)
    {
        $attributeSetCollection = $this->_attributeSetCollection->create()
            ->addFieldToSelect('attribute_set_id')
            ->addFieldToFilter('attribute_set_name', $attributeSetName)
            ->addFieldToFilter('entity_type_id', 4)
            ->getFirstItem();

        $attributeSetId = $attributeSetCollection->getData('attribute_set_id');

        return $attributeSetId;
  }
}
