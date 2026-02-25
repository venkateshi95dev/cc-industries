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

namespace Ebizcharge\Ebizcharge\Observer;

use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\Data;
use Ebizcharge\Ebizcharge\Model\OrderFactory;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Observe and upload order to EConnect automatically
 *
 * Class Addorder
 */
class Addorder implements ObserverInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var Data
     */
    private $dataClass;

    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $ebizchargeLogger;

    /**
     * @var OrderFactory
     */
    private OrderFactory $orderFactory;
    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $sessionManagerInterface;

    /**
     * @param Config $config
     * @param Data $dataClass
     * @param SessionManagerInterface $sessionManagerInterface
     * @param EbizchargeLogger $ebizchargeLogger
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Config                  $config,
        Data                    $dataClass,
        SessionManagerInterface $sessionManagerInterface,
        EbizchargeLogger        $ebizchargeLogger,
        OrderFactory            $orderFactory
    ) {
        /** @var  dataClass */
        $this->dataClass = $dataClass;
        /** @var  config */
        $this->config = $config;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var orderFactory */
        $this->orderFactory = $orderFactory;
        /** @var  sessionManagerInterface */
        $this->sessionManagerInterface = $sessionManagerInterface;
    }

    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     * @return Addorder|void
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isEbizchargeActive() || !$this->config->isUplaodOrdersEnabled()) {
            /** Ebizcharge Payment Method is not active logging to the logger */
            $this->ebizchargeLogger->addError(
                __('EBizCharge module is not active in system configuration')
            );

            return $this;
        }

        try {
            // To add Application Payment Reference ID to payment
            $order = $observer->getEvent()->getData('order');
            $payment = $order->getPayment();

            if($payment) {
                $method = $payment->getMethodInstance();
                $methodCode = $method->getCode();

                /** @var $orderData */
                $orderData = $observer->getEvent()->getData('order');
                $orderNumber = $orderData->getData('increment_id') ? $orderData->getData('increment_id') : '*';

                /** @var  $orderId */
                $orderId = $orderData->getEntityId();

                if ($methodCode === PaymentInterface::CODE) {

                    if (!empty($orderData)) {
                        $this->ebizchargeLogger->addInfo('Start syncing order #: ' . $orderNumber .
                            ' to EBizCharge Hub');

                        /** @var $syncOrderToEbizcharge */
                        $syncOrderToEbizcharge = $this->orderFactory->create()->syncOrderToEbizcharge($orderId);

                        $orderId = $order->getId();
                        $applicationTransactionData = $this->orderFactory->create()
                            ->addApplicationTransactionData($orderId);
                        if ($applicationTransactionData['error'] === false) {
                            $applicationTransactionId = $order->getPayment()->getEbzcApplicationPaymentRefId();

                            if (!$applicationTransactionId) {
                                $applicationRefNumber =
                                    $applicationTransactionData['response']['ApplicationTransactionInternalId'];

                                $payment->setEbzcApplicationPaymentRefId($applicationRefNumber)
                                    ->save();
                                /** @var $syncOrderToEbizcharge */ //update sales order to ebizcharge
                                $syncOrderToEbizcharge = $this->orderFactory->create()->syncOrderToEbizcharge($orderId);
                            }
                        }

                    }
                }
            }
        } catch (Exception $e) {
            /** Logging to the logger the exception */
            $this->ebizchargeLogger->addCritical(__('Exception occurred during syncing Order to EBizCharge Error: ' . $e->getMessage()));
        }
    }
}
