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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Sales;

use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote as BackendQuote;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * Calculate Surcharge via ajax call
 *
 * Class CalculateSurcharge
 */
class CalculateSurcharge extends Action
{
    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @var TranApi
     */
    private TranApi $soapApiModel;

    /**
     * @var PriceHelper
     */
    private PriceHelper $priceHelper;

    /**
     * @var BackendQuote
     */
    private BackendQuote $backendQuote;

    /**
     * Main Constructor Factory
     *
     * @param Context $context
     * @param TranApi $soapApiModel
     * @param JsonFactory $jsonFactory
     * @param PriceHelper $priceHelper
     * @param BackendQuote $backendQuote
     */
    public function __construct(
        Context $context,
        TranApi $soapApiModel,
        JsonFactory $jsonFactory,
        PriceHelper $priceHelper,
        BackendQuote $backendQuote
    ) {
        parent::__construct($context);

        /** @var $jsonFactory */
        $this->jsonFactory = $jsonFactory;
        /** @var $priceHelper */
        $this->priceHelper = $priceHelper;
        /** @var $backendQuote */
        $this->backendQuote = $backendQuote;
        /** @var $soapApiModel */
        $this->soapApiModel = $soapApiModel;

    }

    /**
     * Execute JSON Response
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $jsonFactory = $this->jsonFactory->create();

        /** Response Data */
        $response = SurchargeInterface::DEFAULT_AJAX_CALCULATE_SURCHARGE_RESPONSE;

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $jsonFactory->setData($response);
        }

        /** Unset previous surcharge data */
        $this->_session->unsSurchargeSessionData();
        $storeId = $this->soapApiModel->getStoreId();

        /** Request params */
        $requestParams = $this->getRequest()->getParams();

        /** When Add New Card is selected with empty fields */
        if (isset($requestParams['emptyCall']) && $requestParams['emptyCall']) {

            $surchargeSettings = $this->soapApiModel->getSurchargeSettings($storeId);

            $response[SurchargeInterface::EBIZ_SURCHARGE_ENABLED] =
                $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_ENABLED] ?? false;
            $response[SurchargeInterface::EBIZ_SURCHARGE_FOR_ZIP] = true;
            $response[SurchargeInterface::EBIZ_SURCHARGE_FOR_PAYMENT_METHOD] = true;
            $response[SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE] =
                $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE] ?? 0;
            $response[SurchargeInterface::EBIZ_SURCHARGE_AMOUNT] = 0;
            $response[SurchargeInterface::EBIZ_SURCHARGE_CAPTION] =
                $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_CAPTION] ?? '';

            $this->_session->setSurchargeSessionData($response);
            $jsonFactory->setData($response);
            return $jsonFactory;
        }

        /** API params */
        $params = [
            'amount' => $this->backendQuote->getQuote()->getGrandTotal(),
            'cardNumber' => $requestParams['cardNumber'] ?? null,
            'cardZipCode' => $requestParams['cardZipCode'] ?? null,
            'customerInternalId' => $requestParams['customerInternalId'] ?? null,
            'paymentMethodId' => $requestParams['paymentMethodId'] ?? null
        ];

        /** Calculate surcharge response */
        $response = $this->soapApiModel->calculateSurchargeAmount($params);

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
            ): null;

            /** Surcharge Session Data */
            $response[SurchargeInterface::EBIZ_SURCHARGE_AMOUNT] = $surchargeAmount;
            $response[SurchargeInterface::EBIZ_SURCHARGE_AMOUNT_WITH_SIGN] = $surchargeAmountWithSign;
            $this->_session->setSurchargeSessionData($response);
        }

        $jsonFactory->setData($response);
        return $jsonFactory;
    }
}
