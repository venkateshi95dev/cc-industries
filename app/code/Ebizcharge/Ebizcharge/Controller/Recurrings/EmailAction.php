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

namespace Ebizcharge\Ebizcharge\Controller\Recurrings;

use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Deletes the customer's saved payment method.
 *
 * Class EmailAction
 */
class EmailAction implements AccountInterface
{
    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Main Constructor
     *
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param TranApi $tranApi
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        TranApi $tranApi
    ) {
        /** @var  resultJsonFactory */
        $this->resultJsonFactory = $resultJsonFactory;
        /** @var  request */
        $this->request = $request;
        /** @var  tranApi */
        $this->tranApi = $tranApi;
    }

    /**
     * Execute Method
     *
     * @return int|ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $transactionRefNum = $this->request->getParam('tid');
        $receiptRefNum = $this->request->getParam('rid');
        $emailAddress = $this->request->getParam('email');

        if ($transactionRefNum && $receiptRefNum && $emailAddress) {
            $params = [
                'securityToken' => $this->tranApi->getUeSecurityToken(),
                'transactionRefNum' => $transactionRefNum,
                'receiptRefNum' => $receiptRefNum,
                'emailAddress' => $emailAddress,
            ];

            $emailReceipt = $this->tranApi->getClient()->EmailReceipt($params);
            $getEmailReceiptResult = $emailReceipt->EmailReceiptResult;

            return $this->resultJsonFactory->create()->setData(['html_data' => $getEmailReceiptResult->StatusCode]);
        }

        return 0;
    }
}
