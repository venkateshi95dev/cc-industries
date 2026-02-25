<?php

namespace Crimson\MachAddressVerification\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Model\Order;

/**
 * Class SetAddressValidationFieldsOnOrder
 * @package Crimson\MachAddressVerification\Observer
 */
class SetAddressValidationFieldsOnOrder implements ObserverInterface
{

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): SetAddressValidationFieldsOnOrder
    {
        /* @var $quote Quote */
        $quote = $observer->getEvent()->getQuote();
        if ($quote->getShippingAddress()->getCountryId() != "US") {
            return $this;
        }

        /** @var $shippingAddress Address */
        /** @var $quoteBillingAddress Address */
        $shippingAddress = $quote->getShippingAddress();
        $quoteBillingAddress = $quote->getBillingAddress();


        /* @var $order Order */
        $order = $observer->getEvent()->getOrder();
        $orderShippingAddress = $order->getShippingAddress();
        $orderBillingAddress  = $order->getBillingAddress();

        if ($shippingAddress && $orderShippingAddress && $shippingAddress->getExtensionAttributes()) {

            if ($shippingAddress->getExtensionAttributes()->getShipAdv()) {
                $order->getShippingAddress()->setShipAdv($shippingAddress->getExtensionAttributes()->getShipAdv());
            }

            if ($shippingAddress->getExtensionAttributes()->getShipAdvDate()) {
                $order->getShippingAddress()->setShipAdvDate($shippingAddress->getExtensionAttributes()->getShipAdvDate());
            }

            if ($shippingAddress->getExtensionAttributes()->getShipAdvDpi()) {
                $order->getShippingAddress()->setShipAdvDpi($shippingAddress->getExtensionAttributes()->getShipAdvDpi());
            }

            if ($shippingAddress->getExtensionAttributes()->getShipAdvDi()) {
                $order->getShippingAddress()->setShipAdvDi($shippingAddress->getExtensionAttributes()->getShipAdvDi());
            }

            //setting the info on the billing address if shipping = billing
            if ($orderBillingAddress && ($shippingAddress->getSameAsBilling() || $this->_areShippingAndBillingSame($quoteBillingAddress, $shippingAddress))) {
                if ($shippingAddress->getExtensionAttributes()->getShipAdv()) {
                    $order->getBillingAddress()->setShipAdv($shippingAddress->getExtensionAttributes()->getShipAdv());
                }

                if ($shippingAddress->getExtensionAttributes()->getShipAdvDate()) {
                    $order->getBillingAddress()->setShipAdvDate($shippingAddress->getExtensionAttributes()->getShipAdvDate());
                }

                if ($shippingAddress->getExtensionAttributes()->getShipAdvDpi()) {
                    $order->getBillingAddress()->setShipAdvDpi($shippingAddress->getExtensionAttributes()->getShipAdvDpi());
                }

                if ($shippingAddress->getExtensionAttributes()->getShipAdvDi()) {
                    $order->getBillingAddress()->setShipAdvDi($shippingAddress->getExtensionAttributes()->getShipAdvDi());
                }
            }
        }

        return $this;
    }

    /**
     * @param Address $billingAddress
     * @param Address $shippingAddress
     * @return bool
     */
    protected function _areShippingAndBillingSame(Address $billingAddress, Address $shippingAddress): bool
    {
        $shippingAddressMainFields = [
            "street"      => $shippingAddress->getStreet(),
            "city"        => $shippingAddress->getCity(),
            "region"      => $shippingAddress->getRegion(),
            "postcode"    => $shippingAddress->getPostcode(),
            "country_is"  => $shippingAddress->getCountryId(),
        ];

        $billingAddressMainFields = [
            "street"      => $billingAddress->getStreet(),
            "city"        => $billingAddress->getCity(),
            "region"      => $billingAddress->getRegion(),
            "postcode"    => $billingAddress->getPostcode(),
            "country_is"  => $billingAddress->getCountryId(),
        ];

        return $shippingAddressMainFields == $billingAddressMainFields;
    }
}
