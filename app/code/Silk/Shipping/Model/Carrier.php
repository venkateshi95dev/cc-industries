<?php
namespace Silk\Shipping\Model;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Item;

class Carrier extends \Magento\Fedex\Model\Carrier
{

    /**
     * Forming request for rate estimation depending to the purpose
     *
     * @param string $purpose
     * @return array
     */
    protected function _formRateRequest($purpose): array
    {
        $r = $this->_rawRequest;

        //residential field handling, if request comes from Amasty then it is not residential address
        $residential = (bool)$this->getConfigData('residence_delivery');
        if ($r->getData('am_store_pickup')) {
            $residential = false;
        }

        $ratesRequest = [
            'accountNumber' => [
                'value' => $r->getAccount()
            ],
            'ReturnTransitAndCommit' => true,
            'requestedShipment' => [
                'pickupType' => $this->getConfigData('pickup_type'),
                'packagingType' => $r->getPackaging(),
                'shipper' => [
                    'address' => ['postalCode' => $r->getOrigPostal(), 'countryCode' => $r->getOrigCountry()],
                ],
                'recipient' => [
                    'address' => [
                        'postalCode' => $r->getDestPostal(),
                        'countryCode' => $r->getDestCountry(),
                        'residential' => $residential,
                    ],
                ],
                'customsClearanceDetail' => [
                    'dutiesPayment' => [
                        'payor' => [
                            'responsibleParty' => [
                                'accountNumber' => [
                                    'value' => $r->getAccount()
                                ],
                                'address' => [
                                    'countryCode' => $r->getOrigCountry()
                                ]
                            ]
                        ],
                        'paymentType' => 'SENDER',
                    ],
                    'commodities' => [
                        [
                            'customsValue' => ['amount' => $r->getValue(), 'currency' => $this->getCurrencyCode()]
                        ]
                    ]
                ],
                'PackageCount' => '1',
                'rateRequestType' => ['ACCOUNT','LIST']
            ]
        ];

        foreach ($r->getPackages() as $packageNum => $package) {
            $ratesRequest['requestedShipment']['requestedPackageLineItems'][$packageNum]['subPackagingType'] =
                'PACKAGE';
            $ratesRequest['requestedShipment']['requestedPackageLineItems'][$packageNum]['groupPackageCount'] = 1;
            $ratesRequest['requestedShipment']['requestedPackageLineItems'][$packageNum]['weight']['value']
                = (double) $package['weight'];
            $ratesRequest['requestedShipment']['requestedPackageLineItems'][$packageNum]['weight']['units']
                = $this->getConfigData('unit_of_measure');

            $ratesRequest['RequestedShipment']['RequestedPackageLineItems'][$packageNum]['Dimensions']
                = [
                'Length' => 1,
                'Width' => 1,
                'Height' => 1,
                'Units' => 'IN'
            ];

            if (isset($package['price']) && $this->getConfigData('send_declared_value')) {
                $ratesRequest['requestedShipment']['requestedPackageLineItems'][$packageNum]['declaredValue']['amount'] = (double) $package['price'];
                $ratesRequest['requestedShipment']['requestedPackageLineItems'][$packageNum]['declaredValue']['currency'] = $this->getCurrencyCode();
            }
        }

        $ratesRequest['requestedShipment']['totalPackageCount'] = count($r->getPackages());
        if ($r->getDestCity()) {
            $ratesRequest['requestedShipment']['recipient']['address']['city'] = $r->getDestCity();
        }

        if ($purpose == self::RATE_REQUEST_SMARTPOST) {
            $ratesRequest['requestedShipment']['serviceType'] = self::RATE_REQUEST_SMARTPOST;
            $ratesRequest['requestedShipment']['smartPostInfoDetail'] = [
                'indicia' => (double)$r->getWeight() >= 1 ? 'PARCEL_SELECT' : 'PRESORTED_STANDARD',
                'hubId' => $this->getConfigData('smartpost_hubid'),
            ];
        }

        return $ratesRequest;
    }

    /**
     * Prepare and set request to this instance
     *
     * @param RateRequest $request
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function setRequest(RateRequest $request)
    {
        $this->_request = $request;

        $r = new \Magento\Framework\DataObject();

        if ($request->getLimitMethod()) {
            $r->setService($request->getLimitMethod());
        }

        if ($request->getFedexAccount()) {
            $account = $request->getFedexAccount();
        } else {
            $account = $this->getConfigData('account');
        }
        $r->setAccount($account);

        if ($request->getFedexPackaging()) {
            $packaging = $request->getFedexPackaging();
        } else {
            $packaging = $this->getConfigData('packaging');
        }
        $r->setPackaging($packaging);

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }
        $r->setOrigCountry($this->_countryFactory->create()->load($origCountry)->getData('iso2_code'));

        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStoreId()
                )
            );
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }
        $r->setDestCountry($this->_countryFactory->create()->load($destCountry)->getData('iso2_code'));

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        }

        if ($request->getDestCity()) {
            $r->setDestCity($request->getDestCity());
        }

        if ($request->getFreeMethodWeight() != $request->getPackageWeight()) {
            $r->setFreeMethodWeight($request->getFreeMethodWeight());
        }

        $r->setWeight($request->getPackageWeight());
        $r->setValue($request->getPackagePhysicalValue());
        $r->setValueWithDiscount($request->getPackageValueWithDiscount());

        $r->setPackages($this->createPackages((float) $request->getPackageWeight(), (array) $request->getPackages()));

        $r->setMeterNumber($this->getConfigData('meter_number'));
        $r->setKey($this->getConfigData('key'));
        $r->setPassword($this->getConfigData('password'));

        $r->setIsReturn($request->getIsReturn());

        $r->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());

        //check if request is coming from Amasty, this is for residential field
        if ($request->getData('am_store_pickup')) {
            $r->setData('am_store_pickup', true);
        }

        $this->setRawRequest($r);

        return $this;
    }

    public function getTotalNumOfBoxes($weight)
    {
        //reset num box first before retrieve again
        $this->_numBoxes = 1;
        $country = "";
        if ($this->_request->getDestCountryId()) {
            $country = $this->_request->getDestCountryId();
        }
        if($country == 'US' || $country == 'CA'){
            $maxPackageWeight = $this->getConfigData('max_package_weight');
        } else {
            $maxPackageWeight = 150;
        }

        if ($weight > $maxPackageWeight && $maxPackageWeight != 0) {
            $this->_numBoxes = ceil($weight/$maxPackageWeight);
            $weight = $weight/$this->_numBoxes;
        }

        return $weight;
    }

    public function proccessAdditionalValidation(DataObject $request)
    {
        //Skip by item validation if there is no items in request
        if (!count($this->getAllItems($request))) {
            return $this;
        }

        if($request->getDestCountryId() == 'US' || $request->getDestCountryId() == 'CA'){
            $maxAllowedWeight = (float) $this->getConfigData('max_package_weight');
        }
        else{
            $maxAllowedWeight = 150;
        }
        $errorMsg = '';
        $configErrorMsg = $this->getConfigData('specificerrmsg');
        $defaultErrorMsg = __('The shipping module is not available.');
        $showMethod = $this->getConfigData('showmethod');

        /** @var $item Item */
        foreach ($this->getAllItems($request) as $item) {
            $product = $item->getProduct();
            if ($product && $product->getId()) {
                $weight = $product->getWeight();
                $stockItemData = $this->stockRegistry->getStockItem(
                    $product->getId(),
                    $item->getStore()->getWebsiteId()
                );
                $doValidation = true;

                if ($stockItemData->getIsQtyDecimal() && $stockItemData->getIsDecimalDivided()) {
                    if ($stockItemData->getEnableQtyIncrements() && $stockItemData->getQtyIncrements()
                    ) {
                        $weight = $weight * $stockItemData->getQtyIncrements();
                    } else {
                        $doValidation = false;
                    }
                } elseif ($stockItemData->getIsQtyDecimal() && !$stockItemData->getIsDecimalDivided()) {
                    $weight = $weight * $item->getQty();
                }

                if ($doValidation && $weight > $maxAllowedWeight) {
                    $errorMsg = $configErrorMsg ? $configErrorMsg : $defaultErrorMsg;
                    break;
                }
            }
        }

        if (!$errorMsg && !$request->getDestPostcode() && $this->isZipCodeRequired($request->getDestCountryId())) {
            $errorMsg = __('This shipping method is not available. Please specify the zip code.');
        }

        if ($errorMsg && $showMethod) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($errorMsg);

            return $error;
        } elseif ($errorMsg) {
            return false;
        }

        return $this;
    }

    /**
     * @param RateRequest $request
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAllItems(RateRequest $request)
    {
        $items = [];
        $allItems = $request->getAllItems();

        if (!$allItems) {
            return $items;
        }

        /* @var $item Item */
        foreach ($allItems as $item) {
            if ($item->getProductType() == Type::TYPE_BUNDLE) {
                // Don't process bundle products
                continue;
            } elseif ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                // Don't process children here - we will process (or already have processed) them below
                continue;
            }

            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
                    if (!$child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                        $items[] = $child;
                    }
                }
            } else {
                // Ship together - count compound item as one solid
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Creates packages for rate request.
     *
     * @param float $totalWeight
     * @param array $packages
     * @return array
     */
    private function createPackages(float $totalWeight, array $packages): array
    {
        if (empty($packages)) {
            $dividedWeight = $this->getTotalNumOfBoxes($totalWeight);
            for ($i=0; $i < $this->_numBoxes; $i++) {
                $packages[$i]['weight'] = $dividedWeight;
            }
        }
        $this->_numBoxes = count($packages);

        return $packages;
    }
}
