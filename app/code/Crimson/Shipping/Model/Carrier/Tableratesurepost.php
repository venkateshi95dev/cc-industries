<?php

namespace Crimson\Shipping\Model\Carrier;

use Crimson\Shipping\Model\ResourceModel\Carrier\TableratesurepostFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Crimson\Shipping\Model\Config;
use Crimson\MachCatalog\Model\Service\GetMultiItemInventory;
use Crimson\MachCatalog\Model\Api\Result\MultiInventoryResult;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Item;
use Crimson\PoBoxRestriction\Service\IsShipMethodAllowedForPoBox;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Tableratesurepost
 * @package Crimson\Shipping\Model\Carrier
 */
class Tableratesurepost extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'tablerate_surepost';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var string
     */
    protected $_defaultConditionName = 'package_weight';

    /**
     * @var array
     */
    protected $_conditionNames = [];

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_resultMethodFactory;

    /**
     * @var TableratesurepostFactory
     */
    protected $_tableratesurepostFactory;

    /**
     * @var Config
     */
    protected $config;

    /** @var GetMultiItemInventory */
    protected $getMultiInventory;

    /**
     * @var IsShipMethodAllowedForPoBox
     */
    protected $isShipMethodAllowedForPoBox;


    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $resultMethodFactory,
        TableratesurepostFactory $tableratesurepostFactory,
        Config $config,
        GetMultiItemInventory $getMultiInventory,
        IsShipMethodAllowedForPoBox $isShipMethodAllowedForPoBox,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_resultMethodFactory = $resultMethodFactory;
        $this->_tableratesurepostFactory = $tableratesurepostFactory;
        $this->config = $config;
        $this->getMultiInventory = $getMultiInventory;
        $this->isShipMethodAllowedForPoBox = $isShipMethodAllowedForPoBox;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        foreach ($this->getCode('condition_name') as $k => $v) {
            $this->_conditionNames[] = $k;
        }
    }

    /**
     * @param RateRequest $request
     * @return false|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        // exclude Virtual products price from Package value if pre-configured
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                /** @var \Magento\Quote\Model\Quote\Address\Item $item */
                if ($item->getParentItem()) {
                    continue;
                }

                $truckship = $item->getProduct()->getAttributeText('truckship');
                $freeShipping = $item->getProduct()->getAttributeText('freeshipping');

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual()) {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual() || $freeShipping == 'Yes' || $truckship == 'Yes') {
                    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                }
            }
        }

        // Free shipping by qty
        $freeQty = 0;
        $freePackageValue = 0;

        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeShipping = is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0;
                            $freeQty += $item->getQty() * ($child->getQty() - $freeShipping);
                        }
                    }
                } elseif ($item->getFreeShipping() || $item->getAddress()->getFreeShipping()) {
                    $freeShipping = $item->getFreeShipping() ?
                        $item->getFreeShipping() : $item->getAddress()->getFreeShipping();
                    $freeShipping = is_numeric($freeShipping) ? $freeShipping : 0;
                    $freeQty += $item->getQty() - $freeShipping;
                    $freePackageValue += $item->getBaseRowTotal();
                }
            }
            $oldValue = $request->getPackageValue();
            $request->setPackageValue($oldValue - $freePackageValue);
        }

        if (!$request->getConditionName()) {
            $conditionName = $this->getConfigData('condition_name');
            $request->setConditionName($conditionName ? $conditionName : $this->_defaultConditionName);
        }

        // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();

        $request->setPackageWeight($request->getFreeMethodWeight());
        $request->setPackageQty($oldQty - $freeQty);

        /** @var Result $result */
        $result = $this->_rateResultFactory->create();
        $rate = $this->getRate($request);

        $request->setPackageWeight($oldWeight);
        $request->setPackageQty($oldQty);

        if (!empty($rate) && $rate['price'] >= 0) {
            if ($request->getPackageQty() == $freeQty) {
                $shippingPrice = 0;
            } else {
                $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
            }
            $method = $this->createShippingMethod($shippingPrice, $rate['cost']);
            $result->append($method);
        } elseif ($request->getPackageQty() == $freeQty) {

            /**
             * Promotion rule was applied for the whole cart.
             *  In this case all other shipping methods could be omitted
             * Table rate shipping method with 0$ price must be shown if grand total is more than minimal value.
             * Free package weight has been already taken into account.
             */
            $request->setPackageValue($freePackageValue);
            $request->setPackageQty($freeQty);
            $rate = $this->getRate($request);
            if (!empty($rate) && $rate['price'] >= 0) {
                $method = $this->createShippingMethod(0, 0);
                $result->append($method);
            }
        } else {
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Error $error */
            $error = $this->_rateErrorFactory->create(
                [
                    'data' => [
                        'carrier' => $this->_code,
                        'carrier_title' => $this->getConfigData('title'),
                        'error_message' => $this->getConfigData('specificerrmsg'),
                    ],
                ]
            );
            $result->append($error);
        }

        return $result;
    }

    /**
     * Get rate.
     *
     * @param RateRequest $request
     * @return array|bool
     */
    public function getRate(RateRequest $request)
    {
        return $this->_tableratesurepostFactory->create()->getRate($request);
    }

    public function getCode($type, $code = '')
    {
        $codes = [
            'condition_name' => [
                'package_weight' => __('Weight vs. Destination'),
                'package_value_with_discount' => __('Price vs. Destination'),
                'package_qty' => __('# of Items vs. Destination'),
            ],
            'condition_name_short' => [
                'package_weight' => __('Weight (and above)'),
                'package_value_with_discount' => __('Order Subtotal (and above)'),
                'package_qty' => __('# of Items (and above)'),
            ],
        ];

        if (!isset($codes[$type])) {
            throw new LocalizedException(
                __('The "%1" code type for Table Rate is incorrect. Verify the type and try again.', $type)
            );
        }

        if ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw new LocalizedException(
                __('The "%1: %2" code type for Table Rate is incorrect. Verify the type and try again.', $type, $code)
            );
        }

        return $codes[$type][$code];
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return ['bestway_surepost' => $this->getConfigData('name')];
    }

    /**
     * Get the method object based on the shipping price and cost
     *
     * @param float $shippingPrice
     * @param float $cost
     * @return Method
     */
    private function createShippingMethod($shippingPrice, $cost): Method
    {
        /** @var  Method $method */
        $method = $this->_resultMethodFactory->create();

        $method->setCarrier('tablerate');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('bestway_surepost');
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($shippingPrice);
        $method->setCost($cost);

        return $method;
    }

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * @param DataObject $request
     * @return $this|DataObject|boolean
     * @deprecated 100.2.3
     */
    public function proccessAdditionalValidation(DataObject $request)
    {
        return $this->processAdditionalValidation($request);
    }

    /**
     * @return string
     */
    protected function _getCode(): string
    {
        return $this->_code;
    }


    /**
     * Processing additional validation to check is carrier applicable.
     *
     * Surepost:
     *  < 6 lbs
     *  subtotal < $100
     *  exclude dropship
     *  exclude oversize
     *  exclude hazardous
     *  exclude backorder/partial
     *  exclude truck freight
     *
     * @param DataObject $request
     * @return $this|bool|DataObject|AbstractCarrier
     */
    public function processAdditionalValidation(DataObject $request)
    {
        //Checking PO BOX
        if (!empty($request[IsShipMethodAllowedForPoBox::SHIP_DEST_STREET_FIELD])) {
            $poBoxValidation = $this->isShipMethodAllowedForPoBox->is(
                (string) $request[IsShipMethodAllowedForPoBox::SHIP_DEST_STREET_FIELD],
                $this->_getCode()
            );

            if (!$poBoxValidation) {
                return false;
            }
        }

        //checking weight
        $weightCutoffValue = (float) $this->getConfigData('weight_value_cutoff');
        if (round($request->getPackageWeight(),2) > $weightCutoffValue) {
            return false;
        }

        //checking physical value
        $cutoffValue = (float) $this->getConfigData('physical_value_cutoff');
        if (round($request->getPackagePhysicalValue(),2) > $cutoffValue) {
            return false;
        }

        $checkMachStock = false;
        $multiInvResult = false;
        if ($this->config->getCanCallMach()) {
            $multiInvResult = $this->getMultiInventory->getMultiInvFilteringShipping($request->getAllItems());
            if ($multiInvResult instanceof MultiInventoryResult && $multiInvResult->getResponseStatus()) {
                $checkMachStock = true;
            }
        }

        foreach ($request->getAllItems() as $item) {

            if ($item->getProductType() != "simple") {
                continue;
            }

            $_product = $item->getProduct();

            if (!$_product || (int)$_product->getId() === 0 || empty($_product->getSku())) {
                continue;
            }

            /** @var \Magento\Quote\Model\Quote\Address\Item $item */
            //checking dropship
            if ($item->getProduct()->getData('ships_from_manufacturer') == 1) {
                return false;
            }

            //checking hazardous material
            $isHazardousMaterial = ($_product->getData('hazardous_material')
                ? $this->_castStringToBool($_product->getAttributeText('hazardous_material')) : false);
            if ($isHazardousMaterial) {
                return false;
            }

            //checking stock Mach or Magento
            if ($checkMachStock) {
                //checking stock from Mach, if partial availability or backorder then no express ship
                $qtyAvailFromMach = $multiInvResult->getQtyAvailable($_product->getSku());
                if ($qtyAvailFromMach === false || $qtyAvailFromMach == 0 || $this->config->isPartialAvailability($item, $qtyAvailFromMach)) {
                    return false;
                }
            } else {
                //this needs to be determined based on the info in Magento, NOT Mach
                $result = $this->config->isPartialBackorderMagentoStock($item);
                if ($result) {
                    return false;
                }
            }

            //checking truck freight
            $truckFreightShipping = $item->getProduct()->getData('truckship');
            if (!is_null($truckFreightShipping)
                && $item->getProduct()->getAttributeText('truckship') == 'Yes'
            ) {
                return false;
            }

            //checking oversize
            $oversizeShipping = $item->getProduct()->getData('shippingcharge');
            if (!is_null($oversizeShipping)
                && $item->getProduct()->getAttributeText('shippingcharge') != 'None'
            ) {
                return false;
            }
        }

        return $this;
    }

    /**
     * @param $value
     * @param string $true
     * @return bool
     */
    protected function _castStringToBool($value, $true = 'Yes'): bool
    {
        return ((string)$value) === $true;
    }
}
