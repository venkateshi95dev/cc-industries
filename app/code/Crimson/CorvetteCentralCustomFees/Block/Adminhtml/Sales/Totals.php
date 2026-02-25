<?php
namespace Crimson\CorvetteCentralCustomFees\Block\Adminhtml\Sales;

class Totals extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        private \Crimson\CorvetteCentralCustomFees\Service\CustomFeeCalculator $customFeeCalculator,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context,$data);
    }
    public function initTotals(): void
    {
        $orderTotalsBlock = $this->getParentBlock();
        $this->_initCustomTotal($orderTotalsBlock,'custom_fee_core_charge','Core Charge');
        $this->_initCustomTotal($orderTotalsBlock,'custom_fee_crate_charge','Oversize Shipping / Crate Fee');
        $this->_initCustomTotal($orderTotalsBlock,'custom_fee_freight_charge','Freight Fee');
        $this->_initCustomTotal($orderTotalsBlock,'custom_fee_dropship_charge','Dropship Fee');
        $this->_initCustomTotal($orderTotalsBlock,'custom_fee_truck_frt_set_price','TRUCK-FRT-PREPAID - Set Price');
        $this->_initCustomTotal($orderTotalsBlock,'is_canadian_freight','FREIGHT-CANADA');
        $this->_initCustomTotal($orderTotalsBlock,'is_truck_frt_0070','TRUCK-FRT-PREPAID - See Item 0070');
        $this->_initCanadaTaxes($orderTotalsBlock);
    }
    private function _initCustomTotal($orderTotalsBlock, $customFeeCode, $customFeeLabel)
    {
        $order = $orderTotalsBlock->getOrder();
        $total = $order->getData($customFeeCode);
        //for canadian freight, only indicate that it is true or false, no actual amount in total
        $baseTotal = (in_array($customFeeCode,['is_canadian_freight','is_truck_frt_0070']))?$total:$order->getData('base_'.$customFeeCode);
        if($baseTotal > 0){
            $orderTotalsBlock->addTotal(new \Magento\Framework\DataObject([
                'code' => $customFeeCode,
                'label' => __($customFeeLabel),
                'value' => in_array($customFeeCode,['is_canadian_freight','is_truck_frt_0070'])?0:$total,
                'base_value' => in_array($customFeeCode,['is_canadian_freight','is_truck_frt_0070'])?0:$baseTotal,
            ]), 'shipping');
        }
    }

    private function _initCanadaTaxes($orderTotalsBlock)
    {
        $order = $orderTotalsBlock->getOrder();
        $total = $order->getData('canada_taxes');
        $canadaTaxes = $this->customFeeCalculator->processCanadaTaxFromJson($total);
        foreach ($canadaTaxes as $canadaTax){
            if($canadaTax->getValue() > 0){
                $orderTotalsBlock->addTotal(new \Magento\Framework\DataObject([
                    'code' => 'canada_tax_'.$canadaTax->getCode(),
                    'label' => __('Tax (Canada) - '.$canadaTax->getCode()),
                    'value' => $canadaTax->getValue(),
                    'base_value' => $canadaTax->getValue(),
                ]), 'shipping');
            }
        }
    }
}
