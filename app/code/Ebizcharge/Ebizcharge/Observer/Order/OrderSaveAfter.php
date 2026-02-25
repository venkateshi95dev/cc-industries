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

use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Order save after observer class
 *
 * Class OrderSaveAfter
 */
class OrderSaveAfter extends AbstractModel implements ObserverInterface
{

    /**
     * Update order status
     *
     * @param Observer $observer
     * @return $this
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if($isEbizChargeActive) {
            /** @var Order $order */
            $order = $observer->getEvent()->getOrder();

            if (!$order->getId()) {
                //order not saved in the database
                return $this;
            }

            // Check if the order exists, is placed through EbizCharge method, and has been just placed
            try {
                $payment = $order->getPayment();
                $method = $payment->getMethodInstance();
                $methodCode = $method->getCode();

                if ($order instanceof Order && $methodCode === PaymentInterface::CODE &&
                    $order->getOrigData('entity_id') === null) {

                    /**
                     * Adding Application data to
                     * EBizCharge Payment HuB
                     *
                     */
                    $orderId = $order->getId();
                    $orderFactory = $this->_orderFactory->create();
                    $order = $orderFactory->load($order->getId());

                    if ($order) {
                        /** @var $syncOrderToEbizcharge */
                        $syncOrderToEbizcharge = $orderFactory->syncOrderToEbizcharge($orderId);
                        $applicationTransactionData = $orderFactory->addApplicationTransactionData($orderId);

                        if ($applicationTransactionData["error"] === false) {
                            $applicationTransactionId = $order->getPayment()
                                ->getEbzcApplicationPaymentRefId();

                            if (!$applicationTransactionId) {
                                $applicationRefNumber =
                                    $applicationTransactionData["response"]["ApplicationTransactionInternalId"];

                                $order->getPayment()
                                    ->setEbzcApplicationPaymentRefId($applicationRefNumber)
                                    ->save();
                            }
                        }
                    }
                }

            } catch (Exception $exception) {
                $this->ebizchargeLogger->addCritical("Could not Sync Application Data to EBizCharge Hub. Error:" .
                    $exception->getMessage());
            }
        }

        return $this;
    }
}
