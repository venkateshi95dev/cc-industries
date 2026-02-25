<?php

namespace Crimson\CorvetteCentralFlatRateCustomQuote\Model\Carrier;

use Crimson\CorvetteCentralFlatRateCustomQuote\Model\CorvetteCentralFlatRateCustomQuoteConfig;
use Crimson\CorvetteCentralFlatRateCustomQuote\Service\RequestService;
use Crimson\CorvetteCentralShipping\Model\Config\Source\USAtypicalRegions;
use Crimson\CorvetteCentralShipping\Model\CorvetteCentralShippingConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

class FlatRateCustomQuote extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'flatrate_customquote';

    /**
     * @var bool
     */
    protected $_isFixed = true;
    protected array $_usAtypicalRegions = [];

    public function __construct(
        ScopeConfigInterface            $scopeConfig,
        ErrorFactory                    $rateErrorFactory,
        LoggerInterface                 $logger,
        protected ResultFactory         $rateResultFactory,
        protected MethodFactory         $rateMethodFactory,
        protected ItemPriceCalculator   $itemPriceCalculator,
        protected CorvetteCentralShippingConfig $shippingConfig,
        protected RequestService $requestService,
        array                           $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->_usAtypicalRegions = $this->shippingConfig->getUSAtypicalRegions($this->_code);
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if (!$this->_doesRequestHaveRequiredFields($request)) {
            return false;
        }

        if (!$this->requestService->isFullFlatRateCustomQuoteRequest($request)) {
            return false;
        }

        $freeBoxes = $this->getFreeBoxesCount($request);
        $this->setFreeBoxes($freeBoxes);

        $result = $this->rateResultFactory->create();
        $shippingPrice = $this->getShippingPrice($request, $freeBoxes);
        if ($shippingPrice !== false) {
            $method = $this->createResultMethod($shippingPrice);
            $result->append($method);
        }

        return $result;
    }

    /**
     * Get count of free boxes
     *
     * @param RateRequest $request
     * @return int
     */
    private function getFreeBoxesCount(RateRequest $request)
    {
        $freeBoxes = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                $freeShippingMethod = $item->getFreeShippingMethod();

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    $freeBoxes += $this->getFreeBoxesCountFromChildren($item);
                } elseif (
                    $item->getFreeShipping()
                    && ($freeShippingMethod === null || $freeShippingMethod === 'flatrate_flatrate')
                ) {
                    $freeBoxes += $item->getQty();
                }
            }
        }
        return $freeBoxes;
    }

    public function getAllowedMethods(): array
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * Returns shipping price
     *
     * @param RateRequest $request
     * @param int $freeBoxes
     * @return bool|float
     */
    private function getShippingPrice(RateRequest $request, $freeBoxes)
    {
        $shippingPrice = false;

        $configPrice = $this->getConfigData('price');
        if ($this->getConfigData('type') === 'O') {
            // per order
            $shippingPrice = $this->itemPriceCalculator->getShippingPricePerOrder($request, $configPrice, $freeBoxes);
        } elseif ($this->getConfigData('type') === 'I') {
            // per item
            $shippingPrice = $this->itemPriceCalculator->getShippingPricePerItem($request, $configPrice, $freeBoxes);
        }

        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);

        if ($shippingPrice !== false && $request->getPackageQty() == $freeBoxes) {
            $shippingPrice = '0.00';
        }
        return $shippingPrice;
    }

    private function createResultMethod($shippingPrice): Method
    {
        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        return $method;
    }

    /**
     * Returns free boxes count of children
     *
     * @param mixed $item
     * @return mixed
     */
    private function getFreeBoxesCountFromChildren($item)
    {
        $freeBoxes = 0;
        foreach ($item->getChildren() as $child) {
            if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                $freeBoxes += $item->getQty() * $child->getQty();
            }
        }
        return $freeBoxes;
    }

    private function _doesRequestHaveRequiredFields(RateRequest $request): bool
    {
        if (empty($request->getDestCountryId())) {
            return false;
        }

        if (empty($this->_usAtypicalRegions) || $request->getDestCountryId() !== AbstractCarrier::USA_COUNTRY_ID) {
            return true;
        }

        //US checking we have Region and ZipCode not empty and with at least 5 characters
        if ($request->getDestCountryId() === AbstractCarrier::USA_COUNTRY_ID) {
            return !(empty($request->getDestPostcode()) || empty($request->getDestRegionCode()) || strlen($request->getDestPostcode()) < 5);
        }

        //US regular regions check(we need to do this FIRST)
        if ($request->getDestCountryId() === AbstractCarrier::USA_COUNTRY_ID && !in_array($request->getDestRegionCode(), USAtypicalRegions::ATYPICAL_US_REGIONS)) {
            return true;
        }

        //US atypical regions check
        if ($request->getDestCountryId() === AbstractCarrier::USA_COUNTRY_ID) {
            return $this->isUSAtypicalRegion($request);
        }

        return true;
    }

    public function isUSAtypicalRegion(RateRequest $request): bool
    {
        return !empty($request->getDestRegionCode()) &&
            !empty($this->_usAtypicalRegions) &&
            in_array($request->getDestRegionCode(), $this->_usAtypicalRegions);
    }
}
