<?php
namespace Silk\ProductImage\Plugin\Catalog\Block\Product;

use Magento\Catalog\Block\Product\Image as ImageBlock;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Silk\ProductImage\Model\Document;

class ImageFactory extends \Magento\Catalog\Block\Product\View\Gallery
{
    const XML_ENABLED = "silk_image/general/enable";
    public function aroundCreate(
        $subject,
        callable $proceed,
        Product $product,
        string $imageId,
        array $attributes = null
    ):ImageBlock {

        $block = $proceed($product,$imageId,$attributes);
        if (!$this->getConfig(self::XML_ENABLED)) {
            return $block;
        }
        $data = $block->getData();
        $collection = $this->_loadObject(Document::class)
                            ->getCollection()
                            ->addProductFilter($product->getId())
                            ->addFieldToFilter('for_base_image',['eq'=>1])
                            ->addFieldToFilter('is_active',['eq'=>1]);
        $document = $collection->getFirstItem();
        if($document){
            if(preg_match("#\/catalog/product(\/.*)?#i",$document->getFile()?$document->getFile():'',$matches))
                $data['image_url'] = $this->_imageHelper->init($product, $imageId, ['width' => $data['width'], 'height' => $data['height']])->setImageFile($matches[1])->getUrl();
            $data['label'] = $document->getTitle()?:$data['label'];
        }


        return $block->setData($data);
    }
    protected function _loadObject($object)
    {
        return $this->_getObjectManager()->get($object);
    }

    protected function _getObjectManager()
    {
        return ObjectManager::getInstance();
    }
    public function getConfig($path, $storeCode = null)
    {
        return $this->getScopeConfig()->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
    }

    protected function getScopeConfig()
    {
        return $this->_loadObject(ScopeConfigInterface::class);
    }
}

 ?>
