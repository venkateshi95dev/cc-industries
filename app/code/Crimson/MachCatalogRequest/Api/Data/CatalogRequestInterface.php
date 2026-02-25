<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/14/2019 4:09 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Api\Data;

/**
 * Interface CatalogRequestInterface
 * @package Crimson\MachCatalogRequest\Api\Data
 */
interface CatalogRequestInterface
{

    const CREATED_AT = 'created_at';
    const REQUEST_MORE = 'request_more';
    const REQUESTED_ITEMS = 'requested_items';
    const EMAIL = 'email';
    const MODIFIED_AT = 'modified_at';
    const COUNTRY = 'country';
    const STREET_1 = 'street_1';
    const STREET_2 = 'street_2';
    const CITY = 'city';
    const STATE = 'state';
    const ZIP = 'zip';
    const NAME = 'name';
    const REQUEST_ID = 'request_id';
    const PROCESSED = 'processed';

    /**
     * Get request_id
     * @return string|null
     */
    public function getRequestId();

    /**
     * Set request_id
     * @param string $requestId
     * @return CatalogRequestInterface
     */
    public function setRequestId($requestId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return CatalogRequestInterface
     */
    public function setName($name);

    /**
     * Get email
     * @return string|null
     */
    public function getEmail();

    /**
     * Set email
     * @param string $email
     * @return CatalogRequestInterface
     */
    public function setEmail($email);

    /**
     * Get street
     * @return string|null
     */
    public function getStreet1();

    /**
     * Set street
     * @param string $street
     * @return CatalogRequestInterface
     */
    public function setStreet1($street);

    /**
     * Get street
     * @return string|null
     */
    public function getStreet2();

    /**
     * Set street
     * @param string $street
     * @return CatalogRequestInterface
     */
    public function setStreet2($street);

    /**
     * Get city
     * @return string|null
     */
    public function getCity();

    /**
     * Set city
     * @param string $city
     * @return CatalogRequestInterface
     */
    public function setCity($city);

    /**
     * Get zip
     * @return string|null
     */
    public function getZip();

    /**
     * Set zip
     * @param string $zip
     * @return CatalogRequestInterface
     */
    public function setZip($zip);

    /**
     * Get state
     * @return string|null
     */
    public function getState();

    /**
     * Set state
     * @param string $state
     * @return CatalogRequestInterface
     */
    public function setState($state);

    /**
     * Get country
     * @return string|null
     */
    public function getCountry();

    /**
     * Set country
     * @param string $country
     * @return CatalogRequestInterface
     */
    public function setCountry($country);

    /**
     * Get request_more
     * @return string|null
     */
    public function getRequestMore();

    /**
     * Set request_more
     * @param string $requestMore
     * @return CatalogRequestInterface
     */
    public function setRequestMore($requestMore);

    /**
     * Get requested_items
     * @return string|null
     */
    public function getRequestedItems();

    /**
     * Set requested_items
     * @param string $requestedItems
     * @return CatalogRequestInterface
     */
    public function setRequestedItems($requestedItems);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return CatalogRequestInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get modified_at
     * @return string|null
     */
    public function getModifiedAt();

    /**
     * Set modified_at
     * @param string $modifiedAt
     * @return CatalogRequestInterface
     */
    public function setModifiedAt($modifiedAt);

    /**
     * Get modified_at
     * @return bool|null
     */
    public function getProcessed();

    /**
     * @param boolean $processed
     * @return CatalogRequestInterface
     */
    public function setProcessed($processed);
}
