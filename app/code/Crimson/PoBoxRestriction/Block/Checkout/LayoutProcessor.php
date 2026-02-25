<?php
/**
 * @namespace   Crimson
 * @module      PoBoxRestriction
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/22/2019
 */
namespace Crimson\PoBoxRestriction\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Crimson\PoBoxRestriction\Helper\Data;

/**
 * Class LayoutProcessor
 * @package Crimson\PoBoxRestriction\Block\Checkout
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * LayoutProcessor constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param Data $helper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        Data $helper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        $this->_helper = $helper;
    }

    /**
     * @param array $result
     * @return array
     */
    public function process($result): array
    {
        if (!$this->_helper->getModuleConfig()) {
            return $result;
        }

        if(isset($result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset'])) {

            $shippingFields = $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'];

            $shippingFields = $this->modifyStreetUiComponents($shippingFields);

            $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'] = $shippingFields;

        }

        if ($this->_helper->getEnabledForCustomerAccountAddressCreation()) {
            $result = $this->getBillingFormFields($result);
        }

        return $result;
    }

    /**
     * @param $result
     * @return mixed
     */
    public function getBillingFormFields($result)
    {
        if (isset($result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list'])) {

            $paymentForms = $result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list']['children'];

            foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {

                $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);

                if (!isset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form'])) {
                    continue;
                }

                $billingFields = $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'];

                $billingFields = $this->modifyStreetUiComponents($billingFields);

                $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'] = $billingFields;

            }
        }

        return $result;
    }

    /**
     * @param $addressResult
     * @return mixed
     */
    public function modifyStreetUiComponents($addressResult)
    {
        if (isset($addressResult['street'])) {
            unset($addressResult['street']['children'][1]['validation']);
            unset($addressResult['street']['children'][2]);
        }

        $lineCount = 0;
        while ($lineCount < 2) {
            if (isset($addressResult['street']['children'][$lineCount])) {
                if ($this->_helper->getEnablePOBoxRule()) {
                    $addressResult['street']['children'][$lineCount]['validation'] = $this->_helper->getValidationClassAsArrayForPoBox();
                }
            }
            $lineCount++;
        }
        return $addressResult;
    }

}
