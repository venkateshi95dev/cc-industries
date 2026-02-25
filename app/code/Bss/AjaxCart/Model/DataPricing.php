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

use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as Pricing;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\QuoteRepository;

/**
 * This is a class pricing
 */
class DataPricing
{
    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelper;

    /**
     * @var Pricing
     */
    protected $dataPricing;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Bss\AjaxCart\Helper\Data
     */
    protected $config;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Weee\Helper\Data $weeeHelper
     * @param Pricing $dataPricing
     * @param QuoteRepository $quoteRepository
     * @param \Bss\AjaxCart\Helper\Data $config
     * @param ItemFactory $itemFactory
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        \Magento\Weee\Helper\Data $weeeHelper,
        Pricing                   $dataPricing,
        QuoteRepository           $quoteRepository,
        \Bss\AjaxCart\Helper\Data $config,
        ItemFactory               $itemFactory,
        DataObjectFactory         $dataObjectFactory
    ) {
        $this->dataPricing = $dataPricing;
        $this->weeeHelper = $weeeHelper;
        $this->config = $config;
        $this->quoteRepository = $quoteRepository;
        $this->itemFactory = $itemFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Get final price ,subtraction tax , custom options ...
     *
     * @param Item $lastedItem
     * @return float
     */
    public function getCurrentPriceFinal(Item $lastedItem)
    {
        $resultPrice = $this->config->getProductTaxDisplayType() ==
        \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX ?
            $lastedItem->getPrice() : $lastedItem->getPriceInclTax();
        $weeTax = $this->weeeHelper->getWeeeTaxAppliedAmount($lastedItem);
        if ($weeTax) {
            $resultPrice += $weeTax;
        }
        return $resultPrice;
    }

    /**
     * Return current price and currency
     *
     * @param float $price
     * @return float|string
     */
    public function getCurrentPrice(float $price)
    {
        return $this->dataPricing->currency($price, true, false);
    }

    /**
     * Sum Price all product in cart
     *
     * Can merge with getTotalInCart, but im lazy
     *
     * @param string $quoteId
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getSumPriceInCart(string $quoteId)
    {
        $sumPrice = 0;
        $items = $this->quoteRepository->get($quoteId)->getAllVisibleItems();
        foreach ($items as $item) {
            $count = $item->getQty();
            $priceFinal = $this->getCurrentPriceFinal($item);
            $sumPrice += $count * $priceFinal;
        }
        return $sumPrice;
    }

    /**
     * Total count in cart
     *
     * @param string $quoteId
     * @return int
     * @throws NoSuchEntityException
     */
    public function getTotalInCart(string $quoteId)
    {
        $totalCount = 0;

        $items = $this->quoteRepository->get($quoteId)->getAllVisibleItems();
        foreach ($items as $item) {
            $totalCount += $item->getQty();
        }
        return $totalCount;
    }
}
