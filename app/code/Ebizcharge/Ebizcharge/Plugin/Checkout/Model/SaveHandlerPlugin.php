<?php

/**
 * Century Business Solutions.
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
 *
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 *
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Plugin\Checkout\Model;

use Amasty\GiftCardAccount\Api\GiftCardAccountRepositoryInterface;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\SaveHandler;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Exception;
use Magento\Framework\Exception\PaymentException;
use Magento\Sales\Api\Data\OrderInterface;

class SaveHandlerPlugin extends AbstractModel
{
    /**
     * Before plugin for saveAttributes.
     *
     * @throws Exception
     */
    public function beforeSaveAttributes(
        SaveHandler $amastySaveHandler,
        OrderInterface $order
    ): array {

        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if($isEbizChargeActive) {
            if (!$order->getExtensionAttributes() || !$order->getExtensionAttributes()->getAmGiftcardOrder()) {
                return [$order];
            }
            if (class_exists(GiftCardAccountRepositoryInterface::class)) {
                try {
                    $extension = $order->getExtensionAttributes();
                    $quote = $this->checkoutSession->getQuote();

                    $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();
                    $gCardOrder->setOrderId((int)$order->getId());
                    $giftCards = $gCardOrder->getGiftCards();
                    $giftCardBaseAmount = $giftCardAmount = 0;
                    $orderAppliedAccounts = [
                        'account_id' => $quote->getCustomerId() ?? '0',
                    ];

                    if (count($giftCards) > 0) {
                        foreach ($giftCards as $giftCard) {
                            $giftCardId = isset($giftCard['id']) ? $giftCard['id'] : 0;
                            $giftCardBalance = 0;
                            $giftCardAccount = $this->giftCardAccountRepositoryInterface->getById($giftCardId);
                            $giftCardBaseAmount += isset($giftCard['b_amount']) ? $giftCard['b_amount'] : 0;
                            $giftCardAmount += isset($giftCard['amount']) ? $giftCard['amount'] : 0;
                            if ($giftCardAccount->getAccountId()) {
                                $giftCardInitAmount = (float)$giftCardAccount->getInitialValue();
                                $giftCardBalance = $giftCardInitAmount - (float)$giftCardBaseAmount;
                                $giftCardAccount->setCurrentValue($giftCardBalance);
                                if ($giftCardBalance <= 0) {
                                    $giftCardAccount->setStatus(AccountStatus::STATUS_USED);
                                }
                                $giftCardAccount->save();
                            }
                        }
                    }
                    $gCardOrder->setGiftAmount($giftCardAmount);
                    $gCardOrder->setBaseGiftAmount($giftCardBaseAmount);
                    $gCardOrderData = [
                        'gift_cards' => $giftCards,
                        'order_id' => (int)$order->getId(),
                        'gift_card_base_amount' => $giftCardBaseAmount,
                        'gift_card_amount' => $giftCardBaseAmount,
                        'gift_amount' => $giftCardAmount,
                        'base_gift_amount' => $giftCardBaseAmount,
                        'invoice_gift_amount' => $giftCardAmount,
                        'base_invoice_gift_amount' => $giftCardAmount,
                    ];
                    $gCardOrder->setData($gCardOrderData);
                    $extension->setAmGiftcardOrder($gCardOrder);
                    $order->setExtensionAttributes($extension);

                    $this->ebizchargeLogger->addInfo(__('Gift Card Order detail. ', 100, $gCardOrderData));
                } catch (PaymentException $paymentException) {
                    $this->ebizchargeLogger->addCritical(
                        __('Exception occurred save gift card accounts. ' . $paymentException->getMessage())
                    );
                }
            }
        }

        return [$order];
    }
}
