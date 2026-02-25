<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Api\Data;

/**
 * CMS document interface.
 * @api
 * @since 100.0.2
 */
interface DocumentInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const DOCUMENT_ID              = 'entity_id';
    const TITLE                    = 'title';
    const ATTRIBUTE_CODE           = 'attribute_code';
    const FILE_TYPE                = 'file_type';
    const FILE_SIZE                = 'file_size';
    const FILE_NAME                = 'file_name';
    const FILE                     = 'file';
    const PATH                     = 'path';
    const CREATED_BY               = 'created_by';
    const MODIFIED_BY              = 'modified_by';
    const CREATION_TIME            = 'creation_time';
    const UPDATE_TIME              = 'update_time';
    const IS_ACTIVE                = 'is_active';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    public function getAttributeCode();
    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get document file type
     *
     * @return string|null
     */
    public function getFileType();

    /**
     * Get document file size
     *
     * @return string|null
     * @since 101.0.0
     */
    public function getFileSize();
    /**
     * Get document file name
     *
     * @return string|null
     * @since 101.0.0
     */
    public function getFileName();

    /**
     * Get document file
     *
     * @return string|null
     */
    public function getFile();
    /**
     * Get document path
     *
     * @return string|null
     */
    public function getPath();

    /**
     * Get created by
     *
     * @return string|null
     */
    public function getCreatedBy();

    /**
     * Get modified by
     *
     * @return string|null
     */
    public function getModifiedBy();

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime();


    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setId($id);

    /**
     * Set attribute_cdoe
     *
     * @param string $attribute_code
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setAttributeCode($attribute_cdoe);
    /**
     * Set title
     *
     * @param string $title
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setTitle($title);

    /**
     * Set document file type
     *
     * @param string $fileType
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setFileType($fileType);

    /**
     * Set document file size
     *
     * @param string $fileSize
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     * @since 101.0.0
     */
    public function setFileSize($fileSize);
    /**
     * Set document file name
     *
     * @param string $fileName
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     * @since 101.0.0
     */
    public function setFileName($fileName);

    /**
     * Set document file
     *
     * @param string $file
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setFile($file);

    /**
     * Set document path
     *
     * @param string $path
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setPath($path);

    /**
     * Set created by
     *
     * @param string $createdBy
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setCreatedBy($createdBy);

    /**
     * Set modified by
     *
     * @param string $modifiedBy
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setModifiedBy($modifiedBy);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     */
    public function setIsActive($isActive);
}
