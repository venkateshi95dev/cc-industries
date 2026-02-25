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

namespace Ebizcharge\Ebizcharge\Model\Adapter;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\Payment;
use Exception;
use Magento\Framework\Exception\LocalizedException;

/**
 * Ebizcharge Adapter Model class
 *
 * Class EbizchargeAdapter
 */
class EbizchargeAdapter
{
    /**
     * @var bool
     */
    protected bool $_canSaveCc = true;

    /**
     * @var Payment
     */
    protected Payment $_ebizchargePayment;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var Config
     */
    protected Config $_ebizchargeConfigModel;

    /**
     * EbizchargeAdapter constructor.
     * @param Payment $ebizchargePayment
     * @param Config $ebizchargeConfigModel
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Payment $ebizchargePayment,
        Config $ebizchargeConfigModel,
        EbizchargeLogger $ebizchargeLogger
    ) {
        /** @var  ebizchargePayment */
        $this->_ebizchargePayment = $ebizchargePayment;
        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var  _ebizchargeConfigModel */
        $this->_ebizchargeConfigModel = $ebizchargeConfigModel;
    }

    /**
     * Capture Method
     *
     * @param array $attributes
     * @return Payment|false
     * @throws Exception
     */
    public function capture($attributes)
    {
        $storeId = $this->_ebizchargeConfigModel->getStoreId();
        if(is_array($attributes)){
            $storeId = isset($attributes["store_id"]) ? $attributes["store_id"] : $storeId;
        }
        $isEbizActive = $this->_ebizchargeConfigModel->isActive($storeId);
        if (!$isEbizActive) {
            throw new LocalizedException(__("EBizCharge payment method is disabled. Please enable it and try again."));
        }else {
            $amount = $attributes['amount'];
            return $this->_ebizchargePayment->capture($attributes['payment'], $amount);
        }
    }

    /**
     * Sale Method
     *
     * @param array $attributes
     * @return Payment|false
     * @throws Exception
     */
    public function sale(array $attributes)
    {
        $storeId = $this->_ebizchargeConfigModel->getStoreId();
        if(is_array($attributes)){
            $storeId = isset($attributes["store_id"]) ? $attributes["store_id"] : $storeId;
        }
        $isEbizActive = $this->_ebizchargeConfigModel->isActive($storeId);
        if (!$isEbizActive) {
            throw new LocalizedException(__("EBizCharge payment method is disabled. Please enable it and try again."));
        }else {
            $amount = $attributes['amount'];
            return $this->_ebizchargePayment->capture($attributes['payment'], $amount);
        }
    }

    /**
     * Authorize Command
     *
     * @param array $attributes
     * @return Payment|false
     * @throws LocalizedException
     */
    public function authorize(array $attributes)
    {
        $storeId = $this->_ebizchargeConfigModel->getStoreId();
        if(is_array($attributes)){
            $storeId = isset($attributes["store_id"]) ? $attributes["store_id"] : $storeId;
        }
        $isEbizActive = $this->_ebizchargeConfigModel->isActive($storeId);
        if (!$isEbizActive) {
            throw new LocalizedException(__("EBizCharge payment method is disabled. Please enable it and try again."));
        }else {
            $amount = $attributes['amount'];
            return $this->_ebizchargePayment->authorize($attributes['payment'], $amount);
        }
    }

    /**
     * Refund command
     *
     * @param array $attributes
     * @return Payment
     * @throws LocalizedException
     */
    public function refund(array $attributes)
    {
        $storeId = $this->_ebizchargeConfigModel->getStoreId();
        if(is_array($attributes)){
            $storeId = isset($attributes["store_id"]) ? $attributes["store_id"] : $storeId;
        }
        $isEbizActive = $this->_ebizchargeConfigModel->isActive($storeId);
        if (!$isEbizActive) {
            throw new LocalizedException(__("EBizCharge payment method is disabled. Please enable it and try again."));
        }else {
            $amount = $attributes['amount'];
            return $this->_ebizchargePayment->refund($attributes['payment'], $amount);
        }
    }

    /**
     * Void command
     *
     * @param array $attributes
     * @return Payment
     * @throws LocalizedException
     */
    public function void(array $attributes)
    {
        $storeId = $this->_ebizchargeConfigModel->getStoreId();
        if(is_array($attributes)){
            $storeId = isset($attributes["store_id"]) ? $attributes["store_id"] : $storeId;
        }
        $isEbizActive = $this->_ebizchargeConfigModel->isActive($storeId);
        if (!$isEbizActive) {
            throw new LocalizedException(__("EBizCharge payment method is disabled. Please enable it and try again."));
        }else {
            return $this->_ebizchargePayment->void($attributes['payment']);
        }
    }

    /**
     * Cancel command
     *
     * @param array $attributes
     * @return Payment
     * @throws LocalizedException
     */
    public function cancel(array $attributes)
    {
        $storeId = $this->_ebizchargeConfigModel->getStoreId();
        if(is_array($attributes)){
            $storeId = isset($attributes["store_id"]) ? $attributes["store_id"] : $storeId;
        }
        $isEbizActive = $this->_ebizchargeConfigModel->isActive($storeId);
        if (!$isEbizActive) {
            throw new LocalizedException(__("EBizCharge payment method is disabled. Please enable it and try again."));
        }else {
            return $this->_ebizchargePayment->cancel($attributes['payment']);
        }
    }
}
