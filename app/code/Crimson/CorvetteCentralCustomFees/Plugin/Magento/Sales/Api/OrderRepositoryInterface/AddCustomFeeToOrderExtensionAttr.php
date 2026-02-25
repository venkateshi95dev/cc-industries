<?php
namespace Crimson\CorvetteCentralCustomFees\Plugin\Magento\Sales\Api\OrderRepositoryInterface;

use Crimson\CorvetteCentralCustomFees\Api\Data\CanadaTaxItemInterface;
use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderRepositoryPlugin
 */
class AddCustomFeeToOrderExtensionAttr
{
    public const FIELD_NAME = 'custom_fee_core_charge';

    public function __construct(
        private OrderExtensionFactory $orderExtensionFactory,
        private \Magento\Framework\Serialize\SerializerInterface $serializer,
        private \Crimson\CorvetteCentralCustomFees\Api\Data\CanadaTaxItemInterfaceFactory $taxItemInterfaceFactory
    ) {
    }

    /**
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $this->setCorvetteCentralCustomFees($order);
        return $order;
    }

    /**
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult)
    {
        $orders = $searchResult->getItems();

        foreach ($orders as $order) {
            $this->setCorvetteCentralCustomFees($order);
        }

        return $searchResult;
    }

    public function beforeSave(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        if($order instanceof DataObject) {
            $order->setData('base_custom_fee_core_charge', $order->getExtensionAttributes()?->getBaseCustomFeeCoreCharge());
            $order->setData('custom_fee_core_charge', $order->getExtensionAttributes()?->getCustomFeeCoreCharge());
            $order->setData('base_custom_fee_crate_charge', $order->getExtensionAttributes()?->getBaseCustomFeeCrateCharge());
            $order->setData('custom_fee_crate_charge', $order->getExtensionAttributes()?->getCustomFeeCrateCharge());
            $order->setData('base_custom_fee_freight_charge', $order->getExtensionAttributes()?->getBaseCustomFeeFreightCharge());
            $order->setData('custom_fee_freight_charge', $order->getExtensionAttributes()?->getCustomFeeFreightCharge());
            $order->setData('base_custom_fee_dropship_charge', $order->getExtensionAttributes()?->getBaseCustomFeeDropshipCharge());
            $order->setData('custom_fee_dropship_charge', $order->getExtensionAttributes()?->getCustomFeeDropshipCharge());
            $order->setData('base_custom_fee_truck_frt_set_price', $order->getExtensionAttributes()?->getBaseCustomFeeTruckFrtSetPrice());
            $order->setData('custom_fee_truck_frt_set_price', $order->getExtensionAttributes()?->getCustomFeeTruckFrtSetPrice());
            $order->setData('is_canadian_freight', $order->getExtensionAttributes()?->getIsCanadianFreight());
            $order->setData('is_truck_frt_0070', $order->getExtensionAttributes()?->getIsTruckFrt0070());
            $this->_saveCanadaTaxes($order);
        }
    }
    private function _saveCanadaTaxes($order): void
    {
        $ext = $order->getExtensionAttributes();
        if ($ext && method_exists($ext, 'getCanadaTaxes') && $ext->getCanadaTaxes()) {
            $taxItems = $ext->getCanadaTaxes();
            $payload = [];
            foreach ($taxItems as $taxItem) {
                $payload[] = [
                    'code' => $taxItem->getCode(),
                    'value' => $taxItem->getValue()
                ];
            }
            $json = $this->serializer->serialize($payload);
            $order->setData('canada_taxes', $json);
        }
        if ($ext && method_exists($ext, 'getBaseCanadaTaxes') && $ext->getBaseCanadaTaxes()) {
            $taxItems = $ext->getBaseCanadaTaxes();
            $payload = [];
            foreach ($taxItems as $taxItem) {
                $payload[] = [
                    'code' => $taxItem->getCode(),
                    'value' => $taxItem->getValue()
                ];
            }
            $json = $this->serializer->serialize($payload);
            $order->setData('base_canada_taxes', $json);
        }
    }

    public function setCorvetteCentralCustomFees(OrderInterface $order): void
    {
        if (!$order instanceof DataObject) {
            return;
        }


        $extensionAttributes = $order->getExtensionAttributes();
        if (!$extensionAttributes) {
            $extensionAttributes = $this->orderExtensionFactory->create();
            $order->setExtensionAttributes($extensionAttributes);
        }

        $extensionAttributes->setBaseCustomFeeCoreCharge($order->getData('base_custom_fee_core_charge'));
        $extensionAttributes->setCustomFeeCoreCharge($order->getData('custom_fee_core_charge'));
        $extensionAttributes->setBaseCustomFeeCrateCharge($order->getData('base_custom_fee_crate_charge'));
        $extensionAttributes->setCustomFeeCrateCharge($order->getData('custom_fee_crate_charge'));
        $extensionAttributes->setBaseCustomFeeFreightCharge($order->getData('base_custom_fee_freight_charge'));
        $extensionAttributes->setCustomFeeFreightCharge($order->getData('custom_fee_freight_charge'));
        $extensionAttributes->setBaseCustomFeeDropshipCharge($order->getData('base_custom_fee_dropship_charge'));
        $extensionAttributes->setCustomFeeDropshipCharge($order->getData('custom_fee_dropship_charge'));
        $extensionAttributes->setBaseCustomFeeTruckFrtSetPrice($order->getData('base_custom_fee_truck_frt_set_price'));
        $extensionAttributes->setCustomFeeTruckFrtSetPrice($order->getData('custom_fee_truck_frt_set_price'));
        $extensionAttributes->setIsCanadianFreight($order->getData('is_canadian_freight'));
        $extensionAttributes->setIsTruckFrt0070($order->getData('is_truck_frt_0070'));
        $this->_populateCanadaTaxes($order);
    }
    private function _populateCanadaTaxes($order): void
    {
        $taxAttr = ['canada_taxes', 'base_canada_taxes'];
        foreach ($taxAttr as $attr){
            $json = $order->getData($attr);
            if (!$json) {
                return;
            }

            try {
                $items = $this->serializer->unserialize($json);
            } catch (\Throwable $e) {
                $items = is_array($json) ? $json : [];
            }

            if (!is_array($items)) {
                return;
            }

            $ext = $order->getExtensionAttributes() ?: $this->orderExtensionFactory->create(\Magento\Sales\Api\Data\OrderInterface::class);
            $taxItems = [];

            foreach ($items as $item) {
                $code = $item['code'] ?? null;
                $value = $item['value'] ?? null;

                /** @var CanadaTaxItemInterface $taxItem */
                $taxItem = $this->taxItemInterfaceFactory->create();
                $taxItem->setCode($code);
                $taxItem->setValue($value);

                $taxItems[] = $taxItem;
            }

            $ext->setData($attr,$taxItems);
            $order->setExtensionAttributes($ext);
        }
    }
}
