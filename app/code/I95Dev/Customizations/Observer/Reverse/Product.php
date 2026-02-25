<?php
namespace I95Dev\Customizations\Observer\Reverse;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Product implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $currentObject = $observer->getEvent()->getData('currentObject');

        // Read ERP attribute "hasDiscount" from incoming data
        $hasDiscount = $currentObject->dataHelper->getValueFromArray('hasDiscount', $currentObject->stringData);
        $hasDiscount = (int) ($hasDiscount ?? 0);
        $currentObject->productInterface->setCustomAttribute('cc_hasdiscount', $hasDiscount);

        $ccSearchdescription = $currentObject->dataHelper->getValueFromArray('searchDescription', $currentObject->stringData);
        $currentObject->productInterface->setCustomAttribute('cc_searchdescription', $ccSearchdescription);

        $gmItemNo = $currentObject->dataHelper->getValueFromArray('gmItemNo', $currentObject->stringData);
        $currentObject->productInterface->setCustomAttribute('cc_gmpartnumber', $gmItemNo);
        $paragonItemNo = $currentObject->dataHelper->getValueFromArray('paragonItemNo', $currentObject->stringData);
        $currentObject->productInterface->setCustomAttribute('cc_paragonitemno', $paragonItemNo);
        $ccNetWeight = $currentObject->dataHelper->getValueFromArray('ccNetWeight', $currentObject->stringData);
        $currentObject->productInterface->setCustomAttribute('cc_net_weight', $ccNetWeight);
        $ccEcommerceLeadDays = $currentObject->dataHelper->getValueFromArray('ecommerceLeadDays', $currentObject->stringData);
        $currentObject->productInterface->setCustomAttribute('cc_ecommerce_lead_days', $ccEcommerceLeadDays);

        $airShipmentOk = $currentObject->dataHelper->getValueFromArray('airShipmentOk', $currentObject->stringData);
        $airShipmentOk = (int) ($airShipmentOk ?? 0);
        $currentObject->productInterface->setCustomAttribute('cc_airshipmentok', $airShipmentOk);

        $lastEcommDateChange = $currentObject->dataHelper->getValueFromArray('lastEcommDateChange', $currentObject->stringData);
        $lastEcommDateChange = (!empty($lastEcommDateChange)) ? date('Y-m-d', strtotime($lastEcommDateChange)) : null;
        $currentObject->productInterface->setCustomAttribute('cc_ecomleaddays_lastupdate_date', $lastEcommDateChange);

        $shippingHeight = $currentObject->dataHelper->getValueFromArray('shippingHeight', $currentObject->stringData);
        $currentObject->productInterface->setCustomAttribute('cc_height', $shippingHeight);

        $shippingLength = $currentObject->dataHelper->getValueFromArray('shippingLength', $currentObject->stringData);
        $currentObject->productInterface->setCustomAttribute('cc_length', $shippingLength);

        $shippingWidth = $currentObject->dataHelper->getValueFromArray('shippingWidth', $currentObject->stringData);
        $currentObject->productInterface->setCustomAttribute('cc_width', $shippingWidth);


    }
}
