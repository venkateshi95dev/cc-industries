<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentMapping
 */

namespace I95DevConnect\PaymentMapping\Plugin;

use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward\Payment\PaymentInfo;
use I95DevConnect\PaymentMapping\Api\PaymentMappingManagementInterfaceFactory;
use I95DevConnect\PaymentMapping\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

/**
 * Plugin class responsible for changing the magento payment method to erp payment method
 */
class PaymentMethodForward
{
    /**
     * @var PaymentMappingManagementInterfaceFactory
     */
    public $paymentMgmt;
    /**
     * @var Data
     */
    public $helper;

    public const PAYMENT_METHOD = "paymentMethod";

    /**
     * Payment method forward plugin class constructor
     *
     * @param PaymentMappingManagementInterfaceFactory $paymentMgmt
     * @param Data $helper
     */
    public function __construct(
        PaymentMappingManagementInterfaceFactory $paymentMgmt,
        Data $helper
    ) {
        $this->paymentMgmt = $paymentMgmt;
        $this->helper = $helper;
    }

    /**
     * Added after plugin to replace the payment method name
     *
     * @param PaymentInfo $subject
     * @param string $payments
     * @return string
     * @throws LocalizedException
     */
    public function afterGetOrderPayment(PaymentInfo $subject, $payments) //NOSONAR
    {
        try {
            if ($this->helper->isEnabled() && !empty($payments)) {
                foreach ($payments as $key => $payment) {
                    if (isset($payment[self::PAYMENT_METHOD]) && !empty($payment[self::PAYMENT_METHOD])) {
                        $mappedMethod = $this->paymentMgmt->create()->getByMagentoCode($payment[self::PAYMENT_METHOD]);
                        if ($mappedMethod) {
                            $payments[$key][self::PAYMENT_METHOD] = $mappedMethod;
                        } else {
                            throw new LocalizedException(
                                __("(%1) - Matched_erp_payment_method_not_found", $payment[self::PAYMENT_METHOD])
                            );
                        }
                    }
                }
            }
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return $payments;
    }
}
