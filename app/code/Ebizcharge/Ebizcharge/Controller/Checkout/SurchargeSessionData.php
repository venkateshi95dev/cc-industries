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

use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Ajax call to get surcharge session data
 *
 * Class SurchargeSessionData
 */
class SurchargeSessionData extends Action
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        CheckoutSession $checkoutSession
    ) {
        /** Parent Constructor */
        parent::__construct($context);

        $this->jsonFactory = $jsonFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * To check and calculate surcharge on cart
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $jsonFactory = $this->jsonFactory->create();
        //$requestParams = $this->getRequest()->getParams();

        /** Response object */
        $response = [
            'surchargeAmount' => null,
            'surchargeCaption' => '',
            'surchargePercentage' => null
        ];

        if (!$this->_request->isAjax()) {
            $jsonFactory->setData($response);
            return $jsonFactory;
        }

        /** Surcharge Session Data */
        $sessionData = $this->checkoutSession->getSurchargeData();

        /** Response object */
        $response = [
            'surchargeAmount' => $sessionData['surchargeAmount'] ?? null,
            'surchargeCaption' => $sessionData['surchargeCaption'] ?? '',
            'surchargePercentage' => $sessionData['surchargePercentage'] ?? 0,
            'surchargeAmountWithSign' => $sessionData['surchargeAmountWithSign'] ?? null
        ];

        /** Unset previous surcharge data */
        $this->checkoutSession->unsSurchargeData();

        $jsonFactory->setData($response);
        return $jsonFactory;
    }
}
