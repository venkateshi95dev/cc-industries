<?php
namespace Silk\Coker\Preference\Magento\Shipping\Model\Shipping;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Address;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Store\Model\ScopeInterface;
use Magento\User\Model\User;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Shipping\Model\Rate\PackageResultFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;

/**
 * @inheritDoc
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Labels extends \Magento\Shipping\Model\Shipping\Labels
{
    /**
     * @var RateRequestFactory
     */
    private $rateRequestFactory;

    /**
     * @var PackageResultFactory
     */
    private $packageResultFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Shipping\Model\Shipment\RequestFactory $shipmentRequestFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Math\Division $mathDivision,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Backend\Model\Auth\Session $authSession,
        Request $request,
        RateRequestFactory $rateRequestFactory = null,
        ?PackageResultFactory $packageResultFactory = null
    ) {
        $this->_authSession = $authSession;
        $this->_request = $request;
        parent::__construct(
            $scopeConfig,
            $shippingConfig,
            $storeManager,
            $carrierFactory,
            $rateResultFactory,
            $shipmentRequestFactory,
            $regionFactory,
            $mathDivision,
            $stockRegistry,
            $authSession,
            $request
        );

        $this->rateRequestFactory = $rateRequestFactory ?: ObjectManager::getInstance()->get(RateRequestFactory::class);
        $this->packageResultFactory = $packageResultFactory
            ?? ObjectManager::getInstance()->get(PackageResultFactory::class);
    }

    public function collectCarrierRates($carrierCode, $request)
    {
        try {
            $carrier = $this->prepareCarrier($carrierCode, $request);
        } catch (\RuntimeException $exception) {
            return $this;
        }

        /** @var Result|\Magento\Quote\Model\Quote\Address\RateResult\Error|null $result */
        $result = null;
        if ($carrier->getConfigData('shipment_requesttype')) {
            $packages = $this->composePackagesForCarrier($carrier, $request);
            if (!empty($packages)) {
                //Multiple shipments
                /** @var PackageResult $result */
                $result = $this->packageResultFactory->create();
                foreach ($packages as $weight => $packageCount) {
                    $request->setPackageWeight($weight);
                    $packageResult = $carrier->collectRates($request);
                    if (!$packageResult) {
                        return $this;
                    } else {
                        $result->appendPackageResult($packageResult, $packageCount);
                    }
                }
            }
        }
        if (!$result) {
            //One shipment for all items.
            $result = $carrier->collectRates($request);
        }

        if (!$result) {
            return $this;
        } elseif ($result instanceof Result) {
            $this->getResult()->appendResult($result, $carrier->getConfigData('showmethod') != 0);
        } else {
            $this->getResult()->append($result);
        }

        return $this;
    }


    /**
     * Prepare carrier to find rates.
     *
     * @param string $carrierCode
     * @param RateRequest $request
     * @return AbstractCarrier
     * @throws \RuntimeException
     */
    private function prepareCarrier(string $carrierCode, RateRequest $request): AbstractCarrier
    {
        $carrier = $this->isShippingCarrierAvailable($carrierCode, $request->getStoreId())
            ? $this->_carrierFactory->create($carrierCode, $request->getStoreId())
            : null;
        if (!$carrier) {
            throw new \RuntimeException('Failed to initialize carrier');
        }
        $carrier->setActiveFlag($this->_availabilityConfigField);
        $result = $carrier->checkAvailableShipCountries($request);
        if (false !== $result && !$result instanceof Error) {
            $result = $carrier->processAdditionalValidation($request);
        }
        if (!$result) {
            /*
             * Result will be false if the admin set not to show the shipping module
             * if the delivery country is not within specific countries
             */
            throw new \RuntimeException('Cannot collect rates for given request');
        } elseif ($result instanceof Error) {
            $this->getResult()->append($result);
            throw new \RuntimeException('Error occurred while preparing a carrier');
        }

        return $carrier;
    }

    /**
     * Checks availability of carrier.
     *
     * @param string $carrierCode
     * @param string $storeId
     * @return bool
     */
    private function isShippingCarrierAvailable(string $carrierCode, string $storeId): bool
    {
        return $this->_scopeConfig->isSetFlag(
            'carriers/' . $carrierCode . '/' . $this->_availabilityConfigField,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
