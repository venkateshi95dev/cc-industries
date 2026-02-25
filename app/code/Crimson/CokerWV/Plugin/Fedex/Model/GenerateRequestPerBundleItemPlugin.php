<?php
declare(strict_types=1);

namespace Crimson\CokerWV\Plugin\Fedex\Model;

use Crimson\CokerWV\Model\Config\FedexBundleConfig;
use Crimson\CokerWV\Setup\Patch\Data\AddCanBeBundledInShipmentAttributePatch;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Fedex\Model\Carrier;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\ResultFactory;

class GenerateRequestPerBundleItemPlugin
{
    public function __construct(
        protected readonly ResultFactory $rateResultFactory,
        protected readonly FedexBundleConfig $fedexConfig,
        protected readonly ProductRepositoryInterface $productRepository,
    ) {
    }

    public function aroundCollectRates(Carrier $subject, callable $proceed, RateRequest $request)
    {
        $storeId = (int)($request->getStoreId() ?? 0);
        $enabledStores = $this->fedexConfig->getEnabledStores();

        if (!in_array($storeId, $enabledStores, true)) {
            return $proceed($request);
        }

        $maxWeight = (float)$this->fedexConfig->getMaxBundleWeight();
        /** @var Product[] $items */
        $items = $request->getAllItems();

        if (empty($items)) {
            return $proceed($request);
        }

        $boxes = [];

        foreach ($items as $item) {
            $product = $item->getProduct();
            $product = $this->productRepository->getById($product->getId());
            if ($product->getTypeId() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                continue;
            }

            if ($item->getFreeShipping()) {
                continue;
            }

            $qty = (int)$item->getQty();
            $weight = (float)$item->getWeight();
            $canBeBundled = (bool)$product->getCustomAttribute(AddCanBeBundledInShipmentAttributePatch::ATTRIBUTE_CODE)?->getValue();//false

            if ($canBeBundled === false) {
                for ($i = 0; $i < $qty; $i++) {
                    $boxes[] = ['weight' => $weight, 'price' => $item->getBasePrice()];
                }
            } else {
                $currentBoxWeight = 0.0;
                $totalItemsInABox = 0;
                for ($i = 0; $i < $qty; $i++) {
                    if ($weight > $maxWeight) {
                        $totalItemsInABox = 0;
                        $boxes[] = ['weight' => $weight, 'price' => $item->getBasePrice()];
                        continue;
                    }

                    if ($currentBoxWeight + $weight > $maxWeight) {
                        $boxes[] = ['weight' => $currentBoxWeight, 'price' => $item->getBasePrice() * $totalItemsInABox];
                        $currentBoxWeight = 0.0;
                        $totalItemsInABox = 0;
                    }
                    $currentBoxWeight += $weight;
                    $totalItemsInABox++;
                }

                //This is in case there's a remaining weight in the box
                if ($currentBoxWeight > 0) {
                    $boxes[] = ['weight' => $currentBoxWeight, 'price' => $item->getBasePrice() * $totalItemsInABox];
                }
            }
        }

        return $this->singleRequest($boxes, $request, $proceed);
    }

    public function singleRequest($boxes, $request, $proceed)
    {
        $request->setPackages($boxes);
        return $proceed($request);
    }

    public function multipleRequests($boxes, $request, $proceed)
    {
        $finalResult = $this->rateResultFactory->create();
        $serviceRates = [];

        foreach ($boxes as $box) {
            $boxRequest = clone $request;
            $boxRequest->setPackageWeight($box['weight']);
            $boxRequest->setPackageQty(1);
            $boxRequest->setPackageValue($box['price']);
            $boxRequest->setPackageValueWithDiscount($box['price']);
            $boxRequest->setPackagePhysicalValue($box['price']);
            $boxRequest->setFreeMethodWeight($box['weight']);
            $boxRequest->setPackages([$box]);

            $boxResult = $proceed($boxRequest);
            if (!$boxResult || !$boxResult->getAllRates()) {
                continue;
            }

            foreach ($boxResult->getAllRates() as $rate) {
                $code = $rate->getMethod();
                if (!isset($serviceRates[$code])) {
                    $serviceRates[$code] = clone $rate;
                } else {
                    $serviceRates[$code]->setPrice($serviceRates[$code]->getPrice() + $rate->getPrice());
                    $serviceRates[$code]->setCost($serviceRates[$code]->getCost() + $rate->getCost());
                }
            }
        }

        foreach ($serviceRates as $rate) {
            $finalResult->append($rate);
        }

        return $finalResult;
    }
}
