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

namespace Ebizcharge\Ebizcharge\Observer\Order;

use Magento\Framework\Exception\LocalizedException;
use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;


/**
 * Order Place After Observer
 *
 * Class OrderPlaceAfter
 */
class OrderPlaceAfter extends AbstractModel implements ObserverInterface
{

    /**
     * Update order status
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);
        if($isEbizChargeActive) {
            try {
                /** Order $order */
                $order = $observer->getEvent()->getOrder();
                $payment = $order->getPayment();

                // Set Order status as per configurations
                $methodCode = $payment->getMethodInstance()->getCode();

                if ($methodCode === PaymentInterface::CODE) {
                    $configStatus = $this->configFactory->create()->getNewOrderStatus();

                    if ($configStatus) {
                        $order->setStatus($configStatus);
                    }
                }

                // Set Surcharge Amount and Percentage if exists in quote
                $quoteId = $order->getQuoteId();
                $quote = $this->_quoteRepository->get($quoteId);
                $surchargeAmount = $quote->getEcSurchargeAmount();
                $order->setEcSurchargeAmount($surchargeAmount);
                $order->setEcSurchargePercentage($quote->getEcSurchargePercentage());
                $order->setEcSurchargeIneligible($quote->getEcSurchargeIneligible());

                if (floatval($surchargeAmount) > 0) {
                    $order->setGrandTotal($order->getGrandTotal() + $surchargeAmount);
                    $order->setBaseGrandTotal($order->getBaseGrandTotal() + $surchargeAmount);
                }


            } catch (\Exception $exception) {
                $this->ebizchargeLogger->addCritical(__("Order totals modification error " . $exception->getMessage()));
            }
        }

        return $this;
    }
}
