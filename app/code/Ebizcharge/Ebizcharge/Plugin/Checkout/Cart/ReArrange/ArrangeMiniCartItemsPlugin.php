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

namespace Ebizcharge\Ebizcharge\Plugin\Checkout\Cart\ReArrange;


use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Ebizcharge\Ebizcharge\Model\QuoteFactory;
use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\CustomerData\ItemPool;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;


/**
 * Default config provider Plugin
 *
 * Class DefaultConfigProvider
 */
class ArrangeMiniCartItemsPlugin
{

    /**
     * @var QuoteFactory
     */
    protected QuoteFactory $_quoteFactory;
    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;

    /**
     * @var ConfigFactory
     */
    protected ConfigFactory $configFactory;

    /**
     * @var ItemPool
     */
    protected ItemPool $itemPool;

    /**
     * @param ConfigFactory $configFactory
     * @param QuoteFactory $_quoteFactory
     * @param CheckoutSession $checkoutSession
     * @param ItemPool $itemPool
     */
    public function __construct(
        ConfigFactory   $configFactory,
        QuoteFactory    $_quoteFactory,
        CheckoutSession $checkoutSession,
        ItemPool        $itemPool

    )
    {

        /** @var  configFactory */
        $this->configFactory = $configFactory;
        /** @var  _quoteFactory */
        $this->_quoteFactory = $_quoteFactory;
        /** @var  checkoutSession */
        $this->checkoutSession = $checkoutSession;
        /** @var  itemPool */
        $this->itemPool = $itemPool;
    }

    /**
     * After Get Section Data
     *
     * @param Cart $subject
     * @param array $result
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetSectionData(Cart $subject, array $result = [])
    {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);
        $quote = $this->checkoutSession->getQuote();
        $quoteItems = $quote->getAllVisibleItems() ?? [];
        $newQuoteItems = [];

        if ($isEbizChargeActive) {
            if ($configFactory->isRecurringActive($storeId)) {
                $arrangedQuoteItems = $this->_quoteFactory->create()->reArrangeSubscribedCartItems($quoteItems);
                if (count($arrangedQuoteItems) > 0) {
                    foreach ($arrangedQuoteItems as $arrangedQuoteItem) {
                        $newQuoteItems[] = $this->itemPool->getItemData($arrangedQuoteItem);
                    }
                }
                $result["items"] = $newQuoteItems;
            }
        }

        return $result;
    }


}
