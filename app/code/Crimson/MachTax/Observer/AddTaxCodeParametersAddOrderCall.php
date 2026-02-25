<?php

namespace Crimson\MachTax\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

/**
 * Class AddTaxCodeParametersAddOrderCall
 * @package Crimson\MachTax\Observer
 */
class AddTaxCodeParametersAddOrderCall implements ObserverInterface
{

    CONST PARAMETER_TAX_CODE   = 'TaxCode';
    CONST ORDER_TAX_CODE       = 'tax_code';
    CONST PARAMETERS_DATA_CODE = 'ADD_ORDER_IN';
    CONST PARAMETERS_DATA_NODE = 'enc_name';
    CONST PARAMETERS_DATA_NODE_VALUE = 'enc_value';

    /**
     * @param Observer $observer
     *
     * @return DataObject|void
     */
    public function execute(Observer $observer)
    {
        /** @var DataObject $parameters */
        $parameters = $observer->getEvent()->getArguments();
        $order      = $observer->getEvent()->getOrder();

        $parametersHead  = $parameters->getData('arguments');

        if (is_array($parametersHead) && !empty($parametersHead)) {
            $parametersHead = $this->processOrderParametersValues($parametersHead,$order);
        }

        $parameters->setData('arguments',$parametersHead);

        return $parameters;

    }

    /**
     * @param array $parametersHead
     * @param       $order
     *
     * @return array
     */
    public function processOrderParametersValues(array $parametersHead, $order): array
    {
        foreach ($parametersHead as $headKey => $parameterHead) {

            if (!$this->isValidHeadNode($parameterHead)) {
                continue;
            }

            $mainNodes = $parameterHead->{self::PARAMETERS_DATA_NODE_VALUE};
            if (is_array($mainNodes) && !empty($mainNodes)) {

                foreach ($mainNodes as $nodeKey => $orderValueNode) {

                    if ($this->isValidOrderTaxCodeNode($orderValueNode)) {
                        $taxCodeValue = $this->getTaxCodeFromOrder($order);
                        $mainNodes[$nodeKey] = $this->_soapVar($taxCodeValue, self::PARAMETER_TAX_CODE);
                        $parameterHead->{self::PARAMETERS_DATA_NODE_VALUE} = $mainNodes;
                        $parametersHead[$headKey] = $parameterHead;
                        break 2;
                    }
                }
            }
        }

        return $parametersHead;
    }

    /**
     * @param $headNode
     *
     * @return bool
     */
    public function isValidHeadNode($headNode): bool
    {
       $result = false;
       if (property_exists($headNode, self::PARAMETERS_DATA_NODE)
           && $headNode->{self::PARAMETERS_DATA_NODE} == self::PARAMETERS_DATA_CODE
           && property_exists($headNode, self::PARAMETERS_DATA_NODE_VALUE) ) {

           $result = true;
       }

       return $result;
    }

    public function isValidOrderTaxCodeNode($orderValueNode): bool
    {
        $result = false;
        if (property_exists($orderValueNode, self::PARAMETERS_DATA_NODE)
            && $orderValueNode->{self::PARAMETERS_DATA_NODE} == self::PARAMETER_TAX_CODE) {

            $result = true;
        }

        return $result;
    }

    /**
     * @param      $value
     * @param null $key
     *
     * @return \SoapVar
     */
    protected function _soapVar($value, $key = null): \SoapVar
    {
        if (is_scalar($value)) {
            $encType = XSD_STRING;
        } else {
            $encType = SOAP_ENC_OBJECT;
        }

        if (is_null($key)) {
            return new \SoapVar($value, $encType, null, null, null, 'machsoftware');
        } else {
            return new \SoapVar($value, $encType, null, null, $key, 'machsoftware');
        }
    }

    /**
     * @param $order
     *
     * @return string
     */
    public function getTaxCodeFromOrder($order): string
    {
        $taxCode = 0;
        if ($order instanceof OrderInterface){
            $taxCode = $order->getExtensionAttributes()->getTaxCode();
        }elseif ($order instanceof Order) {
            $taxCode = $order->getData(self::ORDER_TAX_CODE);
        }

        return (string) $taxCode;
    }

}
