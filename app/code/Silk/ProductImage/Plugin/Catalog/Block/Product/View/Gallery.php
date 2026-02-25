<?php
namespace Silk\ProductImage\Plugin\Catalog\Block\Product\View;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Store\Model\ScopeInterface;
use Silk\ProductImage\Model\Document;

class Gallery
{
    const XML_ENABLED = "silk_image/general/enable";

    const DEFAULT_TYPE = "image";
    /**
     *@product \Magento\Catalog\Block\Product
     */
    public function afterGetMediaGalleryImages($product, $images)
    {

        if (!$this->getConfig(self::XML_ENABLED)) {
            return $images;
        }
        //Magento\Framework\Data\Collection
        if (!$images instanceof Collection) {
                return $images;
        }
        $product->load($product->getId());
        try {
            foreach ($this->getData($product) as $document) {
                $this->addItemToGallery($document,$images,$product);
            }
        } catch (Exception $e) {
            //error
        }


       return $this->sort($images);
    }
    private function addItemToGallery($document,$images,&$product){

        $items = $images->getItems();
        $images->clear();
        if($document->getId() && $document->isActive()){
            $for_base_image = $document->getData('for_base_image');
            $image['media_type'] = self::DEFAULT_TYPE;
            $image['row_id'] = $product->getId();
            $image['label'] = $document->getTitle();
            $image['position'] = $for_base_image?0:999;
            $image['disabled'] = 0;
            $image['label_default'] = $document->getTitle();
            $image['position_default'] = $for_base_image?0:999;
            $image['disabled_default'] = 1;
            if(preg_match("#\/catalog/product(\/.*)?#i",$document->getFile(),$matches))
                $image['file'] = $matches[1];
            $image['url'] = $document->getFile();
            $image['path'] = $document->getFilePath();
            if($for_base_image)
                $images->addItem(new \Magento\Framework\DataObject($image));
            foreach ($items as $item) {
                if($for_base_image){
                    $position = $item->getData('position');
                    $position_default = $item->getData('position_default');
                    $disabled_default = $item->getData('disabled_default');
                    $item->setData('position',$position+1);
                    $item->setData('position_default',$position_default+1);
                    $item->setData('disabled_default',0);
                }
                $images->addItem($item);
            }
            if(!$for_base_image)
                $images->addItem(new \Magento\Framework\DataObject($image));
        }
        return $images;
    }
    private function sort(&$images){
        $items = $images->getItems();
        $images->clear();
        if (is_array($items)) {
            usort($items, function($imageA, $imageB){
                return $imageA['position'] < $imageB['position'] ? -1 : 1;
            });
        }
        foreach ($items as $v) {
            $images->addItem($v);
        }

        return $images;
    }

    private function getData($product)
    {

        $documents = $this->_loadObject(Document::class)->getCollection()->addProductFilter($product->getId());

        foreach ($documents as $document) {

            yield $document;
        }
    }
    public function getConfig($path, $storeCode = null)
    {
        return $this->getScopeConfig()->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
    }

    protected function getScopeConfig()
    {
        return $this->_loadObject(ScopeConfigInterface::class);
    }

    protected function _loadObject($object)
    {
        return $this->_getObjectManager()->get($object);
    }

    protected function _getObjectManager()
    {
        return ObjectManager::getInstance();
    }
}



 ?>
