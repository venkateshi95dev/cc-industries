<?php

namespace Crimson\CorvetteCentralCustomFees\Model\Total;

use Crimson\CorvetteCentralCustomFees\Model\Config;
use Crimson\CorvetteCentralCustomFees\Service\CustomFeeCalculator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class IsTruckFreight0700 extends AbstractTotal
{
    public function __construct(
        protected CustomFeeCalculator $calculator,
        protected Config $config,
        protected PriceCurrencyInterface $priceCurrency
    )
    {
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if(!$this->config->isCustomChargeEnabled('TRUCK-FRT-PREPAID') || !$quote->getData('is_truck_frt_0070'))
            return null;
        return [
            'code' => 'TRUCK-FRT-PREPAID-0700',
            'title' => 'TRUCK-FRT-PREPAID-0700',
            'value' => $quote->getData('is_truck_frt_0070')
        ];
    }

    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    )
    {
        if(!$this->config->isCustomChargeEnabled('TRUCK-FRT-PREPAID') || $quote->getItems() == null)
            return $this;
        parent::collect($quote, $shippingAssignment, $total);
        $address = $shippingAssignment->getShipping()->getAddress();
        if($address->getAddressType()!='shipping' || ($address->getAddressType()=='shipping' && $address->getCountryId() != 'US'))
            return $this;
        $flag    = $this->calculator->hasTruckFreight0700($quote);
        $quote->setData('is_truck_frt_0070', $flag);
        return $this;
    }
}
