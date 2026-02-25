<?php

namespace Crimson\Category\Block\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Helper\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Url\Helper\Data;
use Magento\Store\Model\StoreManager;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class FeaturedCategories extends ListProduct
{
    protected $catalogProductVisibility;
    protected $_categoryHelper;
    protected $categoryFactory;
    protected $_catalogLayer;
    protected $_storeConfig;

    protected $_storeManager;

    public function __construct(
        Context $context,
        Category $categoryHelper,
        CategoryFactory $categoryFactory,
        Filesystem $filesystem,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        ScopeConfigInterface $scopeConfig,
        StoreManager $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        protected readonly AdapterFactory $imageAdapterFactory,
        array $data = []
    ) {
        $this->_categoryHelper = $categoryHelper;
        $this->categoryFactory = $categoryFactory;
        $this->_catalogLayer = $layerResolver->get();
        $this->storeManager = $context->getStoreManager();
        $this->_filesystem = $filesystem;
        $this->_storeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * @param $id
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategoryData($id): \Magento\Catalog\Model\Category
    {
        return $this->categoryFactory->create()->load($id);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPlaceholderImage(): string
    {
        //getting the media base url
        $mediaBaseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $placeholderPath = $this->getPlaceholderImagePath();

        //full url of the placeholder image
        return $mediaBaseUrl . 'catalog/product/placeholder/' . $placeholderPath;
    }

    protected function getPlaceholderImagePath(): string
    {
        return $this->_storeManager->getStore()->getConfig('catalog/placeholder/image_placeholder');
    }

    public function getPlaceholderImageWidth(): int
    {
        $dimensions = $this->getPlaceHolderImageDimensions();

        return $dimensions['width'];
    }

    public function getPlaceholderImageHeight(): int
    {
        $dimensions = $this->getPlaceHolderImageDimensions();

        return $dimensions['height'];
    }

    protected function getPlaceHolderImageDimensions(): array
    {
        $placeholderPath = $this->getPlaceholderImagePath();
        $placeholderImage = 'catalog/product/placeholder/' . $placeholderPath;

        $mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $absolutePath = $mediaDirectory->getAbsolutePath($placeholderImage);


        $imageAdapter = $this->imageAdapterFactory->create();
        $imageAdapter->open($absolutePath);

        return [
            'width' => (int)$imageAdapter->getOriginalWidth(),
            'height' => (int)$imageAdapter->getOriginalHeight()
        ];
    }

    public function getCategoryImageSize($category)
    {
        $imageAttribute = $category->getCustomAttribute('image');
        $image = $imageAttribute ? $imageAttribute->getValue() : null;
        $placeHolderSize = [
            'width' => $this->getPlaceholderImageWidth(),
            'height' => $this->getPlaceholderImageHeight(),
        ];

        if (!$image) {
            return $placeHolderSize;
        }


        $mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::PUB);
        $imagePath = $image;
        $absolutePath = $mediaDirectory->getAbsolutePath($imagePath);

        if (!file_exists($absolutePath)) {
            return $placeHolderSize;
        }

        $imageAdapter = $this->imageAdapterFactory->create();
        $imageAdapter->open($absolutePath);

        return [
            'width' => $imageAdapter->getOriginalWidth(),
            'height' => $imageAdapter->getOriginalHeight()
        ];
    }

}
