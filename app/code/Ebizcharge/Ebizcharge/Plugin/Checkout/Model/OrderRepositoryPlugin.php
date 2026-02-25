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

namespace Ebizcharge\Ebizcharge\Plugin\Checkout\Model;

use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Exception;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderRepositoryPlugin extends AbstractModel
{
    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface[]
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function beforeSave(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ): array {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);
        if($isEbizChargeActive) {
            if (class_exists(\Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\SaveHandler::class)) {
                if ($order->getExtensionAttributes() && $order->getExtensionAttributes()->getAmGiftcardOrder()) {
                    $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();
                    $quoteId = $order->getQuoteId();
                    $quote = $this->_quoteRepository->get($quoteId);
                    $gCardQuote = $quote->getExtensionAttributes()->getAmGiftcardQuote();
                    $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();

                    $this->ebizchargeLogger->addCritical(__("From quote repository plugin: "), 100, $gCardQuote->debug());
                    $this->ebizchargeLogger->addCritical(__("From order repository plugin: "), 100, $gCardOrder->debug());
                }
            }
        }
        return [$order];
    }
}
