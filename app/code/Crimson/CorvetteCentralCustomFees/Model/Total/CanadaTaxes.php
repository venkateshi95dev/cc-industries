<?php

namespace Crimson\CorvetteCentralCustomFees\Model\Total;

use Crimson\CorvetteCentralCustomFees\Model\Config;
use Crimson\CorvetteCentralCustomFees\Service\CustomFeeCalculator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Framework\Serialize\SerializerInterface;

class CanadaTaxes extends AbstractTotal
{
    public function __construct(
        protected CustomFeeCalculator $calculator,
        protected Config $config,
        protected PriceCurrencyInterface $priceCurrency,
        protected SerializerInterface $serializer
    )
    {
        $this->setCode('canada_taxes');
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if(!$this->config->isCustomChargeEnabled('canada_taxes') || !$quote->getData('canada_taxes'))
            return null;

        $json = $quote->getData('canada_taxes');
        $amount = 0;
        $title = 'TAX (CANADA): ';
        if ($json) {
            $payload = $this->serializer->unserialize($json);
            foreach ($payload as $p) {
                $amount += (float)$p['value'];
                $title .= '['.$p['code'].':'.$this->priceCurrency->convertAndFormat((float)$p['value'],false).']';
            }
        }
        return [
            'code' => $this->getCode(),
            'title' => __($title),
            'value' => $amount
        ];
    }


    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    )
    {
        if(!$this->config->isCustomChargeEnabled('canada_taxes') || $quote->getItems() == null)
        {
            return $this;
        }
        parent::collect($quote, $shippingAssignment, $total);
        $address = $shippingAssignment->getShipping()->getAddress();
        if($address->getAddressType()!='shipping'){
            return $this;
        }

        $canadaTaxes = $this->calculator->calculateCanadaTaxes($quote, $address);
        $baseAmount = $canadaTaxes['amount']??0;
        if($baseAmount){
            $amount = $this->priceCurrency->convert($baseAmount, $quote->getStore(), $quote->getStoreCurrencyCode());
            $basePayload = $canadaTaxes['payload'];
            $payload = [];
            foreach ($basePayload as $p){
                $payload[] = [
                    'code' => $p['code'],
                    'value' => $this->priceCurrency->convert($p['value'], $quote->getStore(), $quote->getStoreCurrencyCode())
                ];
            }
            $total->setTotalAmount($this->getCode(), $amount);
            $total->setBaseTotalAmount($this->getCode(), $baseAmount);
            $total->setGrandTotal((float)$total->getGrandTotal() + $amount);
            $total->setBaseGrandTotal((float)$total->getBaseGrandTotal() + $baseAmount);
            $quote->setData($this->getCode(), $this->serializer->serialize($payload));
            $quote->setData('base_'.$this->getCode(), $this->serializer->serialize($basePayload));
        }
        else{
            $total->setTotalAmount($this->getCode(), 0);
            $total->setBaseTotalAmount($this->getCode(), 0);
            $quote->setData($this->getCode(), null);
            $quote->setData('base_'.$this->getCode(), null);
        }
        return $this;
    }
}
