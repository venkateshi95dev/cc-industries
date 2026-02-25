<?php

namespace Crimson\MachShipping\Model\Api;

use Crimson\MachBase\Model\Api\AbstractApi;
use Crimson\MachBase\Model\Api\ApiContext;
use Crimson\MachBase\Model\Api\Client;
use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachCustomer\Model\Service\GetMachCustomerNumber;
use Crimson\MachShipping\Model\Api\Request\Freight;
use Crimson\MachShipping\Model\Api\Request\FreightFactory;
use Crimson\MachShipping\Model\Api\Result\Tax;
use Crimson\MachShipping\Model\Api\Result\TaxFactory;
use Crimson\MachBase\Model\MachConfig;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Shipping
 * @package Crimson\MachShipping\Model\Api
 */
class Shipping extends AbstractApi
{

    CONST COMMERCIAL_FLAG  = 'C';
    CONST RESIDENTIAL_FLAG = 'R';

    /**
     * @var MachConfig
     */
    protected $machConfig;

    public function __construct(
        ApiContext $apiContext,
        protected HealthCheck $healthCheck,
        protected FreightFactory $freightFactory,
        protected TaxFactory $taxResultFactory,
        protected GetMachCustomerNumber $getMachCustomerNumber,
        MachConfig $machConfig,
        protected Client $client,
        Subscriber $subscriberResource
    ) {
        $this->machConfig = $machConfig;
        parent::__construct($apiContext,$machConfig, $subscriberResource);
    }

    /**
     * @return bool
     */
    public function isUp(): bool
    {
        return $this->healthCheck->isUp();
    }

    /**
     * @param null $websiteId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isMachEnabled($websiteId = null): bool
    {
        return $this->machConfig->isEnabled($websiteId);
    }

    /**
     * @param RateRequest $request
     * @return array
     */
    public function getShippingRates(RateRequest $request): array
    {
        try {
            $request = $this->buildFreightRequest($request);

            //single call get freights
            return $this->getFreight($request);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function buildFreightRequest($input): Request\Freight
    {
        $request = $this->freightFactory->create();

        if ($input instanceof RateRequest) {
            $request->setData($input->getData());
        } else {
            /**
             * @var Address $input
             */
            $request->setAllItems($input->getAllItems());
            $request->setDestCountryId($input->getCountryId());
            $request->setDestRegionId($input->getRegionId());
            $request->setDestRegionCode($input->getRegionCode());
            /**
             * need to call getStreet with -1
             * to get data in string instead of array
             */
            $request->setDestStreet($input->getStreetFull());
            $request->setDestCity($input->getCity());
            $request->setDestPostcode($input->getPostcode());
            $request->setPackageValue($input->getBaseSubtotal());
            $packageValueWithDiscount = $input->getBaseSubtotalWithDiscount();
            $request->setPackageValueWithDiscount($packageValueWithDiscount);
            $request->setPackageWeight($input->getWeight());
            $request->setPackageQty($input->getItemQty());

            $request->setShippingAmount($input->getShippingAmount());

            /**
             * Need for shipping methods that use insurance based on price of physical products
             */
            $packagePhysicalValue = $input->getBaseSubtotal() - $input->getBaseVirtualAmount();
            $request->setPackagePhysicalValue($packagePhysicalValue);

            $request->setFreeMethodWeight($input->getFreeMethodWeight());

            /**
             * Store and website identifiers need specify from quote
             */
            $request->setStoreId($input->getQuote()->getStore()->getId());
            $request->setWebsiteId($input->getQuote()->getStore()->getWebsiteId());
            $request->setFreeShipping($input->getFreeShipping());

            if ($input instanceof Address) {
                $request->setShippingAmount($input->getShippingAmount());

                if(!empty($input['county'])) {
                    $request->setCounty($input['county']);
                }

                //Adding the Handling Amount
                $request->setAdditionalHandlingAmount($input->getBaseAdditionalHandlingAmount());
            }

            /**
             * Currencies need to convert in free shipping
             */
            $request->setBaseCurrency($input->getQuote()->getStore()->getBaseCurrency());
            $request->setPackageCurrency($input->getQuote()->getStore()->getCurrentCurrency());
            $request->setLimitCarrier($input->getLimitCarrier());

            $request->setBaseSubtotalInclTax($input->getBaseSubtotalInclTax() + $input->getBaseExtraTaxAmount());
        }

        return $request;
    }

    public function getFreight(Freight $request): array
    {
        $action = self::CALL_GET_FREIGHT;
        $actionCode = self::GET_FREIGHT_MULTIPLE_ACTION_CODE_SHIPRATE;
        $this->debugLog('Beginning ' . $action . ' Call'. ' and action code: '.$actionCode);
        $status = $cacheUsed = false;

        if (!$request->getActionCode()) {
            $request->setActionCode($actionCode);
        }

        try {
            $arguments = array(
                $this->_soapVar($this->getSecurityCode(), 'SecurityCode'),
                $this->_soapVar($actionCode, 'Action'),
            );

            //starting with info for MACH
            $result = $freightInfoIn = [];
            if (empty($request->getMachMethods())) {
                return [];
            }

            //Shipping methods processing
            foreach ($request->getMachMethods() as $magentoShippingMethod => $methodLabel) {
                $ShipMethodList  = $this->_soapVar($magentoShippingMethod, 'ShipMethodList');
                $freightInfoIn[] = $this->_soapVar([$ShipMethodList], 'SHIPMETHODMULTI');
            }


            //build item array
            $itemCount = 0;
            foreach ($request->getAllItems() as $item) {
                /** @var Item $item */
                if ($item->getProductType() === 'simple') {
                    $freightItemData = $this->_buildFreightItem($item);
                    $freightInfoIn[] = $this->_soapVar($freightItemData, 'ITEMS');

                    $itemCount++;
                }
            }

            //if we have no items, all are free shipping.
            if (!$itemCount) {
                return [];
            }

            //Customer info
            $customerEmail  = $request->getRecipientEmail();
            $customerNumber = $this->getMachCustomerNumber->get($customerEmail);

            $resComFlag = $this->getResidentialCommercialValue($request->getResBusFlag() ?? '');
            $freightInfoIn = array_merge(
                $freightInfoIn,
                [
                    $this->_soapVar(null, 'ShipMethod'),
                    $this->_soapVar(substr($request->getDestPostcode() ?? '', 0, 5), 'ShipToZipCode'),
                    $this->_soapVar(null, 'CatalogType'),
                    $this->_soapVar(null, 'DeclineBioAir'),
                    $this->_soapVar(null, 'NDABio'),
                    $this->_soapVar($request->getDestRegionCode(), 'ShipToState'),
                    $this->_soapVar(ucwords(strtolower($request->getDestCity() ?? '')), 'ShipToCity'),
                    $this->_soapVar(null, 'ShipToCounty'),
                    $this->_soapVar($customerNumber, 'CustomerNumber'),
                    $this->_soapVar($customerEmail, 'CustomerEmail'),
                    $this->_soapVar(null, 'ShippingCharges'),
                    $this->_soapVar(null, 'AddCharges'),
                    $this->_soapVar($resComFlag, 'ResComFlag'),
                    $this->_soapVar(null, 'OtherCharges'),
                    $this->_soapVar(null, 'CreditCharges'),
                    $this->_soapVar(ucwords(strtolower($request->getDestStreet() ?? '')), 'ShipToAddress'),

                    $this->_soapVar(null, 'Other1'),
                    $this->_soapVar(null, 'Other2'),
                    $this->_soapVar(null, 'Other3'),
                    $this->_soapVar(null, 'Other4'),
                    $this->_soapVar(null, 'Other5'),
                ]);

            $arguments[] = $this->_soapVar($freightInfoIn, 'FREIGHT_INFO_IN');

            /** Implement Freight Caching -  Start Get Cache */
            /** @var array $sessionData */
            $sessionData = $this->_getCachedResult($arguments, $action);
            if ($sessionData !== false) {
                $result = $sessionData;
                $status = $cacheUsed = true;
                return $result;
            }
            /** End Get Cache */
            $this->apiContext->setClient($this->client);
            $response           = $this->makeRequest($action, $arguments, SOAP_ENC_OBJECT);
            $successResultCheck = $this->_isSuccess($action, null, $response);

            if ($successResultCheck !== true) {
                $errorNumber  = $successResultCheck['error_number'];
                $errorMessage = $successResultCheck['error_message'];
                $message      = 'Error occurred attempting to retrieve %3$s info from MACH ERP.<br/>';
                $message .= 'Error Number: %1%s.';

                if ($errorMessage === false) {
                    $message .= '%2$s';
                    $errorMessage = '';
                } else {
                    $message .= ' Error Message: %2$s.';
                }

                $message = sprintf($message, $errorNumber, $errorMessage, $actionCode, 'shipping rate');
                $this->debugLog($message);

                throw new \Exception($message);
            } else {
                $status      = true;
                $freightData = $this->_adjustFreightResponseData($response->FREIGHT_INFO_OUT->RATESHOPOUT);

                foreach ($freightData as $freight) {
                    $result[$freight->RSSHVkey] = [
                        'price'           => $freight->RSTotAddAmt,
                        'fob_ship_amount' => $freight->RSFOBShipAmount,
                        'days_in_transit' => $freight->RSDaysInTransit,
                        'res_com_flag'    => $resComFlag,
                    ];
                }

                /** Set Freight Cache */
                $this->_setCachedResult($arguments, $result, $action);

                $this->debugLog($result);
            }
        } catch (\Exception $e) {
            $this->debugLog($e->getMessage());
            throw $e;
        } finally {
            $message = array(
                'message'    => sprintf('Finished %s.  Result: %s', $action, ($status ? 'PASS' : 'FAIL')),
                'cache_used' => $this->_castBoolToString($cacheUsed, 'No', 'Yes'),
            );
            $this->debugLog($message);
            $this->debugLog('-------------------------------------------');
        }

        return $result;
    }

    private function _adjustFreightResponseData($freightData): array
    {
        $result = [];
        if (is_array($freightData)) {
            $result = $freightData;
        } else {
            $result[] = $freightData;
        }

        return $result;
    }

    //MACH Tax call
    public function getTax(Freight $request): Tax
    {
        $action = self::CALL_GET_FREIGHT;
        $actionCode = self::GET_FREIGHT_ACTION_CODE_TAX;
        $this->debugLog('Beginning ' . $action . ' Call'. ' and action code: '.$actionCode);
        $status = $cacheUsed = false;

        if (!$request->getActionCode()) {
            $request->setActionCode($actionCode);
        }

        try {
            $arguments = array(
                $this->_soapVar($this->getSecurityCode(), 'SecurityCode'),
                $this->_soapVar($actionCode, 'Action'),
            );

            //starting with info for MACH
            $result = $this->taxResultFactory->create();
            $freightInfoIn = [];
            $resComFlag = $this->getResidentialCommercialValue($request->getResBusFlag() ?? '');

            //build item array
            $itemCount = 0;
            foreach ($request->getAllItems() as $item) {
                /** @var Item $item */
                if ($item->getProductType() === 'simple') {
                    $freightItemData = $this->_buildFreightItem($item);
                    $freightInfoIn[] = $this->_soapVar($freightItemData, 'ITEMS');

                    $itemCount++;
                }
            }

            //if we have no items, all are free shipping.
            if (!$itemCount) {
                $result->setData(
                    [
                        'price'           => 0,
                        'fob_ship_amount' => 0,
                        'tax_code'        => 0,
                        'res_com_flag'    => $resComFlag,
                        'tax_amount'      => 0,
                        'allow_free'      => true,
                    ]
                );

                return $result;
            }

            //Customer info
            $customerEmail  = $request->getRecipientEmail();
            $customerNumber = $this->getMachCustomerNumber->get($customerEmail);

            //address related data
            $shippingCharges = $request->getShippingAmount() ?? null;
            $shippingCounty = $request->getCounty() ?? null;
            $additionalHandlingAmount = $request->getAdditionalHandlingAmount() ?? null;
            $freightInfoIn = array_merge(
                $freightInfoIn,
                [
                    $this->_soapVar(null, 'ShipMethod'),
                    $this->_soapVar(substr($request->getDestPostcode() ?? '', 0, 5), 'ShipToZipCode'),
                    $this->_soapVar(null, 'CatalogType'),
                    $this->_soapVar(null, 'DeclineBioAir'),
                    $this->_soapVar(null, 'NDABio'),
                    $this->_soapVar($request->getDestRegionCode(), 'ShipToState'),
                    $this->_soapVar(ucwords(strtolower($request->getDestCity() ?? '')), 'ShipToCity'),
                    $this->_soapVar(ucwords(strtolower($shippingCounty ?? '')), 'ShipToCounty'),
                    $this->_soapVar($customerNumber, 'CustomerNumber'),
                    $this->_soapVar($customerEmail, 'CustomerEmail'),
                    $this->_soapVar($shippingCharges, 'ShippingCharges'),
                    $this->_soapVar($additionalHandlingAmount, 'AddCharges'),
                    $this->_soapVar($resComFlag, 'ResComFlag'),
                    $this->_soapVar(null, 'OtherCharges'),
                    $this->_soapVar(null, 'CreditCharges'),
                    $this->_soapVar(ucwords(strtolower($request->getDestStreet() ?? '')), 'ShipToAddress'),

                    $this->_soapVar(null, 'Other1'),
                    $this->_soapVar(null, 'Other2'),
                    $this->_soapVar(null, 'Other3'),
                    $this->_soapVar(null, 'Other4'),
                    $this->_soapVar(null, 'Other5'),
                ]);

            $arguments[] = $this->_soapVar($freightInfoIn, 'FREIGHT_INFO_IN');

            /** Implement Freight Caching -  Start Get Cache */
            /** @var array $sessionData */
            $sessionData = $this->_getCachedResult($arguments, $action);
            if ($sessionData !== false) {
                $result->setData($sessionData);
                $status = $cacheUsed = true;
                return $result;
            }
            /** End Get Cache */
            $this->apiContext->setClient($this->client);
            $response           = $this->makeRequest($action, $arguments, SOAP_ENC_OBJECT);
            $successResultCheck = $this->_isSuccess($action, null, $response, true);

            if ($successResultCheck !== true) {
                $errorNumber  = $successResultCheck['error_number'];
                $errorMessage = $successResultCheck['error_message'];
                $message      = 'Error occurred attempting to retrieve %3$s info from MACH ERP.<br/>';
                $message .= 'Error Number: %1%s.';

                if ($errorMessage === false) {
                    $message .= '%2$s';
                    $errorMessage = '';
                } else {
                    $message .= ' Error Message: %2$s.';
                }

                $message = sprintf($message, $errorNumber, $errorMessage, $actionCode, 'tax');
                $this->debugLog($message);

                throw new \Exception($message);
            } else {
                $status      = true;
                $freightData = $response->FREIGHT_INFO_OUT;

                $result->setData(
                    [
                        'tax_code'        => $freightData->TaxCode,
                        'tax_amount'      => $freightData->TaxAmount,
                        'res_com_flag'    => $resComFlag
                    ]
                );

                /** Set Freight Cache */
                $this->_setCachedResult($arguments, $result->getData(), $action);

                $this->debugLog($result);
            }
        } catch (\Exception $e) {
            $this->debugLog($e->getMessage());
            throw $e;
        } finally {
            $message = array(
                'message'    => sprintf('Finished %s.  Result: %s', $action, ($status ? 'PASS' : 'FAIL')),
                'cache_used' => $this->_castBoolToString($cacheUsed, 'No', 'Yes'),
            );
            $this->debugLog($message);
            $this->debugLog('-------------------------------------------');
        }

        return $result;
    }

    protected function _buildFreightItem(Item $item): array
    {
        $qty   = $item->getQty();
        if ($item->getParentItem()) {
            $qty   = $item->getParentItem()->getQty();
        }

        //We now get the price with discount amount applied to send a real number to Mach in order
        //to calculate the Tax correctly
        $price = $this->_calculatePrice($item);

        return [
            $this->_soapVar($item->getSku(), 'Items'),
            $this->_soapVar($qty, 'Qtys'),
            $this->_soapVar($price, 'Prices'),

            $this->_soapVar(null, 'OpenIn1'),
            $this->_soapVar(null, 'OpenIn2'),
            $this->_soapVar(null, 'OpenIn3'),
            $this->_soapVar(null, 'OpenIn4'),
            $this->_soapVar(null, 'OpenIn5'),
            $this->_soapVar(null, 'OpenIn6'),
            $this->_soapVar(null, 'OpenIn7'),
        ];
    }

	protected function _calculatePrice(Item $quoteItem): float
	{
		if ($quoteItem->getParentItem() && $quoteItem->getParentItem()->getProductType() === Configurable::TYPE_CODE) {
			$rowTotal = $quoteItem->getParentItem()->getBaseRowTotal();
			$discountAmount = $quoteItem->getParentItem()->getBaseDiscountAmount();
			$qtyOrdered = $quoteItem->getParentItem()->getQty();
		} else {
			$rowTotal = $quoteItem->getBaseRowTotal();
			$discountAmount = $quoteItem->getBaseDiscountAmount();
			$qtyOrdered = $quoteItem->getQty();
		}

        return ($rowTotal - $discountAmount) / $qtyOrdered;
	}

    public function getResidentialCommercialValue(string $value = ''): string
    {
        return $value === 'B' ? Shipping::COMMERCIAL_FLAG : Shipping::RESIDENTIAL_FLAG;
    }
}
