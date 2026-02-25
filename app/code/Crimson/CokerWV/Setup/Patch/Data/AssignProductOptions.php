<?php
namespace Crimson\CokerWV\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\CsvFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AssignProductOptions implements DataPatchInterface
{
    const CSV_FILE_PATH = '/import/coker_zip_data_migration/coker_product_options.csv';
    private \Magento\Eav\Setup\EavSetup $eavSetup;

    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory,
        private CsvFactory $csvReaderFactory,
        private DirectoryList $directoryList,
        private Config $eavConfig
    ) {

        $this->eavSetup = $this->eavSetupFactory->create([
            'setup' => $this->moduleDataSetup
        ]);
    }

    /**
     * @throws LocalizedException
     */
    public function apply()
    {

        $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH;
        $csvReader = $this->csvReaderFactory->create();
        $rows = $csvReader->getData($csvPath);

        foreach ($rows as $num => $data) {
            if($num>0){
                $attributeCode = $data[0];
                $attributeOptions = array_unique(explode('**',$data[2]));

                $attr = $this->eavConfig->getAttribute(Product::ENTITY, $attributeCode);
                if ($attr->getId()) {
                    $this->addOptions($attr, $attributeOptions);
                }
            }
        }
    }

    private function addOptions($attribute, $arrOptions)
    {
        /*** Magento\Eav\Setup\EavSetup  */
        $this->eavSetup->addAttributeOption(
                [
                    'values' => $arrOptions,
                    'attribute_id' => $attribute->getAttributeId()
                ]
        );
    }

    public static function getDependencies()
    {
        return [
            CreateProductAttrSets::class,
            CreateOtherTypesProductAttributes::class,
            CreateIntergerProductAttributes::class
        ];
    }

    public function getAliases()
    {
        return [];
    }
}
