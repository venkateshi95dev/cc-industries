<?php
/**
 * @author Arushi Bansal
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ShippingMapping
 */

namespace I95DevConnect\ShippingMapping\Api\Data;

/**
 * Represents Data Object for a Shipping Mapping
 */
interface ShippingMappingDataInterface
{

    public const ID = 'id';
    public const ERP_CODE = 'erp_code';
    public const MAGENTO_CODE = 'magento_code';
    public const IS_ERP_DEFAULT = 'is_erp_default';
    public const IS_ECOMMERCE_DEFAULT = 'is_ecommerce_default';

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $Id
     * @return mixed
     */
    public function setId($Id);

    /**
     * Get erp code
     *
     * @return int|null
     */
    public function getErpCode();

    /**
     * Set erp code
     *
     * @param string $erpCode
     * @return $this
     */
    public function setErpCode($erpCode);

    /**
     * Get magento code
     *
     * @return string
     */
    public function getMagentoCode();

    /**
     * Get magento code
     *
     * @param string $magentoCode
     * @return $this
     */
    public function setMagentoCode($magentoCode);

    /**
     * Get is erp default
     *
     * @return string
     */
    public function getIsErpDefault();

    /**
     * Set is erp default
     *
     * @param string $isErpDefault
     * @return $this
     */
    public function setIsErpDefault($isErpDefault);

    /**
     * Get is ecommerce default
     *
     * @return string
     */
    public function getIsEcommerceDefault();

    /**
     * Set is ecommerce default
     *
     * @param string $isEcommerceDefault
     * @return $this
     */
    public function setIsEcommerceDefault($isEcommerceDefault);
}
