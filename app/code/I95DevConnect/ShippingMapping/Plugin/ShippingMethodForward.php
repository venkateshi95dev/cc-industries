<?php
/**
 * @author Arushi Bansal
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ShippingMapping
 */

namespace I95DevConnect\ShippingMapping\Plugin;

use \I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\ShippingAddress;
use \I95DevConnect\ShippingMapping\Api\ShippingMappingManagementInterfaceFactory;
use I95DevConnect\ShippingMapping\Helper\Data;
use \Magento\Framework\App\ProductMetadataInterface;

/**
 * Plugin class responsible for changing the magento shipping method to erp shipping method
 */
class ShippingMethodForward
{
    /**
     * @var ProductMetadataInterface
     */
    public $productMetadata;

    /**
     * @var ShippingMappingManagementInterfaceFactory
     */
    public $shippingMgmt;

    /**
     * @var Data
     */
    public $helper;

    /**
     * ShippingMethodForward constructor.
     *
     * @param ShippingMappingManagementInterfaceFactory $shippingMgmt
     * @param Data $helper
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ShippingMappingManagementInterfaceFactory $shippingMgmt,
        Data $helper,
        ProductMetadataInterface $productMetadata
    ) {
        $this->shippingMgmt = $shippingMgmt;
        $this->helper = $helper;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Added after plugin to replace the shipping method name
     *
     * @param ShippingAddress $subject
     * @param string $shippingMethod
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetShippingMethod(ShippingAddress $subject, $shippingMethod) //NOSONAR
    {
        if ($this->helper->isEnabled()) {
            $productVersion = $this->productMetadata->getVersion();
            if ($productVersion <= '2.4.5' && $shippingMethod === 'instore_pickup') {
                $shippingMethod = 'instore_instore';
            }
            $mappedMethod = $this->shippingMgmt->create()->getByMagentoCode($shippingMethod);

            if (!empty($mappedMethod) && isset($mappedMethod['erp_code'])) {
                return $mappedMethod['erp_code'];
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("(%1) - Matched_erp_ship_method_not_found", $shippingMethod)
                );
            }
        }

        return $shippingMethod;
    }
}
