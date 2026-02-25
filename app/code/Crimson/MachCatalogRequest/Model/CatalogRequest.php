<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/14/2019 3:45 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Model;

use Crimson\MachCatalogRequest\Api\Data\CatalogRequestInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * @method ResourceModel\CatalogRequest getResource()
 * @method ResourceModel\CatalogRequest\Collection getCollection()
 */
class CatalogRequest extends AbstractModel
    implements CatalogRequestInterface
{
    protected $_cacheTag = 'mach_catalog_request';
    protected $_eventPrefix = 'mach_catalog_request';

    protected function _construct()
    {
        $this->_init('Crimson\MachCatalogRequest\Model\ResourceModel\CatalogRequest');
    }

    /**
     * Get request_id
     * @return string|null
     */
    public function getRequestId(): ?string
    {
        return $this->_getData(self::REQUEST_ID);
    }

    /**
     * Set request_id
     * @param string $requestId
     * @return CatalogRequestInterface
     */
    public function setRequestId($requestId): CatalogRequestInterface
    {
        return $this->setData(self::REQUEST_ID, $requestId);
    }

    /**
     * Get name
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->_getData(self::NAME);
    }

    /**
     * Set name
     * @param string $name
     * @return CatalogRequestInterface
     */
    public function setName($name): CatalogRequestInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get email
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->_getData(self::EMAIL);
    }

    /**
     * Set email
     * @param string $email
     * @return CatalogRequestInterface
     */
    public function setEmail($email): CatalogRequestInterface
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get street
     * @return string|null
     */
    public function getStreet1(): ?string
    {
        return $this->_getData(self::STREET_1);
    }

    /**
     * Set street
     * @param string $street
     * @return CatalogRequestInterface
     */
    public function setStreet1($street): CatalogRequestInterface
    {
        return $this->setData(self::STREET_1, $street);
    }

    /**
     * Get street
     * @return string|null
     */
    public function getStreet2(): ?string
    {
        return $this->_getData(self::STREET_2);
    }

    /**
     * Set street
     * @param string $street
     * @return CatalogRequestInterface
     */
    public function setStreet2($street): CatalogRequestInterface
    {
        return $this->setData(self::STREET_2, $street);
    }

    /**
     * Get city
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->_getData(self::CITY);
    }

    /**
     * Set city
     * @param string $city
     * @return CatalogRequestInterface
     */
    public function setCity($city): CatalogRequestInterface
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * Get zip
     * @return string|null
     */
    public function getZip(): ?string
    {
        return $this->_getData(self::ZIP);
    }

    /**
     * Set zip
     * @param string $zip
     * @return CatalogRequestInterface
     */
    public function setZip($zip): CatalogRequestInterface
    {
        return $this->setData(self::ZIP, $zip);
    }

    /**
     * Get state
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->_getData(self::STATE);
    }

    /**
     * Set state
     * @param string $state
     * @return CatalogRequestInterface
     */
    public function setState($state): CatalogRequestInterface
    {
        return $this->setData(self::STATE, $state);
    }

    /**
     * Get country
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->_getData(self::COUNTRY);
    }

    /**
     * Set country
     * @param string $country
     * @return CatalogRequestInterface
     */
    public function setCountry($country): CatalogRequestInterface
    {
        return $this->setData(self::COUNTRY, $country);
    }

    /**
     * Get request_more
     * @return string|null
     */
    public function getRequestMore(): ?string
    {
        return $this->_getData(self::REQUEST_MORE);
    }

    /**
     * Set request_more
     * @param string $requestMore
     * @return CatalogRequestInterface
     */
    public function setRequestMore($requestMore): CatalogRequestInterface
    {
        return $this->setData(self::REQUEST_MORE, $requestMore);
    }

    /**
     * Get requested_items
     * @return string|null
     */
    public function getRequestedItems(): ?string
    {
        return $this->_getData(self::REQUESTED_ITEMS);
    }

    /**
     * Set requested_items
     * @param string $requestedItems
     * @return CatalogRequestInterface
     */
    public function setRequestedItems($requestedItems): CatalogRequestInterface
    {
        return $this->setData(self::REQUESTED_ITEMS, $requestedItems);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return CatalogRequestInterface
     */
    public function setCreatedAt($createdAt): CatalogRequestInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get modified_at
     * @return string|null
     */
    public function getModifiedAt(): ?string
    {
        return $this->_getData(self::MODIFIED_AT);
    }

    /**
     * Set modified_at
     * @param string $modifiedAt
     * @return CatalogRequestInterface
     */
    public function setModifiedAt($modifiedAt): CatalogRequestInterface
    {
        return $this->setData(self::MODIFIED_AT, $modifiedAt);
    }

    /**
     * @return bool|null
     */
    public function getProcessed(): ?bool
    {
        return $this->_getData(self::PROCESSED);
    }

    /**
     * @param bool $processed
     * @return CatalogRequestInterface|CatalogRequest
     */
    public function setProcessed($processed)
    {
        return $this->setData(self::PROCESSED, $processed);
    }
}
