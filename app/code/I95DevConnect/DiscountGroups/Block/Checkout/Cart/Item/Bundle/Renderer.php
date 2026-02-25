<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Block\Checkout\Cart\Item\Bundle;

use I95DevConnect\DiscountGroups\Helper\Data;
use I95DevConnect\DiscountGroups\Model\ApplyDiscount;
use Magento\Bundle\Helper\Catalog\Product\Configuration;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Checkout\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class Renderer extends \Magento\Bundle\Block\Checkout\Cart\Item\Renderer
{
    /**
     * @var ApplyDiscount
     */
    protected $applyDiscount;

    /**
     * @var \Magento\Customer\Model\Session
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
     * @var Configuration
     */
    public $bundleProductConfiguration;
    /**
     * @var bool
     */
    protected $_isScopePrivate;// phpcs:ignore

    /**
     * Class constructor
     *
     * @param Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
     * @param Session $checkoutSession
     * @param ImageBuilder $imageBuilder
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param ManagerInterface $messageManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param Manager $moduleManager
     * @param InterpretationStrategyInterface $messageInterpretationStrategy
     * @param Configuration $bundleProductConfiguration
     * @param ApplyDiscount $applyDiscount
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Data $discountGroupsHelper
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        Session $checkoutSession,
        ImageBuilder $imageBuilder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        ManagerInterface $messageManager,
        PriceCurrencyInterface $priceCurrency,
        Manager $moduleManager,
        InterpretationStrategyInterface $messageInterpretationStrategy,
        Configuration $bundleProductConfiguration,
        ApplyDiscount $applyDiscount,
        \Magento\Customer\Model\Session $customerSession,
        Data $discountGroupsHelper,
        array $data = []
    ) {
        $this->bundleProductConfiguration = $bundleProductConfiguration;
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
            $bundleProductConfiguration,
            $data
        );
        $this->_isScopePrivate = true;
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
        /** @var \I95DevConnect\DiscountGroups\Block\Checkout\Cart\Item\Renderer $block */
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
