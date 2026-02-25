<?php

namespace Crimson\CorvetteCentralCustomFees\Model\Total;

use Crimson\CorvetteCentralCustomFees\Model\Config;
use Crimson\CorvetteCentralCustomFees\Service\CustomFeeCalculator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config as TaxConfig;

class BaseCharge extends AbstractTotal
{
    public function __construct(
        protected CustomFeeCalculator $calculator,
        protected Config $config,
        protected PriceCurrencyInterface $priceCurrency,
        protected TaxConfig $taxConfig,
        protected Calculation $taxCalculation,
        protected StoreManagerInterface $storeManager,
        protected string $feeCode,
        protected string $feeTitle,
        protected string $feeAttrCode
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
        if(!$this->config->isCustomChargeEnabled($this->feeCode) || !$quote->getData($this->feeCode)>0)
            return null;
        return [
            'code' => $this->feeCode,
            'title' => $this->feeTitle,
            'value' => $quote->getData($this->feeCode)
        ];
    }

    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    )
    {
        if(!$this->config->isCustomChargeEnabled($this->feeCode) || $quote->getItems() == null)
        {
            return $this;
        }
        parent::collect($quote, $shippingAssignment, $total);
        $address = $shippingAssignment->getShipping()->getAddress();
        if ($address->getAddressType() !== 'shipping') {
            return $this;
        }
        $baseFee = $this->calculator->calculateCustomChargeForQuoteByAttributeCode($quote,$this->feeAttrCode);
        $fee = $this->priceCurrency->convert($baseFee, $quote->getStore(), $quote->getStoreCurrencyCode());


        $taxClassId = $this->config->getTaxClassForCustomFees();
        $taxAmount = $baseTaxAmount = 0;
        if($taxClassId){
            // Build tax rate request
            $request = $this->taxCalculation->getRateRequest(
                $address,
                $quote->getBillingAddress(),
                $quote->getCustomerTaxClassId(),
                $this->storeManager->getStore($quote->getStoreId())
            );
            $rate = (float)$this->taxCalculation->getRate($request->setProductClassId($taxClassId));

            $taxAmount = $fee * ($rate / 100);
            $baseTaxAmount = $baseFee * ($rate / 100);

            $total->setTaxAmount($total->getTaxAmount() + $taxAmount);
            $total->setBaseTaxAmount($total->getBaseTaxAmount() + $baseTaxAmount);
        }

        $total->setBaseTotalAmount($this->feeCode, $baseFee);
        $total->setTotalAmount($this->feeCode, $fee);

        $total->setGrandTotal((float)$total->getGrandTotal() + $fee + $taxAmount);
        $total->setBaseGrandTotal((float)$total->getBaseGrandTotal() + $baseFee + $baseTaxAmount);

        $quote->setData($this->feeCode, $fee);
        $quote->setData('base_'.$this->feeCode,$fee);
        return $this;
    }
}
