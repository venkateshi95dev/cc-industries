<?php

namespace Crimson\MachShipping\Plugin;

use Crimson\MachShipping\Model\Carrier\Mach;
use Magento\Quote\Api\Data\ShippingMethodInterfaceFactory;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote\Address\Rate;

class ShippingMethodConverterPlugin
{

    protected ShippingMethodInterfaceFactory $extensionFactory;

    public function __construct(
        ShippingMethodInterfaceFactory $extensionFactory
    )
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param ShippingMethodConverter $subject
     * @param $result
     * @param Rate $rateModel
     * @param $quoteCurrencyCode
     * @return mixed
     */
    public function afterModelToDataObject(ShippingMethodConverter $subject, $result, Rate $rateModel, $quoteCurrencyCode)
    {
        if ($rateModel->getCarrier() === Mach::CODE && $rateModel->getMethodDescription()) {
            $extensionAttribute = $result->getExtensionAttributes()
                ? $result->getExtensionAttributes()
                : $this->extensionFactory->create();

            $extensionAttribute->setDeliveryDate($rateModel->getMethodDescription());
            $result->setExtensionAttributes($extensionAttribute);
        }

        return $result;
    }
}
