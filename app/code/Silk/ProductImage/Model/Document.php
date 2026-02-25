<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Model;

use Silk\ProductImage\Api\Data\DocumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

/**
 * Cms Document Model
 *
 * @api
 * @method Document setStoreId(int $storeId)
 * @method int getStoreId()
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @since 100.0.2
 */
class Document extends AbstractModel implements DocumentInterface
{
    /**
     * No route document id
     */
    const NOROUTE_DOCUMENT_ID = 'no-route';

    /**#@+
     * Document's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * CMS document cache tag
     */
    const CACHE_TAG = 'img_d';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'img_document';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Silk\ProductImage\Model\ResourceModel\Document::class);
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteDocument();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Document
     *
     * @return \Silk\ProductImage\Model\Document
     */
    public function noRouteDocument()
    {
        return $this->load(self::NOROUTE_DOCUMENT_ID, $this->getIdFieldName());
    }

    /**
     * Prepare document's statuses, available event img_document_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::DOCUMENT_ID);
    }

    /**
     * Get attribute code
     *
     * @return string
     */
    public function getAttributeCode()
    {
        return parent::getData(self::ATTRIBUTE_CODE);
    }
    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get file type
     *
     * @return string
     */
    public function getFileType()
    {
        return $this->getData(self::FILE_TYPE);
    }

    /**
     * Get file size
     *
     * @return string|null
     * @since 101.0.0
     */
    public function getFileSize()
    {
        return $this->getData(self::FILE_SIZE);
    }
    /**
     * Get file name
     *
     * @return string|null
     * @since 101.0.0
     */
    public function getFileName()
    {
        return $this->getData(self::FILE_NAME);
    }
    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->getData(self::FILE);
    }
    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getData(self::PATH);
    }
    /**
     * Get created by
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->getData(self::CREATED_BY);
    }

    /**
     * Get modified by
     *
     * @return string
     */
    public function getModifiedBy()
    {
        return $this->getData(self::MODIFIED_BY);
    }

    /**
     * Get creation time
     *
     * @return string
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setId($id)
    {
        return $this->setData(self::DOCUMENT_ID, $id);
    }
    /**
     * Set attribute code
     *
     * @param string $attribute_code
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setAttributeCode($attribute_code)
    {
        return $this->setData(self::ATTRIBUTE_CODE, $attribute_code);
    }
    /**
     * Set title
     *
     * @param string $title
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set file type
     *
     * @param string $setFileType
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setFileType($setFileType)
    {
        return $this->setData(self::FILE_TYPE, $setFileType);
    }

    /**
     * Set file size
     *
     * @param string $fileSize
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     * @since 101.0.0
     */
    public function setFileSize($fileSize)
    {
        return $this->setData(self::FILE_SIZE, $fileSize);
    }
    /**
     * Set file name
     *
     * @param string $fileName
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     * @since 101.0.0
     */
    public function setFileName($fileName)
    {
        return $this->setData(self::FILE_NAME, $fileName);
    }

    /**
     * Set file
     *
     * @param string $file
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setFile($file)
    {
        return $this->setData(self::FILE, $file);
    }
    /**
     * Set path
     *
     * @param string $path
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setPath($path)
    {
        return $this->setData(self::FILE, $path);
    }
    /**
     * Set created by
     *
     * @param string $createdBy
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setCreatedBy($createdBy)
    {
        return $this->setData(self::CREATED_BY, $createdBy);
    }

    /**
     * Set modified by
     *
     * @param string $contentHeading
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setModifiedBy($modifiedBy)
    {
        return $this->setData(self::MODIFIED_BY, $modifiedBy);
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }
    public function getFilePath(){
        return $this->getData(self::PATH).'/'.$this->getData(self::FILE_NAME);
    }
    /**
     * Retrieve array of product id's for document
     *
     * The array returned has the following format:
     * array($productId => $position)
     *
     * @return array
     */
    public function getProductsPosition()
    {
        if (!$this->getId()) {
            return [];
        }

        $array = $this->getData('products_position');
        if ($array === null) {
            $array = $this->getResource()->getProductsPosition($this);
            $this->setData('products_position', $array);
        }
        return $array;
    }
    public function loadByProductId($productId)
    {
        return $this->_getResource()->loadByProductId($productId);
    }
}
