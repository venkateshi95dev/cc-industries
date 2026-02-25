<?php

namespace Crimson\CorvetteCentralCustomFees\Service;

use Crimson\CorvetteCentralCustomFees\Api\Data\CanadaTaxItemInterface;
use Crimson\CorvetteCentralCustomFees\Model\Config;
use Magento\Quote\Model\Quote;

class CustomFeeCalculator
{
    const CC_DROPSHIP_ATTR = 'cc_may_ship_from_manufacturer';
    const CC_TRUCK_FRT_TYPE_ATTR = 'cc_truck_freight_type';
    const CC_FREIGHT_CHARGE_ATTR = 'cc_freight_charge';
    const TRUCK_FRT_SET_PRICE = 'Truck Freight Prepaid - Set Price';
    const TRUCK_FRT_SEE_ITEM = 'Truck Freight Prepaid - See Item 0070';
    public function __construct(
        private Config $config,
        private \Magento\Framework\Serialize\SerializerInterface $serializer,
        private \Crimson\CorvetteCentralCustomFees\Api\Data\CanadaTaxItemInterfaceFactory $taxItemInterfaceFactory
    )
    {
    }
    public function calculateCustomChargeForQuoteByAttributeCode(Quote $quote, $attributeCode)
    {
        $fee = 0;
        if(!$quote->getItems())
            return $fee;
        switch ($attributeCode) {
            case self::CC_DROPSHIP_ATTR:
                if($this->_isDropshipChargeApplicable($quote)){
                    return $this->config->getDropshipChargeAmount();
                }
                break;
            case self::CC_TRUCK_FRT_TYPE_ATTR:
                if($quote->getShippingAddress()->getCountryId() !== 'US')
                    return 0;
                foreach ($quote->getItems() as $item)
                {
                    if($item->getProduct()->getAttributeText(self::CC_TRUCK_FRT_TYPE_ATTR) == self::TRUCK_FRT_SET_PRICE){
                        $charge = $item->getProduct()->getData(self::CC_FREIGHT_CHARGE_ATTR);
                        if($charge)
                            $fee+=$charge*$item->getQty();
                    }
                }
                break;
            case self::CC_FREIGHT_CHARGE_ATTR:
                $fee = $this->calculateFreightCharge($quote);
                break;
            default:
                foreach ($quote->getItems() as $item)
                {
                    $charge = $item->getProduct()->getData($attributeCode);
                    if($charge)
                        $fee+=$charge*$item->getQty();
                }
                break;
        }
        return $fee;
    }

    private function calculateFreightCharge($quote)
    {
        $fee = 0;
        if($quote->getShippingAddress()->getCountryId() !== 'US')
            return $fee;
        foreach ($quote->getItems() as $item)
        {
            if(!$item->getProduct()->getAttributeText(self::CC_TRUCK_FRT_TYPE_ATTR)){
                $charge = $item->getProduct()->getData(self::CC_FREIGHT_CHARGE_ATTR);
                if($charge)
                    $fee+=$charge*$item->getQty();
            }
        }
        return $fee;
    }
    public function hasTruckFreight0700($quote)
    {
        foreach ($quote->getItems() as $item){
            if($item->getProduct()->getAttributeText(self::CC_TRUCK_FRT_TYPE_ATTR) == self::TRUCK_FRT_SEE_ITEM)
                return true;
        }
        return false;
    }

    public function calculateCanadaTaxes($quote, $address)
    {
        $configs = $this->config->getTaxMappingConfig();
        $groupId = (int) $quote->getCustomerGroupId();
        $bundleGroupsConfig = [
            'dealer' => $this->config->getCustomerGroupsBundleConfig('dealer')??[],
            'retail' => $this->config->getCustomerGroupsBundleConfig('retail')??[],
            'all' => []
        ];
        $payload = [];
        foreach ($configs as $config){
            if($address->getRegionCode() == $config['province']){
                if(isset($bundleGroupsConfig[$config['apply_to']])){
                    if($bundleGroupsConfig[$config['apply_to']] == [] || in_array($groupId, $bundleGroupsConfig[$config['apply_to']])){
                        $payload[] = ['code' => $config['province'].' '.$config['jurisdiction_code'],
                            'value' => round(($quote->getSubtotal()*(float)$config['value'])/100,2)
                        ];
                    }
                }
            }
        }
        $amount = 0.0;
        foreach ($payload as $p) {
            $amount += $p['value'];
        }
        return[
            'amount' => $amount,
            'payload' => $payload
        ];
    }

    public function processCanadaTaxFromJson($json)
    {
        try {
            $items = $this->serializer->unserialize($json);
        } catch (\Throwable $e) {
            $items = is_array($json) ? $json : [];
        }

        if (!is_array($items)) {
            return;
        }
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
        return $taxItems;
    }

    private function _isDropshipChargeApplicable($quote)
    {
        if(!$quote->getBillingAddress()
            || ($quote->getBillingAddress()->getStreet() == $quote->getShippingAddress()->getStreet()))
        {
            return false;
        }
        $groupId = (int) $quote->getCustomerGroupId();
        if(!in_array($groupId, $this->config->getDropshipCustomerGroups()))
            return false;
        foreach ($quote->getItems() as $item){
            if($item->getProduct()->getData(self::CC_DROPSHIP_ATTR) == 1)
                return true;
        }
        return false;
    }
}
