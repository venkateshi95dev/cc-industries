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

namespace Ebizcharge\Ebizcharge\Plugin\Checkout\Cart;


use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Magento\Checkout\Block\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;


/**
 * Add Product to Cart Plugin
 */
class ArrangeCartItemsPlugin extends AbstractModel
{

    /**
     * After Get Items
     *
     * @param Cart $subject
     * @param array $result
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetItems(Cart $subject, array $result = [])
    {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);
        $quote = $this->checkoutSession->getQuote();
        $quoteItems = $quote->getAllVisibleItems() ?? [];
        $newQuoteItems = [];

        if ($isEbizChargeActive) {
            if ($this->isRecurringEnabled($this->getStoreId())) {
                $arrangedQuoteItems = $this->_quoteFactory->create()->reArrangeSubscribedCartItems($quoteItems);
                if (count($arrangedQuoteItems) > 0) {
                    foreach ($arrangedQuoteItems as $arrangedQuoteItem) {
                        $newQuoteItems[] = $arrangedQuoteItem;
                    }
                }
                $result = $newQuoteItems;
            }
        }

        return $result;
    }
}
