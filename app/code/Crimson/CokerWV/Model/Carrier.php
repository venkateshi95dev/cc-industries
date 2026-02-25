<?php

namespace Crimson\CokerWV\Model;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Framework\Xml\Security;
use Magento\Catalog\Model\ProductFactory;
use Crimson\CokerWV\Model\Config as CokerWVConfig;
use Psr\Log\LoggerInterface;

class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    const CODE = 'lofproductshipping';
    protected $checkoutSession;
    protected $_code = self::CODE;
    protected $_request;
    protected $productFactory;

    protected $_result;
    protected $_baseCurrencyRate;
    protected $_xmlAccessRequest;
    protected $_localeFormat;
    protected $_logger;
    protected $configHelper;
    protected $_objectManager;
    protected $_errors = [];
    protected $_isFixed = true;

    public function __construct(
        ScopeConfigInterface                                 $scopeConfig,
        ErrorFactory                                         $rateErrorFactory,
        LoggerInterface                                      $logger, Security $xmlSecurity,
        ElementFactory                                       $xmlElFactory,
        ResultFactory                                        $rateFactory,
        MethodFactory                                        $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory       $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        StatusFactory                                        $trackStatusFactory,
        RegionFactory                                        $regionFactory,
        CountryFactory                                       $countryFactory,
        CurrencyFactory                                      $currencyFactory,
        Data                                                 $directoryData,
        StockRegistryInterface                               $stockRegistry,
        protected CokerWVConfig                              $cokerWVConfig,
        array $data = []
    )
    {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $xmlSecurity, $xmlElFactory, $rateFactory, $rateMethodFactory, $trackFactory, $trackErrorFactory, $trackStatusFactory, $regionFactory, $countryFactory, $currencyFactory, $directoryData, $stockRegistry, $data);
    }

    protected function _doShipmentRequest(DataObject $request)
    {

    }

    public function getAllowedMethods()
    {

    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->cokerWVConfig->getIsActive()) {
            return false;
        }

        $result = $this->_rateFactory->create();
        $partner = 0;
        $handling = 0;
        $countrycode = $request->getDestCountryId();
        $postcode = $request->getDestPostcode();
        $postcode = str_replace('-', '', $postcode);
        $shippingdetail = [];
        $shippostaldetail = [
            'countrycode' => $countrycode,
            'postalcode' => $postcode
        ];

        foreach($request->getAllItems() as $item) {
            $proid   = $item->getProductId();
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }

            $child_weight = 0;
            $weight = 0;
            if ($item->getHasChildren()) {
                $_product = $this->loadProduct($item->getProductId());
                if($_product->getTypeId() == "bundle") {
                    foreach ($item->getChildren() as $child) {
                            $productWeight = $this->loadProduct($child->getProductId())->getWeight();
                            $child_weight += $productWeight*$child->getQty();
                    }
                $weight = $child_weight * $item->getQty();
                } else if($_product->getTypeId() == "configurable") {
                    foreach ($item->getChildren() as $child) {
                        $productWeight = $this->loadProduct($child->getProductId())->getWeight();
                        $weight = $productWeight * $item->getQty();
                    }
                }
            }else {
                $productWeight = $this->loadProduct($proid)->getWeight();
                $weight=$productWeight*$item->getQty();
            }

            if(count($shippingdetail) == 0) {
                $shippingdetail[] = [
                    'seller_id'    => $partner,
                    'items_weight' => $weight,
                    'product_name' => $item->getName(),
                    'item_id'      => $item->getId()
                ];
            } else {
                $shipinfoflag = true;
                $index = 0;
                foreach($shippingdetail as $itemship) {
                    $itemship['items_weight'] = $itemship['items_weight'] + $weight;
                    $itemship['product_name'] = $itemship['product_name'] . "," . $item->getName();
                    $itemship['item_id']      = $itemship['item_id'] . "," . $item->getId();
                    $shippingdetail[$index]   = $itemship;
                    $shipinfoflag = false;
                    $index++;
                }
                if($shipinfoflag == true) {
                    $shippingdetail[] = [
                        'items_weight' => $weight,
                        'product_name' => $item->getName(),
                        'item_id' => $item->getId()
                    ];
                }
            }
        }

        $shippingpricedetail = $this->getShippingPricedetail($shippingdetail,$shippostaldetail);
        if($shippingpricedetail['errormsg'] !== "") {
            // Display error message if there
            $this->_errors[$this->_code]=$shippingpricedetail['errormsg'];
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->cokerWVConfig->getshippingTitle());
            $error->setErrorMessage($shippingpricedetail['errormsg']);
            return $error;
        }

        /*store shipping in session*/
        $shippingAll = $this->checkoutSession->getShippingInfo();
        $shippingAll[$this->_code] = $shippingpricedetail['shippinginfo'];
        $this->checkoutSession->setShippingInfo($shippingAll);

        /*store shipping in session*/
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->cokerWVConfig->getshippingTitle());

        /* Use method name */
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->cokerWVConfig->getshippingName());
        $method->setCost($shippingpricedetail['handlingfee']);
        $method->setPrice($shippingpricedetail['handlingfee']);
        $result->append($method);

        return $result;
    }
    public function getShippingPricedetail($shippingdetail,$shippostaldetail)
    {
        $shippinginfo = [];
        $handling = 0;
        $shippingbasedon = $this->cokerWVConfig->getShippingBasedOn();
        foreach($shippingdetail as $shipdetail) {
            $price = 0;
            $itemsarray = explode(',',$shipdetail['item_id']);
            $allItems = $this->checkoutSession->getQuote()->getAllItems();
            foreach ($allItems as $item) {
                $bundlePrice = 0;

                if(in_array($item->getId(),$itemsarray)) {
                    if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                        continue;
                    }

                    if ($item->getHasChildren()) {
                        $_product = $this->loadProduct($item->getProductId());
                        if($_product->getTypeId() == "bundle") {
                            $mpshippingcharge = $_product->getLofShippingCharge();
                            if($shippingbasedon == 0) {
                                if(floatval($mpshippingcharge) == 0){
                                    $price = $price+(floatval($this->cokerWVConfig->getDefaultShippingPrice()) * floatval($item->getQty()));
                                }else{
                                    $price = $price+($mpshippingcharge * floatval($item->getQty()));
                                }
                                continue;
                            }else{
                                foreach ($item->getChildren() as $child) {
                                    $mpshippingcharge = $this->loadProduct($child->getProductId())->getLofShippingCharge();
                                    if(floatval($mpshippingcharge) == 0){
                                        $bundlePrice = $bundlePrice+(floatval($this->cokerWVConfig->getDefaultShippingPrice()) * floatval($child->getQty()));
                                    }else{
                                        $bundlePrice = $bundlePrice+($mpshippingcharge * floatval($child->getQty()));
                                    }
                                }
                                $bundlePrice = $bundlePrice * floatval($item->getQty());
                                $price = $price + $bundlePrice;
                            }
                        }else if($_product->getTypeId() == "configurable") {
                            if($shippingbasedon == 0) {
                                $mpshippingcharge = $_product->getLofShippingCharge();
                                if(floatval($mpshippingcharge) == 0) {
                                    $price = $price + (floatval($this->cokerWVConfig->getDefaultShippingPrice()) * floatval($item->getQty()));
                                }else{
                                    $price = $price + ($mpshippingcharge * floatval($item->getQty()));
                                }
                                continue;
                            }else{
                                foreach ($item->getChildren() as $child) {
                                    $mpshippingcharge = $this->loadProduct($child->getProductId())->getLofShippingCharge();
                                    if(floatval($mpshippingcharge) == 0) {
                                        $price = $price + (floatval($this->cokerWVConfig->getDefaultShippingPrice()) * floatval($item->getQty()));
                                    }else{
                                        $price = $price + ($mpshippingcharge * floatval($item->getQty()));
                                    }
                                    continue;
                                }
                            }
                        }
                    }else{

                        $mpshippingcharge = $this->loadProduct($item->getProductId())->getLofShippingCharge();
                        if(floatval($mpshippingcharge) == 0) {
                            $price = $price + (floatval($this->cokerWVConfig->getDefaultShippingPrice()) * floatval($item->getQty()));
                        }else{
                            $price = $price + ($mpshippingcharge * floatval($item->getQty()));
                        }
                    }
                }

            }
            $handling = $handling + $price;
            $submethod = [
                [
                    'method' => $this->cokerWVConfig->getshippingTitle(),
                    'cost'   => $price,
                    'error'  => 0
                ]
            ];
            $shippinginfo[] = [
                'methodcode'       => $this->_code,
                'shipping_ammount' => $price,
                'product_name'     => $shipdetail['product_name'],
                'submethod'        => $submethod,
                'item_ids'         => $shipdetail['item_id']
            ];

        }

        return [
            'handlingfee'  => $handling,
            'shippinginfo' => $shippinginfo,
            'errormsg'     => ""
        ];
    }

    private function loadProduct($id)
    {
        return $this->productFactory->create()->load($id);
    }

    public function proccessAdditionalValidation(DataObject $request): true
    {
        return true;
    }
}
