<?php
namespace Crimson\CokerWV\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\CsvFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory;

class CreateProductAttrSets implements DataPatchInterface
{
    const CSV_FILE_PATH = '/import/coker_zip_data_migration/coker_attribute_set.csv';
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory,
        private CsvFactory $csvReaderFactory,
        private DirectoryList $directoryList,
        private SetFactory $attributeSetFactory
    ) {}

    public function apply()
    {

        $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH;
        $csvReader = $this->csvReaderFactory->create();
        $rows = $csvReader->getData($csvPath);

        $eavSetup = $this->eavSetupFactory->create();
        $attributeSetId = $eavSetup->getDefaultAttributeSetId(4);
        foreach ($rows as $data) {

            /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
            $attributeSet = $this->attributeSetFactory->create();
            $attributeSet->setData([
                'attribute_set_name' => $data[0],
                'entity_type_id' => 4
            ]);
            $attributeSet->validate();
            $attributeSet->save();
            $attributeSet->initFromSkeleton($attributeSetId)->save();
        }
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
