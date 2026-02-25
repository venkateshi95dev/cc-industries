<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 * @updatedBy Divya Koona. Removed addPaymentInfo() method as it is not used anywhere
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Reverse;

use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\Generic;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\AbstractOrder;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Config;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Add Payment information while creating an order
 */
class Payment extends AbstractOrder
{
    public const I95EXC = 'i95devApiException';
    public const PAYMENTMETHOD = "paymentMethod";

    /**
     * @var string
     */
    public $paymentData;

    /**
     * @var Config
     */
    public $paymentModelConfig;

    /**
     * @var ScopeConfigInterface
     */
    public $appConfigScopeConfigInterface;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     *
     * @param Data $dataHelper
     * @param Config $paymentModelConfig
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     * @param LoggerInterfaceFactory $logger
     * @param Generic $genericHelper
     * @param Validate $validate
     */
    public function __construct(
        Data $dataHelper,
        Config $paymentModelConfig,
        ScopeConfigInterface $appConfigScopeConfigInterface,
        LoggerInterfaceFactory $logger,
        Generic $genericHelper,
        Validate $validate,
        StoreManagerInterface $storeManager
    ) {
        $this->dataHelper = $dataHelper;
        $this->paymentModelConfig = $paymentModelConfig;
        $this->appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->storeManager = $storeManager;
        parent::__construct(
            $logger,
            $genericHelper,
            $validate
        );
    }

    /**
     * Validate request payment data
     *
     * @param array $stringData
     * @return boolean
     * @throws LocalizedException
     * @author Divya Koona
     */
    public function validateData($stringData)
    {
        $this->stringData = $stringData;
        $this->paymentData = $this->dataHelper->getValueFromArray("payment", $this->stringData);
        $isEditOrder = $this->dataHelper->getValueFromArray("isEditOrder", $stringData);
        if ($isEditOrder === true){
            return true;
        }
        $activeMethods = $this->getActivePaymentMethods();
        if (!empty($this->paymentData)) {
            foreach ($this->paymentData as $payment) {
                if (!in_array($this->dataHelper->getValueFromArray(self::PAYMENTMETHOD, $payment), $activeMethods)) {
                    throw new LocalizedException(
                        __("i95dev_order_022"),
                        null,
                        109
                    );
                }
            }
        }
        return true;
    }

    /**
     * Get Active Payment Methods
     *
     * @return array $methods
     * @throws LocalizedException
     * @author Divya Koona
     */
    public function getActivePaymentMethods()
    {
        $methods = [];
        try {
            $store = null;
            $websiteId = $this->dataHelper->getValueFromArray("websiteIds", $this->stringData);
            if ($websiteId !== null) {
                $store = $this->storeManager->getWebsite($websiteId)->getDefaultStore();
            }
            $payments = $this->paymentModelConfig->getActiveMethods($store);
            foreach ($payments as $paymentCode => $paymentModel) {
                $methods[] = $paymentCode;
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, 'critical');
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
        return $methods;
    }

    /**
     * Prepare payment data to add in order post data
     *
     * @author Divya Koona
     * @return array
     */
    public function setPaymentInformation()
    {
        $payment = $this->paymentData[0];
        $paymentData = [];
        $paymentData['method'] = isset($payment[self::PAYMENTMETHOD]) ? $payment[self::PAYMENTMETHOD] : '';
        $paymentData['po_number'] = isset($payment['poNumber']) ? $payment['poNumber'] : '';
        $paymentData['cc_type'] = isset($payment['ccType']) ? $payment['ccType'] : '';
        $paymentData['cc_number'] = isset($payment['ccNumber']) ? $payment['ccNumber'] : '';
        $paymentData['cc_exp_month'] = isset($payment['ccExpMonth']) ? $payment['ccExpMonth'] : '';
        $paymentData['cc_exp_year'] = isset($payment['ccExpYear']) ? $payment['ccExpYear'] : '';
        $paymentData['cc_cid'] = isset($payment['ccCid']) ? $payment['ccCid'] : '';
        $chkNumber = $this->dataHelper->getValueFromArray("checkNumber", $payment);
        if ($chkNumber) {
            $additional_data = ['additional_information' => [$chkNumber]];
            $paymentData = (array_merge($paymentData, $additional_data));
        }
        return $paymentData;
    }
}
