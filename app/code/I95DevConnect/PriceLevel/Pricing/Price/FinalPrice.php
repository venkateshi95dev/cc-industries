<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Pricing\Price;

use I95DevConnect\PriceLevel\Model\ItemPrice;
use Magento\Catalog\Pricing\Price\FinalPriceInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\SaleableInterface;
use I95DevConnect\PriceLevel\Helper\Data;

/**
 * Final price model
 */
class FinalPrice extends AbstractPrice implements FinalPriceInterface
{
    /**
     * Price type final
     */
    public const PRICE_CODE = 'final_price';

    /**
     * @var BasePrice
     */
    public $basePrice;

    /**
     * @var AmountInterface
     */
    public $minimalPrice;

    /**
     * @var AmountInterface
     */
    public $maximalPrice;

    /**
     * @var SessionFactory
     */
    public $session;

    /**
     * @var ItemPrice
     */
    public $itemPrice;

    /**
     * @var PriceInfoInterface
     */
    public $priceInfo;

    /**
     * @var Data
     */
    public $data;

    /**
     * Class constructor to include all the dependencies
     *
     * @param SessionFactory $session
     * @param ItemPrice $itemPrice
     * @param Data $data
     * @param CalculatorInterface $calculator
     * @param SaleableInterface $saleableItem
     * @param float $quantity
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        SessionFactory $session,
        ItemPrice $itemPrice,
        Data $data,
        CalculatorInterface $calculator,
        SaleableInterface $saleableItem,
        $quantity,
        PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->session = $session;
        $this->itemPrice = $itemPrice;
        $this->data = $data;
    }

    /**
     * Get Price of a product for the customer
     *
     * @return float|bool
     */
    public function getValue()
    {
        $customerId =  $this->session->create()->getCustomer()->getId();
        $qty = 1;
        $finalPrice = $this->itemPrice->getItemFinalPrice($this->product, $customerId, $qty);
        $finalPrice = $this->data->convertPrice($finalPrice);
        if ((int)$finalPrice !== 0) {
            return $finalPrice;
        } else {
            return max(0, $this->getBasePrice()->getValue());
        }
    }

    /**
     * Get Minimal Price Amount for the product
     *
     * @return AmountInterface
     */
    public function getMinimalPrice()
    {
        if (!$this->minimalPrice) {
            $minimal_price = $this->product->getMinimalPrice();
            if ($minimal_price === null) {
                $minimal_price = $this->getValue();
            } else {
                $minimal_price = $this->priceCurrency->convertAndRound($minimal_price);
            }
            $this->minimalPrice = $this->calculator->getAmount($minimal_price, $this->product);
        }
        return $this->minimalPrice;
    }

    /**
     * Get Maximal Price Amount for the product
     *
     * @return AmountInterface
     */
    public function getMaximalPrice()
    {
        if (!$this->maximalPrice) {
            $this->maximalPrice = $this->calculator->getAmount($this->getValue(), $this->product);
        }
        return $this->maximalPrice;
    }

    /**
     * Retrieve base price instance lazily
     *
     * @return BasePrice|PriceInterface
     */
    public function getBasePrice()
    {
        if (!$this->basePrice) {
            $this->basePrice = $this->priceInfo->getPrice(BasePrice::PRICE_CODE);
        }
        return $this->basePrice;
    }
}
