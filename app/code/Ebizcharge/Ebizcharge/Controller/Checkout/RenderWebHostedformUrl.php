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
 * @copyright   Copyright (c) 2022 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      TekHQS https://www.tekhqs.com
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller\Checkout;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\OrderFactory;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Ajax call to save 3DSecure response data in session
 *
 * Class Save3DSecureData
 */
class RenderWebHostedformUrl implements ActionInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @var SessionManagerInterface
     */
    private CheckoutSession $sessionManager;

    /**
     * @var OrderFactory
     */
    private OrderFactory $orderFactory;

    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $ebizchargeLogger;

    /**
     * @param JsonFactory $jsonFactory
     * @param RequestInterface $request
     * @param OrderFactory $orderFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param CheckoutSession $sessionManager
     */
    public function __construct(
        JsonFactory      $jsonFactory,
        RequestInterface $request,
        OrderFactory     $orderFactory,
        EbizchargeLogger $ebizchargeLogger,
        CheckoutSession  $sessionManager
    )
    {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->sessionManager = $sessionManager;
        $this->orderFactory = $orderFactory;
        $this->ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Save 3DSecure data in session
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $jsonFactory = $this->jsonFactory->create();

        $webHostedFormResponse = [
            "error" => true,
            "message" => __("could not prepare web hosted form url"),
            "hosted_pro_url" => "",
            "payload" => [],
            "response" => [
                "ebiz_hosted_pro_url" => "",
                "error_message" => __("could not prepare web hosted form url"),
                "payment_internal_id" => ""
            ],
        ];

        if (!$this->request->isAjax()) {
            $jsonFactory->setData($webHostedFormResponse);
            return $jsonFactory;
        }
        try {
            /** Request params */
            $requestParams = $this->request->getParams();

            if ($requestParams) {
                $storeId = $this->sessionManager->getQuote()->getStore()->getId();
                $webHostedFormResponse = $this->orderFactory->create()->renderCheckoutWebHostedProFormUrl($storeId);

                $webHostedFormResponse["hosted_pro_url"] = isset($webHostedFormResponse["response"]["ebiz_hosted_pro_url"]) ? $webHostedFormResponse["response"]["ebiz_hosted_pro_url"] : "";

                $this->ebizchargeLogger->addInfo(__("Success, Web form loaded from EBizCharge Hub."));
            }
        } catch (Exception $exception) {
            $this->ebizchargeLogger->addCritical(__("Exception occurred during fetching web form hosted url. " . $exception->getMessage()));
            $webHostedFormResponse["message"] = __($exception->getMessage());

        }
        $jsonFactory->setData($webHostedFormResponse);

        return $jsonFactory;
    }
}
