<?php
declare(strict_types=1);

namespace Crimson\CorvetteCentral\Plugin\Magento\Quote\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;

class PreventDiscontinuedProducts
{
    public function __construct(
        protected readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function beforeAddProduct(
        Quote $subject,
        Product $product,
        $request = null,
        $processMode = AbstractType::PROCESS_MODE_FULL
    ) {
        if ($product->getId()) {
            $websiteCode = $this->storeManager->getWebsite()->getCode();

            if ($websiteCode === "corvette_central_webiste") {
                $attributeValue = $product->getData('item_discount_group');

                if ($attributeValue && strtolower($attributeValue) === "disc") {
                    throw new LocalizedException(
                        __('This product is discontinued and cannot be added to the cart.')
                    );
                }
            }
        }

        return [$product, $request, $processMode];
    }
}
