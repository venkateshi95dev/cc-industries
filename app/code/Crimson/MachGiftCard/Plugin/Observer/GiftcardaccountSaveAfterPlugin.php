<?php
 /**
 * @namespace   Crimson
 * @module      MachGiftCard
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/8/2019 11:16 AM
 * @brief       MACH will update data on giftcard account frequently, this will prevent many unnecessary saves.
 */

namespace Crimson\MachGiftCard\Plugin\Observer;

use Crimson\MachGiftCard\Model\GiftCardAccountRepository;
use Magento\Framework\Event\Observer;
use Magento\GiftCardAccount\Model\Giftcardaccount;
use Magento\GiftCardAccount\Observer\GiftcardaccountSaveAfter;

/**
 * Class GiftcardaccountSaveAfterPlugin
 * @package Crimson\MachGiftCard\Plugin\Observer
 */
class GiftcardaccountSaveAfterPlugin
{
    /**
     * @param GiftcardaccountSaveAfter $subject
     * @param callable $proceed
     * @param Observer $observer
     * @return mixed
     */
    public function aroundExecute(
        GiftcardaccountSaveAfter $subject,
        callable $proceed,
        Observer $observer
    ) {
        /** @var Giftcardaccount $giftCardAccount */
        $giftCardAccount = $observer->getEvent()->getGiftcardaccount();
        if (!$giftCardAccount->getIsNew()) {
            $giftCardAccount->unsHistoryAction();
        }

        return $proceed($observer);
    }
}
