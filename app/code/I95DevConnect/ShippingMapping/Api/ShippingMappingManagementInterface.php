<?php
/**
 * @author Arushi Bansal
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ShippingMapping
 */

namespace I95DevConnect\ShippingMapping\Api;

/**
 * Management Interface for Shipping Mapping
 */
interface ShippingMappingManagementInterface
{

    /**
     * Data insertion into shipping mapping table
     *
     * @param string $data
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processMappingData($data);

    /**
     * Get the magento shipping method code from mapping table
     *
     * @param string $magentoshipCode
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByMagentoCode($magentoshipCode);

    /**
     * Get the erp shipping method code from mapping table
     *
     * @param string $erpshipCode
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByErpCode($erpshipCode);
}
