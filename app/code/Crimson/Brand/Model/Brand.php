<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/17/2019
 */
namespace Crimson\Brand\Model;

use Crimson\Brand\Api\Data\BrandInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Exception\LocalizedException;

class Brand extends AbstractModel implements BrandInterface
{
    const CACHE_TAG = 'crimson_brand_brand';

    /**
     * @var UploaderPool
     */
    protected $uploaderPool;

    /**
     * Brand constructor.
     * @param Context $context
     * @param Registry $registry
     * @param UploaderPool $uploaderPool
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        UploaderPool $uploaderPool,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->uploaderPool = $uploaderPool;
    }

    protected function _construct()
    {
        $this->_init('Crimson\Brand\Model\ResourceModel\Brand');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getName()
    {
        return $this->getData(self::NAME);
    }

    public function setName($name)
    {
        $this->setData(self::NAME, $name);
    }

    public function getSmallImage()
    {
        return $this->getData(self::SMALL_IMAGE);
    }

    public function setSmallImage($smallImage)
    {
        $this->setData(self::SMALL_IMAGE, $smallImage);
    }

    public function getLargeImage()
    {
        return $this->getData(self::LARGE_IMAGE);
    }

    public function setLargeImage($largeImage)
    {
        $this->setData(self::LARGE_IMAGE, $largeImage);
    }

    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    public function setDescription($description)
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }

    public function getUrlKey()
    {
        return $this->getData(self::URL_KEY);
    }

    public function setUrlKey($urlKey)
    {
        $this->setData(self::URL_KEY, $urlKey);
    }

    public function getCmsId()
    {
        return $this->getData(self::CMS_ID);
    }

    public function setCmsId($cmsId)
    {
        $this->setData(self::CMS_ID, $cmsId);
    }

	public function setDisplay($display)
	{
		$this->setData(self::DISPLAY, $display);
	}

	public function getDisplay()
	{
		return $this->getData(self::DISPLAY);
	}

    /**
     * Get small image URL
     *
     * @return bool|string
     * @throws LocalizedException
     */
    public function getSmallImageUrl()
    {
        $url = false;
        $image = $this->getSmallImage();
        if ($image) {
            if (is_string($image)) {
                $uploader = $this->uploaderPool->getUploader('image');
                $url = $uploader->getBaseUrl().$uploader->getBasePath().$image;
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the small image url.')
                );
            }
        }
        return $url;
    }

    /**
     * Get large image URL
     *
     * @return bool|string
     * @throws LocalizedException
     */
    public function getLargeImageUrl()
    {
        $url = false;
        $image = $this->getLargeImage();
        if ($image) {
            if (is_string($image)) {
                $uploader = $this->uploaderPool->getUploader('image');
                $url = $uploader->getBaseUrl().$uploader->getBasePath().$image;
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the large image url.')
                );
            }
        }
        return $url;
    }
}