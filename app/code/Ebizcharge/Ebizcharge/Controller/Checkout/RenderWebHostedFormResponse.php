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

namespace Ebizcharge\Ebizcharge\Controller\Checkout;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Exception;
use GuzzleHttp\Client;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;

/**
 * Ebiz Web Form Response Action class
 *
 * Class EbizWebFormResponse
 */
class RenderWebHostedFormResponse extends Action implements ActionInterface, HttpGetActionInterface
{


    /**
     * @var Client
     */
    protected Client $httpClient;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context          $context,
        CustomerFactory  $customerFactory,
        EbizchargeLogger $ebizchargeLogger

    )
    {
        parent::__construct($context);

        $this->customerFactory = $customerFactory;
        $this->ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Execute function
     *
     * @return ResponseInterface|ResultInterface|Page
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $callBackHtml = "";
        $httpRequest = $this->getRequest();
        $requestParams = $httpRequest->getParams();

        try {
            $customerFactory = $this->customerFactory->create();
            $callBackHtml .= $customerFactory->authenticateGatewayPaymentResponse($httpRequest);
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addCritical(__("Could not authenticated the payment at gateway. error:" . $ex->getMessage()));
        }
        $this->getResponse()->setBody($callBackHtml);
    }
}
