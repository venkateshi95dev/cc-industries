<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Plugin;


use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;


/**
 * Subscribe button for item on admin order create
 *
 * Class SubscribeOrderButton
 */
class SubscribeOrderButton extends Grid
{

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected \Magento\Backend\Model\Session\Quote $sessionQuote;

    /**
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    protected \Magento\Sales\Model\AdminOrder\Create $orderCreate;

    protected ConfigFactory $configFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\GiftMessage\Model\Save $giftMessageSave
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param StockRegistryInterface $stockRegistry
     * @param StockStateInterface $stockState
     * @param ConfigFactory $configFactory
     * @param array $data
     * @param CatalogHelper|null $catalogHelper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote    $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create  $orderCreate,
        PriceCurrencyInterface                  $priceCurrency,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\GiftMessage\Model\Save         $giftMessageSave,
        \Magento\Tax\Model\Config               $taxConfig,
        \Magento\Tax\Helper\Data                $taxData,
        \Magento\GiftMessage\Helper\Message     $messageHelper,
        StockRegistryInterface                  $stockRegistry,
        StockStateInterface                     $stockState,
        ConfigFactory                           $configFactory,
        array                                   $data = [],
        ?CatalogHelper                          $catalogHelper = null
    )
    {
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $wishlistFactory, $giftMessageSave, $taxConfig, $taxData, $messageHelper, $stockRegistry, $stockState, $data, $catalogHelper);

        /** @var $sessionQuote */
        $this->sessionQuote = $sessionQuote;
        /** @var $configFactory */
        $this->configFactory = $configFactory;
    }

    /**
     * Subscribe button for items
     *
     * @param Grid $subject
     * @param string $result
     * @param Item $item
     * @return string
     * @throws LocalizedException
     */
    public function afterGetConfigureButtonHtml(Grid $subject, string $result, Item $item): string
    {
        $isCustomer = $this->sessionQuote->getCustomerId();
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);
        $isRecurringEnabled = $this->configFactory->create()->isRecurringEnabled();

        if($isEbizChargeActive) {
            if ($isRecurringEnabled) {
                if (!$isCustomer) {
                    return $result;
                }

                /** @var  $options */
                $options = [
                    'label' => $this->_escaper->escapeHtmlAttr(__('Subscribe'))
                ];
                $options['onclick'] = sprintf('order.showQuoteItemConfiguration(%s)', $item->getId());
                return $this->getLayout()
                    ->createBlock(Button::class)
                    ->setData($options)
                    ->toHtml();

            } else {
                return $result;
            }
        }
        return $result;
    }
}
