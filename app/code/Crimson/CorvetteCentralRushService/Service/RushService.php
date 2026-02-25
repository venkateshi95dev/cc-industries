<?php

namespace Crimson\CorvetteCentralRushService\Service;

use Crimson\CorvetteCentralRushService\Model\CorvetteCentralRushServiceConfig;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;

class RushService
{

    CONST SKU          = 'rush-service';
    CONST PRODUCT_TYPE = Type::TYPE_VIRTUAL;
    CONST URL          = 'rush_service/adjust/index';
    CONST ATTR_DROPSHIP_ONLY = 'cc_dropship_only';
    CONST ATTR_MAY_DROPSHIP = 'cc_may_ship_from_manufacturer';


    public function __construct(
        protected CorvetteCentralRushServiceConfig $corvetteCentralRushServiceConfig,
        protected ProductRepositoryInterface $productRepositoryInterface,
        protected UrlInterface $urlInterface,
        protected FormKey $formKey,
        protected Cart $cart,
        protected LoggerInterface $logger
    ) {}

    public function isEnabled(): bool
    {
        return $this->corvetteCentralRushServiceConfig->isEnabled();
    }

    public function isEligible()
    {
        $isEligible = false;
        foreach ($this->cart->getItems() as $item){
            if($this->isItemEligibleForRush($item)){
                $isEligible = true;
                break;
            }
        }
        return $isEligible;
    }

    public function isItemEligibleForRush($item): bool
    {
        if((int)$item->getProduct()->getData(self::ATTR_DROPSHIP_ONLY) == 0 && (int)$item->getProduct()->getData(self::ATTR_MAY_DROPSHIP) == 0){
            $stockItem = $item->getProduct()->getExtensionAttributes()->getStockItem();
            if ($stockItem->getIsInStock() && $stockItem->getQty() >= $item->getQty()) {
                return true;
            }
        }
        return false;
    }

    public function getLabel(): string
    {
        return $this->corvetteCentralRushServiceConfig->getLabel();
    }

    public function getMessage(): string
    {
        return $this->corvetteCentralRushServiceConfig->getMessage();
    }

    public function doesSkuExist(): ?ProductInterface
    {
        try {
            return $this->productRepositoryInterface->get(self::SKU);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getUrl(): string
    {
        return $this->urlInterface->getUrl(self::URL);
    }

    public function doesCartHaveRushService(CartInterface $quote): bool
    {
        foreach ($quote->getItems() as $item) {
            /** @var Item $item */
            if ($this->isItemProductRushService($item)) {
                return true;
            }
        }

        return false;
    }

    public function isItemProductRushService(Item|Product $element): bool
    {
        if ($element instanceof Item &&
            $element->getProduct()->getTypeId() === Type::TYPE_VIRTUAL &&
            $element->getProduct()->getSku() === self::SKU
        ) {
            return true;
        }

        if ($element instanceof Product &&
            $element->getTypeId() === Type::TYPE_VIRTUAL &&
            $element->getSku() === self::SKU
        ) {
            return true;
        }

        return false;
    }

    public function addRushServiceToCart(?ProductInterface $product): array
    {
        try {
            //if no product passed we try to load the rush service product
            if (!$product) {
                $product = $this->doesSkuExist();
            }

            //checking if the rush service product exists
            if (!$product) {
                throw new \Exception(__('Rush Service product does not exist.'));
            }

            $params = [
                'form_key' => $this->formKey->getFormKey(),
                'product'  => $product->getId(),
                'qty'      => 1
            ];
            $this->cart->addProduct($product, $params);
            $this->cart->save();
            $response['error']   = false;
            $response['message'] = __("Rush Service added successfully.");
        } catch (\Exception $e) {
            $response['error'] = true;
            $response['message'] = __("Error adding the Rush Service. Please contact us.");
            $this->logger->error($e->getMessage());
        }

        return $response;
    }

    public function removeRushServiceFromCart(): array
    {
        try {
            $quote = $this->cart->getQuote();
            foreach ($quote->getItems() as $item) {
                if ($this->isItemProductRushService($item)) {
                    $this->cart->removeItem($item->getId());
                }
            }
            $quote->setTotalsCollectedFlag(false);
            $this->cart->save();
            $response['error']   = false;
            $response['message'] = __("Rush Service removed successfully.");
        } catch (\Exception $e) {
            $response['error'] = true;
            $response['message'] = __("Error removing the Rush Service. Please contact us.");
            $this->logger->error($e->getMessage());
        }

        return $response;
    }
}
