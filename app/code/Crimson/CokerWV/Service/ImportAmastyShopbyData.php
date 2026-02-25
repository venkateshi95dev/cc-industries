<?php

namespace Crimson\CokerWV\Service;

use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterfaceFactory;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Cms\Model\ResourceModel\Block as BlockResource;
use Magento\Cms\Api\GetBlockByIdentifierInterface as BlockGetter;

class ImportAmastyShopbyData
{

    const CSV_FILE_PATH = '/import/coker_zip_data_migration/amasty_amshopby_data.csv';
    private $attrOptions = [];

    public function __construct(
        private readonly AttributeRepository $attributeRepository,
        private readonly DirectoryList $directoryList,
        private readonly Csv $csvReader,
        private readonly File $file,
        private readonly StoreRepositoryInterface $storeRepositoryInterface,
        private readonly OptionSettingRepositoryInterface $optionSettingRepository,
        private readonly OptionSettingInterfaceFactory $optionSettingInterfaceFactory,
        private readonly BlockGetter $getBlockByIdentifier
    ) {}


    public function execute(): array
    {
        $result['message'] = "All Amasty Shopby Data imported.";
        try {
            $csvPath = $this->directoryList->getPath(DirectoryList::VAR_DIR).self::CSV_FILE_PATH;
            if (!$this->file->isExists($csvPath)) {
                throw new \Exception("Error with the CSV file.");
            }

            $rows = $this->csvReader->getData($csvPath);
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        if (!empty($rows)) {
            //getting needed store ids
            $cokerTireStoreId        = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_STORE_CODE)->getId();
            $cokerTireDefaultStoreId = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_DEFAULT_STORE_CODE)->getId();
            $wvStoreId               = $this->storeRepositoryInterface->get(WVStoreInterface::WV_STORE_CODE)->getId();

            $header = array_shift($rows);

            $rows = array_map(function($v)use($header){
                return array_combine($header, $v);
            }, $rows);
        }

        // looping
        foreach ($rows as $row) {
           $optionId = $this->getOptionId($row['attribute_code'],$row['option_value']);
           if ($optionId)
           {
               $oldOption = $this->optionSettingRepository->getByParams($row['attribute_code'],$optionId,0);
               if($oldOption->getId())
                   $this->optionSettingRepository->deleteByOptionId($optionId);
               $topCmsId = $bottomCmsId = null;
               try {
                   $topCmsId = $this->getBlockByIdentifier->execute('top_cms_identifier', $cokerTireStoreId);
                   $bottomCmsId = $this->getBlockByIdentifier->execute('top_cms_identifier', $cokerTireStoreId);
               }
               catch (\Exception $e){}
               /** @var OptionSettingInterface $optionSetting */
               $optionSetting = $this->optionSettingInterfaceFactory->create();
               $optionSetting->setStoreId(0)
                   ->setValue($optionId)
                   ->setImage($row['image']!='NULL'?$row['image']:null)
                   ->setDescription($row['description']!='NULL'?str_replace(['&comma;','&quot;'], [',','"'], $row['description']):null)
                   ->setTitle($row['title']!='NULL'?$row['title']:null)
                   ->setMetaDescription($row['meta_description']!='NULL'?str_replace(['&comma;','&quot;'], [',','"'], $row['meta_description']):null)
                   ->setMetaKeywords($row['meta_keywords']!='NULL'?str_replace(['&comma;','&quot;'], [',','"'], $row['meta_keywords']):null)
                   ->setMetaTitle($row['meta_title']!='NULL'?$row['meta_title']:null)
                   ->setTopCmsBlockId($topCmsId)
                   ->setBottomCmsBlockId($bottomCmsId);
               $optionSetting->setAttributeCode($row['attribute_code']);
               $this->optionSettingRepository->save($optionSetting);
           }
        }

        return $result;
    }

    private function getOptionId($attrCode, $optionValue)
    {
        $arrValue = $this->getAttributeValues($attrCode);
        return $arrValue[$optionValue];
    }

    /**
     * @param string $attrCode
     * @return string[]
     */
    private function getAttributeValues(string $attrCode): array
    {
        if(isset($this->attrOptions[$attrCode]))
            return $this->attrOptions[$attrCode];
        $attributeValues = [];
        try {
            /** @var \Magento\Eav\Model\Entity\Attribute\Option[]  $attributeOptions */
            $attributeOptions = $this->attributeRepository->get($attrCode)->getOptions();
        } catch (NoSuchEntityException $exception) {
            return $attributeValues;
        }

        foreach ($attributeOptions as $option) {
            if ($option->getValue()) {
                $attributeValues[$option->getLabel()] = $option->getValue();
            }
        }
        $this->attrOptions[$attrCode] = $attributeValues;
        return $this->attrOptions[$attrCode];
    }
}
