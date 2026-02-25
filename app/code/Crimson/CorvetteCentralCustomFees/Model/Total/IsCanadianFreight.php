<?php

namespace Crimson\CorvetteCentralCustomFees\Model\Total;

use Crimson\CorvetteCentralCustomFees\Model\Config;
use Crimson\CorvetteCentralCustomFees\Service\CustomFeeCalculator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class IsCanadianFreight extends AbstractTotal
{
    public function __construct(
        protected Config $config
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
        if(!$this->config->isCustomChargeEnabled('FREIGHT-CANADA') || !$quote->getData('is_canadian_freight'))
            return null;
        return [
            'code' => 'FREIGHT-CANADA',
            'title' => 'FREIGHT-CANADA',
            'value' => $quote->getData('is_canadian_freight')
        ];
    }


    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    )
    {
        if(!$this->config->isCustomChargeEnabled('FREIGHT-CANADA') || $quote->getItems() == null)
        {
            return $this;
        }
        parent::collect($quote, $shippingAssignment, $total);
        $address = $shippingAssignment->getShipping()->getAddress();
        if($address->getAddressType()!='shipping'){
            return $this;
        }
        $flag    = ($address->getCountryId() === 'CA') ? 1 : 0;
        $quote->setData('is_canadian_freight', $flag);
        return $this;
    }
}
