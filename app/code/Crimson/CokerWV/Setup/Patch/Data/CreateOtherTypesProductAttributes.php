<?php
namespace Crimson\CokerWV\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class CreateOtherTypesProductAttributes implements DataPatchInterface
{
    const CSV_FILE_PATH = '/import/coker_zip_data_migration/coker_product_attributes_not_int.csv';
    private \Magento\Eav\Setup\EavSetup $eavSetup;

    public function __construct(
        private \Magento\Framework\File\CsvFactory $csvReaderFactory,
        private ModuleDataSetupInterface  $setup,
        private EavSetupFactory           $eavSetupFactory,
        private \Magento\Catalog\Model\Config $catalogConfig,
        private \Magento\Eav\Model\Config $eavConfig,
        private \Magento\Eav\Api\AttributeManagementInterface $attributeManagement,
        private DirectoryList $directoryList,
        private LoggerInterface $logger
    ) {
        $this->eavSetup = $this->eavSetupFactory->create([
            'setup' => $this->setup
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
        foreach ($rows as $rownum => $data) {
            if ($rownum > 0){
                $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $data[0]);
                if($attribute->getId()){
                    continue;
                }
                $this->createAttribute($data);
            }
        }
    }

    private function createAttribute($data)
    {
        try {
            $this->eavSetup->addAttribute(
                Product::ENTITY,
                $data[0],
                [
                    'label' => $data[1],
                    'type' => $data[2]!='NULL'?$data[2]:null,
                    'backend' => $data[3]!='NULL'?$data[3]:null,
                    'input' => $data[4]!='NULL'?$data[4]:null,
                    'frontend_class'=>$data[5]!='NULL'?$data[5]:null,
                    'source' => $data[6]!='NULL'?$data[6]:null,
                    'required'=> $data[7]!='NULL'?$data[7]:null,
                    'default' =>$data[8]!='NULL'?$data[8]:null,
                    'unique' => $data[9],
                    'user_defined' => true,
                    'global' => $data[11],
                    'visible' => $data[12],
                    'searchable' => $data[13],
                    'filterable' => $data[14],
                    'comparable' => $data[15],
                    'visible_on_front' => $data[16],
                    'wysiwyg_enabled' => $data[17],
                    'is_html_allowed_on_front' => $data[18],
                    'visible_in_advanced_search' => $data[19],
                    'filterable_in_search' => $data[20],
                    'used_in_product_listing' => $data[21],
                    'used_for_sort_by' => $data[22],
                    'apply_to' => $data[23]!='NULL'?$data[23]:null,
                    'position' => $data[24],
                    'used_for_promo_rules' => $data[25],
                    'is_used_in_grid' => $data[26],
                    'is_visible_in_grid' => $data[27],
                    'is_filterable_in_grid' => $data[28]
                ]
            );
            $this->eavConfig->clear();
            if(!empty($data[10])){
                $attributeSets = explode(',',$data[10]);
                foreach ($attributeSets as $attributeSetName){
                    $attributeSet = $this->eavSetup->getAttributeSet(4, $attributeSetName);
                    if(isset($attributeSet['attribute_set_id'])) {
                        $group_id = $this->catalogConfig->getAttributeGroupId($attributeSet['attribute_set_id'], 'General');
                        $this->attributeManagement->assign(
                            'catalog_product',
                            $attributeSet['attribute_set_id'],
                            $group_id,
                            $data[0],
                            100
                        );
                    }
                }
            }
        }catch (\Exception $exception){
            $this->logger->debug($exception);
        }
    }

    public static function getDependencies()
    {
        return [
            CreateProductAttrSets::class
        ];
    }

    public function getAliases()
    {
        return [];
    }
}
