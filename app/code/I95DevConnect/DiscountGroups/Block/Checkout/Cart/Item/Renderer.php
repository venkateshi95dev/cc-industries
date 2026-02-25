<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Block\Checkout\Cart\Item;

use I95DevConnect\DiscountGroups\Helper\Data;
use I95DevConnect\DiscountGroups\Model\ApplyDiscount;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Helper\Product\Configuration;
use Magento\Customer\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class Renderer extends \Magento\Checkout\Block\Cart\Item\Renderer
{
    /**
     * @var ApplyDiscount
     */
    protected $applyDiscount;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var storeManager
     */
    protected $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @var Data
     */
    public $discountGroupsHelper;

    /**
     * Class constructor
     *
     * @param Context $context
     * @param Configuration $productConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param ImageBuilder $imageBuilder
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param ManagerInterface $messageManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param Manager $moduleManager
     * @param InterpretationStrategyInterface $messageInterpretationStrategy
     * @param ApplyDiscount $applyDiscount
     * @param Session $customerSession
     * @param Data $discountGroupsHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Configuration $productConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        ImageBuilder $imageBuilder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        ManagerInterface $messageManager,
        PriceCurrencyInterface $priceCurrency,
        Manager $moduleManager,
        InterpretationStrategyInterface $messageInterpretationStrategy,
        ApplyDiscount $applyDiscount,
        Session $customerSession,
        Data $discountGroupsHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $productConfig,
            $checkoutSession,
            $imageBuilder,
            $urlHelper,
            $messageManager,
            $priceCurrency,
            $moduleManager,
            $messageInterpretationStrategy,
            $data
        );
        $this->applyDiscount = $applyDiscount;
        $this->customerSession = $customerSession;
        $this->discountGroupsHelper = $discountGroupsHelper;
    }

    /**
     * Get discount block
     *
     * @param AbstractItem $item
     * @return mixed
     */
    public function getDiscountBlock(AbstractItem $item)
    {
        $isEnabled = $this->discountGroupsHelper->isDiscountGroupsEnabled();
        if (!$isEnabled) {
            return parent::getDiscountBlock($item);
        }
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('checkout.item.discount.unit');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * Get discount amount
     *
     * @param AbstractItem $item
     * @return mixed
     */
    public function getDiscountAmount($item)
    {
        $isEnabled = $this->discountGroupsHelper->isDiscountGroupsEnabled();
        if (!$isEnabled) {
            return parent::getDiscountAmount($item);
        }
        $customerId = $this->customerSession->getCustomer()->getId();
        $discountGroup = $this->applyDiscount->getDiscountPercentage($item, $customerId);
        return $discountGroup['discountAmount'];
    }

    /**
     * Get row total
     *
     * @param AbstractItem $item
     * @return float|int
     */
    public function getRowTotal(AbstractItem $item)
    {
        $isEnabled = $this->discountGroupsHelper->isDiscountGroupsEnabled();
        if (!$isEnabled) {
            return parent::getRowTotal($item);
        }
        $itemRowTotal = $item->getRowTotal();
        $discount = $this->getDiscountAmount($item);
        return $itemRowTotal - ($itemRowTotal * $discount) / 100;
    }
}
