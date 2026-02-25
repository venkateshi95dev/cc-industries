<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Pricing\Price;

use I95DevConnect\PriceLevel\Helper\Data;
use I95DevConnect\PriceLevel\Model\ItemPrice;
use Magento\Bundle\Pricing\Price\BundleOptionPrice;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Bundle\Pricing\Price\FinalPriceInterface;

/**
 * Bundle Product Final price model
 */
class BundleFinalPrice extends \Magento\Bundle\Pricing\Price\FinalPrice
{
    /**
     * @var AmountInterface
     */
    protected $maximalPrice;

    /**
     * @var AmountInterface
     */
    protected $minimalPrice;

    /**
     * @var AmountInterface
     */
    protected $priceWithoutOption;

    /**
     * @var BundleOptionPrice
     */
    protected $bundleOptionPrice;

    /**
     * @var ItemPrice
     */
    private $itemPrice;
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SessionFactory
     */
    private $session;

    /**
     * BundleFinalPrice constructor.
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param ProductCustomOptionRepositoryInterface $productOptionRepository
     * @param ItemPrice $itemPrice
     * @param Data $helper
     * @param SessionFactory $session
     */
    public function __construct( // NOSONAR
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        PriceCurrencyInterface $priceCurrency,
        ProductCustomOptionRepositoryInterface $productOptionRepository,
        ItemPrice $itemPrice,
        Data $helper,
        SessionFactory $session
    ) {
        $this->itemPrice = $itemPrice;
        $this->helper = $helper;
        $this->session = $session;
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency, $productOptionRepository);
    }

    /**
     * Returns price amount
     *
     * @return AmountInterface
     */
    public function getAmount()
    {
        $this->minimalPrice = parent::getAmount();
        if ($this->helper->isEnabled()) {
            $customerId =  $this->session->create()->getCustomer()->getId();
            $qty = 1;
            $itemP = $this->itemPrice->getItemFinalPrice($this->product, $customerId, $qty);
            $finalPrice = $this->helper->convertPrice($itemP);
            if ($finalPrice != 0) {
                $finalPrice = (string)$finalPrice;
                $finalMinPrice = min($finalPrice, $this->minimalPrice);
                $this->minimalPrice = $this->calculator->getAmount($finalMinPrice, $this->product);
            }
        }
        return $this->minimalPrice;
    }
}
