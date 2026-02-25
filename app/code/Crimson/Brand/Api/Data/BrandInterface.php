<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/10/2019
 */
namespace Crimson\Brand\Api\Data;

interface BrandInterface
{
    const ID = 'brand_id';
    const NAME = 'name';
    const SMALL_IMAGE = 'small_image';
    const LARGE_IMAGE = 'large_image';
    const DESCRIPTION = 'description';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const URL_KEY = 'url_key';
    const CMS_ID = 'cms_id';
    const DISPLAY = 'display';

    /**
     * Get brand id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set brand id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get small image
     *
     * @return string|null
     */
    public function getSmallImage();

    /**
     * Set small image
     *
     * @param string $smallImage
     * @return $this
     */
    public function setSmallImage($smallImage);

    /**
     * Get large image
     *
     * @return string|null
     */
    public function getLargeImage();

    /**
     * Set large image
     *
     * @param string $largeImage
     * @return $this
     */
    public function setLargeImage($largeImage);

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get created at time
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created at time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at time
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated at time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get url key
     *
     * @return string|null
     */
    public function getUrlKey();

    /**
     * Set url key
     *
     * @param string $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey);

    /**
     * Get cms page id
     *
     * @return string|null
     */
    public function getCmsId();

    /**
     * Set cms page id
     *
     * @param string $cmsId
     * @return $this
     */
    public function setCmsId($cmsId);

	/**
	 * @return mixed
	 */
	public function getDisplay();

	/**
	 * @param $display
	 * @return mixed
	 */
	public function setDisplay($display);
}