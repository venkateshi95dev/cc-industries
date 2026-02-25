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

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Ajax call to save 3DSecure response data in session
 *
 * Class Save3DSecureData
 */
class Save3DSecureData implements ActionInterface
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
    private SessionManagerInterface $sessionManager;

    /**
     * @param JsonFactory $jsonFactory
     * @param RequestInterface $request
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        JsonFactory $jsonFactory,
        RequestInterface $request,
        SessionManagerInterface $sessionManager
    ) {

        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Save 3DSecure data in session
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $jsonFactory = $this->jsonFactory->create();
        $response = [
            'success' => false,
        ];

        if (!$this->request->isAjax()) {
            $jsonFactory->setData($response);
            return $jsonFactory;
        }

        $this->sessionManager->unsEbiz3DSecureData();

        /** Request params */
        $requestParams = $this->request->getParams();
        $ebiz3DSecureSessionData = '';

        if ($requestParams) {
            /** API params */
            $ebiz3DSecureSessionData = [
                'CreditCardData' => [
                    'CAVV' => $requestParams['cavv'] ?? null,
                    'XID' => $requestParams['xid'] ?? null,
                    'ECI' => $requestParams['eci'] ?? null,
                    'Pares' => $requestParams['pares'] ?? null
                ],
                'DSTransactionId' => $requestParams['dsTransactionId'] ?? null
            ];
        }

        $this->sessionManager->setEbiz3DSecureData($ebiz3DSecureSessionData);

        $response['success'] = (bool)$ebiz3DSecureSessionData;

        $jsonFactory->setData($response);
        return $jsonFactory;
    }
}
