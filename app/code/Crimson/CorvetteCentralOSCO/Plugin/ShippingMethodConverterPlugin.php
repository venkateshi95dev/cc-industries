<?php

namespace Crimson\CorvetteCentralOSCO\Plugin;

use Crimson\CorvetteCentralOSCO\Model\Carrier\Osco;
use Magento\Quote\Api\Data\ShippingMethodInterfaceFactory;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote\Address\Rate;

class ShippingMethodConverterPlugin
{

    public function __construct(
        protected ShippingMethodInterfaceFactory $extensionFactory
    ) {}

    public function afterModelToDataObject(ShippingMethodConverter $subject, $result, Rate $rateModel, $quoteCurrencyCode)
    {
        if ($rateModel->getCarrier() === Osco::CODE && $rateModel->getMethodDescription()) {
            $extensionAttribute = $result->getExtensionAttributes()
                ? $result->getExtensionAttributes()
                : $this->extensionFactory->create();

            $extensionAttribute->setOscoDeliveryDate($rateModel->getMethodDescription());
            $result->setExtensionAttributes($extensionAttribute);
        }

        return $result;
    }
}
