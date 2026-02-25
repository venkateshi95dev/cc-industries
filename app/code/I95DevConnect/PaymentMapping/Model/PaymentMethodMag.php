<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentMapping
 */

namespace I95DevConnect\PaymentMapping\Model;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Config;

/**
 * Payment mapping management class
 */
class PaymentMethodMag
{
    /**
     * @var Config
     */
    public $paymentConfig;
    
    /**
     * @var ScopeConfigInterface
     */
    public $scope;

    /**
     * Payment mapping management constructor
     *
     * @param Config $paymentConfig
     * @param ScopeConfigInterface $scope
     */
    public function __construct(
        Config $paymentConfig,
        ScopeConfigInterface $scope
    ) {
        $this->scope = $scope;
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * Get available payment method
     *
     * @return array
     * @throws LocalizedException
     */
    public function availablePaymentMethods()
    {
        try {
            $codeList = [];
            $methodList = $this->scope->getValue('payment');
            foreach ($methodList as $method => $params) {
                if (isset($params['active']) && $params['active']) {
                    $codeList[] = $method;
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return $codeList;
    }
}
