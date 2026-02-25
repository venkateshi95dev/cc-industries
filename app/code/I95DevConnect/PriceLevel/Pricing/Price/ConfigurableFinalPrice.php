<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Pricing\Price;

use I95DevConnect\PriceLevel\Helper\Data;
use I95DevConnect\PriceLevel\Model\ItemPrice;
use Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\SaleableInterface;

/**
 * Configurable Product Final price model
 */
class ConfigurableFinalPrice extends \Magento\ConfigurableProduct\Pricing\Price\FinalPrice
{
    /**
     * @var PriceResolverInterface
     */
    protected $priceResolver;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var SessionFactory
     */
    public $session;

    /**
     * @var ItemPrice
     */
    public $itemPrice;

    /**
     * @var Data
     */
    public $data;

    /**
     * @param SessionFactory $session
     * @param ItemPrice $itemPrice
     * @param Data $data
     * @param SaleableInterface $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param PriceResolverInterface $priceResolver
     */
    public function __construct(
        SessionFactory $session,
        ItemPrice $itemPrice,
        Data $data,
        SaleableInterface $saleableItem,
        float $quantity,
        CalculatorInterface $calculator,
        PriceCurrencyInterface $priceCurrency,
        PriceResolverInterface $priceResolver
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency, $priceResolver);
        $this->session = $session;
        $this->itemPrice = $itemPrice;
        $this->data = $data;
    }
    /**
     * Returns price amount
     *
     * @return float
     */
    public function getValue()
    {
        $finalPrice = parent::getValue();
        if ($this->data->isEnabled()) {
            $customerId =  $this->session->create()->getCustomer()->getId();
            $qty = 1;
            $itemP = $this->itemPrice->getItemFinalPrice($this->product, $customerId, $qty);
            if ($itemP != 0) {
                return $this->data->convertPrice($itemP);
            }
        }
        return $finalPrice;
    }
}
