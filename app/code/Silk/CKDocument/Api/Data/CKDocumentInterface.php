<?php
namespace Silk\CKDocument\Api\Data;

/**
 * CMS document interface.
 * @api
 * @since 100.0.2
 */
interface CKDocumentInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const DOCUMENT_ID              = 'document_id';
    const TITLE                    = 'title';
    const FILE_TYPE                = 'file_type';
    const FILE_SIZE                = 'file_size';
    const FILE_NAME                = 'file_name';
    const FILE                     = 'file';
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
     * @return \Silk\CKDocument\Api\Data\CKDocumentInterface
     */
    public function setId($id);

    /**
     * Set title
     *
     * @param string $title
     * @return \Silk\CKDocument\Api\Data\CKDocumentInterface
     */
    public function setTitle($title);

    /**
     * Set document file type
     *
     * @param string $fileType
     * @return \Silk\CKDocument\Api\Data\CKDocumentInterface
     */
    public function setFileType($fileType);

    /**
     * Set document file size
     *
     * @param string $fileSize
     * @return \Silk\CKDocument\Api\Data\CKDocumentInterface
     * @since 101.0.0
     */
    public function setFileSize($fileSize);
    /**
     * Set document file name
     *
     * @param string $fileName
     * @return \Silk\CKDocument\Api\Data\CKDocumentInterface
     * @since 101.0.0
     */
    public function setFileName($fileName);

    /**
     * Set document file
     *
     * @param string $file
     * @return \Silk\CKDocument\Api\Data\CKDocumentInterface
     */
    public function setFile($file);

    /**
     * Set created by
     *
     * @param string $createdBy
     * @return \Silk\CKDocument\Api\Data\CKDocumentInterface
     */
    public function setCreatedBy($createdBy);

    /**
     * Set modified by
     *
     * @param string $modifiedBy
     * @return \Silk\CKDocument\Api\Data\CKDocumentInterface
     */
    public function setModifiedBy($modifiedBy);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Silk\CKDocument\Api\Data\CKDocumentInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Silk\CKDocument\Api\Data\CKDocumentInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Silk\CKDocument\Api\Data\CKDocumentInterface
     */
    public function setIsActive($isActive);
}
