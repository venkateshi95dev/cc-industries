<?php
/**
 * @namespace   Crimson
 * @module      MachOrderFees
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        5/15/2019 2:16 PM
 * @brief
 */

namespace Crimson\MachOrderFees\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Model\Cart;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Class PaymentCartCollectItemsAndAmounts
 * @package Crimson\MachOrderFees\Observer
 */
class PaymentCartCollectItemsAndAmounts implements ObserverInterface
{
    /**
     * @var Json
     */
    protected $json;

    public function __construct(
        Json $json
    ) {
        $this->json = $json;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Cart $cart */
        /** @noinspection PhpUndefinedMethodInspection */
        $cart = $observer->getEvent()->getCart();

        //add handling or core charges, if present.
        $additionalHandlingAmount = $this->_getAdditionalHandlingFromObject($cart);
        if ($additionalHandlingAmount >= 0.01) {
            $cart->addCustomItem(__('Additional Handling'), 1, $additionalHandlingAmount, 'ADDTL_HANDLING');
        }

        $coreChargeSkus = $this->_getCoreChargeData($cart);
        if ($coreChargeSkus) {
            if (!is_array($coreChargeSkus)) {
                $coreChargeSkus = $this->json->unserialize($coreChargeSkus);
            }
            foreach ($coreChargeSkus as $index => $coreChargeData) {
                $coreChargeSku = $coreChargeData['core_charge_sku'] ?? null;
                if (!$coreChargeSku) {
                    //backwards compatibility with sku as index.
                    if (!is_numeric($index)) {
                        $coreChargeSku = $index;
                    }
                }

                $qty    = $coreChargeData['qty'];
                $amount = $coreChargeData['core_charge_amount'];
                $cart->addCustomItem('Core Charge - ' . $coreChargeSku, $qty, $amount, $coreChargeSku);
            }
        }
    }

    /**
     * @param Cart $cart
     * @return mixed
     */
    protected function _getAdditionalHandlingFromObject(Cart $cart)
    {
        if ($cart->getSalesModel()->getTaxContainer() instanceof AddressInterface) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $cart->getSalesModel()->getTaxContainer()->getDataUsingMethod('additional_handling_amount');
        } else {
            return $cart->getSalesModel()->getDataUsingMethod('additional_handling_amount');
        }
    }

    /**
     * @param Cart $cart
     * @return mixed
     */
    protected function _getCoreChargeData(Cart $cart)
    {
        if ($cart->getSalesModel()->getTaxContainer() instanceof AddressInterface) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $cart->getSalesModel()->getTaxContainer()->getDataUsingMethod('core_charge_sku_list');
        } else {
            return $cart->getSalesModel()->getDataUsingMethod('core_charge_sku_list');
        }
    }
}
