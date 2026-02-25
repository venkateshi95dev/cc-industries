<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentMapping
 */

namespace I95DevConnect\PaymentMapping\Plugin;

use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Create;
use I95DevConnect\PaymentMapping\Api\PaymentMappingManagementInterfaceFactory;
use I95DevConnect\PaymentMapping\Helper\Data;
use I95DevConnect\MessageQueue\Helper\Data as MqHelper;
use Magento\Framework\Exception\LocalizedException;

/**
 * Plugin class responsible for changing the erp payment method to magento payment method
 */
class PaymentMethodReverse
{
    /**
     * @var PaymentMappingManagementInterfaceFactory
     */
    public $paymentMgm;
    /**
     * @var Data
     */
    public $helper;
    public const PAYMENT_METHOD = "paymentMethod";

    /**
     * payment method forward plugin class constructor
     * @param PaymentMappingManagementInterfaceFactory $paymentMgm
     * @param Data $helper
     */
    public function __construct(
        PaymentMappingManagementInterfaceFactory $paymentMgm,
        Data $helper
    ) {
        $this->paymentMgm = $paymentMgm;
        $this->helper = $helper;
    }

    /**
     * Added after plugin to replace the payment method name
     *
     * @param Create $subject
     * @param string $payments
     * @return string
     * @throws LocalizedException
     */
    public function afterGetPaymentMethods(Create $subject, $payments) // NOSONAR
    {
        try {
            if ($this->helper->isEnabled() && !empty($payments)) {
                foreach ($payments as $key => $payment) {
                    if (isset($payment[self::PAYMENT_METHOD]) && !empty($payment[self::PAYMENT_METHOD])) {
                        $mappedMethod = $this->paymentMgm->create()->getByErpCode($payment[self::PAYMENT_METHOD]);
                        if ($mappedMethod) {
                            $payments[$key][self::PAYMENT_METHOD] = $mappedMethod;
                        } else {
                            throw new LocalizedException(
                                __("(%1) - Matched_magento_payment_method_not_found", $payment[self::PAYMENT_METHOD])
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
