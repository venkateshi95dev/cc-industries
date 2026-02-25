<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_AjaxCart
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AjaxCart\Model;

use Bss\AjaxCart\Api\CartItemRepositoryInterface;
use Bss\AjaxCart\Helper\Data;
use ErrorException;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Repository for quote item.
 */
class CartItemRepository implements CartItemRepositoryInterface
{
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var DataPricing
     */
    protected $dataPricing;

    /**
     * @var Data
     */
    protected $config;

    /**
     * @param QuoteRepository $quoteRepository
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param DataPricing $dataPricing
     * @param Data $config
     */
    public function __construct(
        QuoteRepository            $quoteRepository,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface      $storeManager,
        DataPricing                $dataPricing,
        Data $config
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->dataPricing = $dataPricing;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $cartItem
     * @return array
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function save(CartItemInterface $cartItem)
    {
        //Validate input
        if (!$this->validateInput($cartItem)) {
            return ["status : error", "message : You must enter all field!"];
        }
        /** @var Quote $quote */
        $cartId = $cartItem->getQuoteId();
        if (!$cartId) {
            throw new InputException(__('"%fieldName" is required. Enter and try again.', ['fieldName' => 'quoteId']));
        }
        $quote = $this->quoteRepository->getActive($cartId);
        try {
            $product = $this->productRepository->get($cartItem->getSku());
        } catch (ErrorException $ex) {
            return $ex = ["status" => "error", "message" => "Something went wrong when save product"];
        }
        //Check product is not simple ... message choose options to add
        if ($this->checkTypeOfProduct($cartItem, $product)) {
            return ["status : error", "message : You need to choose options for your item!"];
        }
        //add product to cart
        $this->quoteSaveAfterCheckTypeProduct($cartItem, $quote);
        $lastedItem = $quote->getLastAddedItem();
        return $this->responseResultSuccess($cartItem, $product, $lastedItem);
    }

    /**
     * Check input # null
     *
     * @param CartItemInterface $cartItem
     * @return bool
     */
    public function validateInput(CartItemInterface $cartItem)
    {
        if ($cartItem->getSku() == "" || $cartItem->getQty() == "") {
            return false;
        }
        return true;
    }

    /**
     * Check type of product
     *
     * @param CartItemInterface $cartItem
     * @param ProductInterface $product
     * @return bool
     */
    public function checkTypeOfProduct(CartItemInterface $cartItem, ProductInterface $product)
    {
        $type = ["bundle", "grouped", "configurable"];
        $typeOfProduct = $product->getTypeId();
        if (in_array($typeOfProduct, $type)) {
            if ($cartItem->getProductOption()) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * Quote save after check type product
     *
     * @param CartItemInterface $cartItem
     * @param Quote $quote
     * @return void
     */
    public function quoteSaveAfterCheckTypeProduct(CartItemInterface $cartItem, Quote $quote)
    {
        try {
            $quoteItems = $quote->getItems();
            $quoteItems[] = $cartItem;
            $quote->setItems($quoteItems);
            $this->quoteRepository->save($quote);
            $quote->collectTotals();
        } catch (ErrorException $ex
        ) {
            $ex = ["status" => "error", "message" => "Something went wrong when save product"];
        }
    }

    /**
     * Display response result
     *
     * @param CartItemInterface $cartItem
     * @param ProductInterface $product
     * @param Item $lastedItem
     * @return array
     * @throws NoSuchEntityException
     */
    public function responseResultSuccess(
        CartItemInterface $cartItem,
        ProductInterface  $product,
        Item              $lastedItem
    ): array {
        $quoteId = $cartItem->getQuoteId();
        $price = $this->dataPricing->getCurrentPriceFinal($lastedItem);
        $sumPrice = $this->dataPricing->getSumPriceInCart($quoteId);
        $relationProduct = $this->getProductFollowConfig($product);
        $result["cart_product"] = [
            "status" => "success",
            "name" => $product->getName(),
            "items_qty_in_cart" => $this->dataPricing->getTotalInCart($quoteId),
            "price" => $this->dataPricing->getCurrentPrice($price),
            "cart_subtotal" => $this->dataPricing->getCurrentPrice($sumPrice),
            "relation_product" => $this->getInformationRelationProduct($relationProduct),
        ];
        return $result;
    }

    /**
     * Return relation products follow config in admin
     *
     * @param Product $product
     * @return array
     * @throws NoSuchEntityException
     */
    public function getProductFollowConfig(ProductInterface $product)
    {
        $config = $this->config->getSuggestSource();
        $enable = $this->config->isShowSuggestBlock();
        $productRE = [];
        if ($config == 0 && $enable) {
            $productRE = $this->getInformationRelationProduct($product->getRelatedProducts());
        }
        if ($config == 1 && $enable) {
            $productRE = $this->getInformationRelationProduct($product->getUpSellProducts());
        }
        if ($config == 2 && $enable) {
            $productRE = $this->getInformationRelationProduct($product->getCrossSellProducts());
        }
        return $productRE;
    }

    /**
     * Use to get some information relation product :>>
     *
     * @param array $product
     * @return array
     * @throws NoSuchEntityException
     */
    public function getInformationRelationProduct(array $product)
    {
        $result = [];
        foreach ($product as $item) {
            $relation = $this->productRepository->get($item['sku']);
            //get url image
            $productImageUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) .
                'catalog/product' . $relation->getImage();
            $productUrl = $relation->getProductUrl();
            $price = $relation->getPrice();
            $result[] = [
                "sku" => $relation->getSku(),
                'name' => $relation->getName(),
                "price" => $this->dataPricing->getCurrentPrice(floatval($price)),
                "url_image_product" => $productImageUrl,
                "url_product" => $productUrl
            ];
        }
        return $result;
    }
}
