<?php
/**
 * @author Arushi Bansal
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ShippingMapping
 */

namespace I95DevConnect\ShippingMapping\Plugin;

use \I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Create;
use \I95DevConnect\ShippingMapping\Api\ShippingMappingManagementInterfaceFactory;
use I95DevConnect\ShippingMapping\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

/**
 * Plugin class responsible for changing the erp shipping method to magento shipping method
 */
class ShippingMethodReverse
{
    /**
     * @var ShippingMappingManagementInterfaceFactory
     */
    public $shippingMgmt;
    /**
     * @var Data
     */
    public $helper;

    /**
     * ShippingMethodReverse constructor.
     *
     * @param ShippingMappingManagementInterfaceFactory $shippingMgmt
     * @param Data $helper
     */
    public function __construct(
        ShippingMappingManagementInterfaceFactory $shippingMgmt,
        Data $helper
    ) {
        $this->shippingMgmt = $shippingMgmt;
        $this->helper = $helper;
    }

    /**
     * Added after plugin to replace the shipping method name
     *
     * @param Create $subject
     * @param string $shippingMethod
     * @return mixed
     * @throws LocalizedException
     */
    public function afterGetShippingMethod(Create $subject, $shippingMethod) //NOSONAR
    {
        if ($this->helper->isEnabled()) {
            $mappedMethod = $this->shippingMgmt->create()->getByErpCode($shippingMethod);
            if (!empty($mappedMethod) && isset($mappedMethod['magento_code'])) {
                if ($mappedMethod['magento_code'] === 'instore_instore') {
                    return 'instore_pickup';
                } else {
                    return $mappedMethod['magento_code'];
                }
            } else {
                throw new LocalizedException(
                    __("(%1) - Matched_magento_ship_method_not_found", $shippingMethod)
                );
            }
        }
        return $shippingMethod;
    }
}
