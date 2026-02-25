<?php
/**
 * @author Arushi Bansal
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ShippingMapping
 */

namespace I95DevConnect\ShippingMapping\Model;

use \I95DevConnect\ShippingMapping\Api\ShippingMappingManagementInterface;
use \I95DevConnect\ShippingMapping\Api\Data\ShippingMappingDataInterfaceFactory;
use \I95DevConnect\ShippingMapping\Model\ResourceModel\ShippingMappingFactory;

/**
 * Performs operation related to shipping mapping
 */
class ShippingMappingManagement implements ShippingMappingManagementInterface
{

    public const ISECOMMERCEDEFAULT= 'is_ecommerce_default';

    /**
     * @var ShippingMappingDataInterfaceFactory
     */
    public $shippingMappingData;

    /**
     * @var ShippingMappingFactory
     */
    public $shippingMappingResource;

    /**
     * Shipping mapping management constructor
     *
     * @param ShippingMappingDataInterfaceFactory $shippingMappingData
     * @param ShippingMappingFactory $shippingMappingResource
     */
    public function __construct(
        ShippingMappingDataInterfaceFactory $shippingMappingData,
        ShippingMappingFactory $shippingMappingResource
    ) {

        $this->shippingMappingResource = $shippingMappingResource;
        $this->shippingMappingData = $shippingMappingData;
    }

    /**
     * Insert the data in shipping mapping table
     *
     * @param string $shipping_mapping_list
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processMappingData($shipping_mapping_list)
    {
        try {
            $this->shippingMappingResource->create()->truncateTable();

            if (is_string($shipping_mapping_list)) {
                $shipping_mapping_list = json_decode($shipping_mapping_list);
            }

            foreach ($shipping_mapping_list as $shipping_mapping_cloud_data) {
                $prepareDataObject = $this->shippingMappingData->create();
                $prepareDataObject->setMagentoCode(trim($shipping_mapping_cloud_data->ecommerceMethod));
                $prepareDataObject->setErpCode(trim($shipping_mapping_cloud_data->erpMethod));
                $prepareDataObject->setIsErpDefault(trim($shipping_mapping_cloud_data->isErpDefault));
                $prepareDataObject->setIsEcommerceDefault(trim($shipping_mapping_cloud_data->isEcommerceDefault));
                $this->shippingMappingResource->create()->save($prepareDataObject);
            }

            return true;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }

        return false;
    }

    /**
     * Get erp shipping method code using magento shipping method
     *
     * @param string $magentoshipCode
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByMagentoCode($magentoshipCode)
    {
        try {
            $codeArray = $this->shippingMappingData->create()->getCollection()
                ->addFieldToSelect(['is_erp_default', 'erp_code'])
                ->addFieldToFilter('magento_code', $magentoshipCode)
                ->getData();

            return $this->fetchCode($codeArray, 'is_erp_default');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Get magento shipping method code using erp shipping method
     *
     * @param string $erpshipCode
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByErpCode($erpshipCode)
    {
        try {

            $codeArray = $this->shippingMappingData->create()->getCollection()
                ->addFieldToSelect([self::ISECOMMERCEDEFAULT, 'magento_code'])
                ->addFieldToFilter('erp_code', $erpshipCode)
                ->getData();

            return $this->fetchCode($codeArray, self::ISECOMMERCEDEFAULT);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {

            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Fetch code
     *
     * @param array $codeArray
     * @param bool $isDefault
     * @return string
     */
    public function fetchCode($codeArray, $isDefault)
    {
        $return_val = "";
        if (!empty($codeArray)) {
            foreach ($codeArray as $code) {
                $return_val = $code;
                if ($code[$isDefault]) {
                    return $code;
                }
            }
        }
        return $return_val;
    }
}
