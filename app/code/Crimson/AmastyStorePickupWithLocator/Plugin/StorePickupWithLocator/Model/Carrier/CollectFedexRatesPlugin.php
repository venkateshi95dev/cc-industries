<?php
declare(strict_types=1);

namespace Crimson\AmastyStorePickupWithLocator\Plugin\StorePickupWithLocator\Model\Carrier;

use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;
use Crimson\AmastyStorePickupWithLocator\Model\Config\AmastyStorePickupFedexConfig;
use Magento\Fedex\Model\Carrier;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Interceptor for @see Shipping
 */
class CollectFedexRatesPlugin
{
    public function __construct(
        protected readonly AmastyStorePickupFedexConfig $config,
        protected readonly Carrier $fedexCarrier,
    ) {
    }

    public function aroundCollectRates(Shipping $subject, callable $proceed, RateRequest $request)
    {
        $logger = new \Monolog\Logger('crimson_fedex_rates');
        $logger->pushHandler(new \Monolog\Handler\StreamHandler(BP . '/var/log/crimson_fedex_rates.log'));

        $logger->info('=== STARTING FEDEX RATE COLLECTION ===');

        $originalProceed = $proceed($request);

        if (!$this->config->isEnabled()) {
            $logger->info('Config is DISABLED - returning original rates');
            return $originalProceed;
        }

        if(!$this->config->selectedDeliveryMethod()){
            $logger->info('Delivery method is not selected - returning original rates');
            return $originalProceed;
        }

        $logger->info('Config is ENABLED - collecting FedEx rates');

        try {
            //setting request flag to later handle the "residential"
            $request->setData('am_store_pickup', true);
            $fedexRates = $this->fedexCarrier->collectRates($request);

            $logger->info('FedEx rates collected - Total rates: ' . count($fedexRates->getAllRates()));

            if (empty($fedexRates->getAllRates())) {
                $logger->info('No rates returned - returning original rates');
                return $originalProceed;
            }

            $configMethodForPickUp = $this->config->selectedDeliveryMethod();
            if (!$configMethodForPickUp) {
                $logger->info('No method set in Admin config - returning original rates');
                return $originalProceed;
            }

            $homeRate = null;
            foreach ($fedexRates->getAllRates() as $rate) {
                if ($rate->getData('method') === $configMethodForPickUp) {
                    $homeRate = $rate;
                    $cost = $rate->getData('cost');
                    $price = $rate->getData('price');
                    $logger->info("*** FOUND GROUND_DELIVERY - Cost: {$cost}, Price: {$price} ***");
                    break;
                }
            }

            if ($homeRate) {
                $amastyRate = $originalProceed->getAllRates()[0];
                $amastyRate->setData('cost', $homeRate->getData('cost'));
                $amastyRate->setData('price', $homeRate->getData('price'));
                $logger->info("*** RATE UPDATED ***");
            } else {
                $logger->warning('GROUND_DELIVERY rate NOT FOUND in FedEx rates');
            }

        } catch (\Exception $e) {
            $logger->error('ERROR collecting FedEx rates: ' . $e->getMessage());
            $logger->error('Stack trace: ' . $e->getTraceAsString());
        }

        $logger->info('=== FEDEX RATE COLLECTION COMPLETED ===');

        return $originalProceed;
    }
}
