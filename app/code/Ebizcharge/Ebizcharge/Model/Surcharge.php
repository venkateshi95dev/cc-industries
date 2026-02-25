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

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\TranApiFactory as SoapApiFactory;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * Surcharge model class
 *
 * Class Surcharge
 */
class Surcharge implements SurchargeInterface
{
    /**
     * @var BackendSession
     */
    protected BackendSession $_backendSession;
    /**
     * @var TranApiFactory
     */
    protected SoapApiFactory $soapApiFactory;
    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;
    /**
     * @var Session
     */
    protected Session $checkoutSession;
    /**
     * @var PriceHelper
     */
    protected PriceHelper $priceHelper;
    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;


    /**
     * Main Constructor
     *
     * @param BackendSession $backendSession
     * @param TranApiFactory $soapApiFactory
     * @param CustomerFactory $customerFactory
     * @param Session $checkoutSession
     * @param PriceHelper $priceHelper
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        BackendSession   $backendSession,
        SoapApiFactory   $soapApiFactory,
        CustomerFactory  $customerFactory,
        Session          $checkoutSession,
        PriceHelper      $priceHelper,
        EbizchargeLogger $ebizchargeLogger,

    )
    {
        /** @var $_backendSession */
        $this->_backendSession = $backendSession;
        $this->soapApiFactory = $soapApiFactory;
        $this->customerFactory = $customerFactory;
        $this->checkoutSession = $checkoutSession;
        $this->priceHelper = $priceHelper;
        $this->ebizchargeLogger = $ebizchargeLogger;

    }

    /**
     * Get Surcharge Session Data
     *
     * @return array
     */
    public function getSurchargeSessionData(): array
    {

        $sessionData = $this->_backendSession->getSurchargeSessionData();
        $this->_backendSession->unsSurchargeSessionData();
        return $sessionData ?: [];
    }

    /**
     * @return bool
     */
    public function isSurchargeEnabled(mixed $storeId = "0"): bool
    {
        $storeId = $storeId ?? $this->soapApiFactory->create()->getStoreId();
        $surchargeSettings = $this->getSurchargeSettings($storeId);
        $isSurchargeEnabled = false;
        if ($surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_ENABLED]) {
            $isSurchargeEnabled = true;
        }
        return $isSurchargeEnabled;
    }

    /**
     * @return array
     */
    public function getSurchargeSettings(mixed $storeId = "0"): array
    {
        $surchargeSettingsResponse = [
            SurchargeInterface::EBIZ_SURCHARGE_ENABLED => false,
            SurchargeInterface::EBIZ_SURCHARGE_FOR_ZIP => false,
            SurchargeInterface::EBIZ_SURCHARGE_FOR_PAYMENT_METHOD => 0,
            SurchargeInterface::EBIZ_SURCHARGE_AMOUNT => 0,
            SurchargeInterface::EBIZ_SURCHARGE_CAPTION => ''
        ];
        $storeId = $storeId ?? $this->soapApiFactory->create()->getStoreId();
        /**
         * gt Surcharge settings
         */
        $surchargeSettings = $this->soapApiFactory->create()->getSurchargeSettings($storeId);

        if (is_array($surchargeSettings) && count($surchargeSettings) > 0) {

            $surchargeSettingsResponse[SurchargeInterface::EBIZ_SURCHARGE_ENABLED] =
                $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_ENABLED] ?? false;
            $surchargeSettingsResponse[SurchargeInterface::EBIZ_SURCHARGE_FOR_ZIP] = true;
            $surchargeSettingsResponse[SurchargeInterface::EBIZ_SURCHARGE_FOR_PAYMENT_METHOD] = true;
            $surchargeSettingsResponse[SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE] =
                $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE] ?? 0;
            $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_AMOUNT] = 0;

            $surchargeSettingsResponse[SurchargeInterface::EBIZ_SURCHARGE_CAPTION] =
                $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_CAPTION] ?? '';
        }
        return $surchargeSettingsResponse;

    }

    /**
     * @param array $surchargeParams
     * @return array
     * @throws NoSuchEntityException
     */
    public function calculateSurcharge(array $surchargeParams = []): array
    {
        $surchargeResp = [];

        /** Calculate surcharge response */
        $response = $this->soapApiFactory->create()->calculateSurchargeAmount($surchargeParams);

        /** If surcharge is enabled then set data in session */
        if (isset($response[SurchargeInterface::EBIZ_SURCHARGE_ENABLED]) &&
            $response[SurchargeInterface::EBIZ_SURCHARGE_ENABLED]) {
            /** Surcharge Data */
            $surchargeAmount = $response[SurchargeInterface::EBIZ_SURCHARGE_AMOUNT] ?? null;
            $ineligible = $response[SurchargeInterface::EBIZ_SURCHARGE_INELIGIBLE] ?? false;
            $surchargeAmount = !$ineligible ? $surchargeAmount : 0;
            $surchargeAmountWithSign = $surchargeAmount !== null ? $this->priceHelper->currency(
                $surchargeAmount,
                true,
                false
            ) : null;

            /** Surcharge Session Data */
            $surchargeResp[SurchargeInterface::EBIZ_SURCHARGE_AMOUNT] = $surchargeAmount;
            $surchargeResp[SurchargeInterface::EBIZ_SURCHARGE_AMOUNT_WITH_SIGN] = $surchargeAmountWithSign;

        }
        return $surchargeResp;
    }


}
