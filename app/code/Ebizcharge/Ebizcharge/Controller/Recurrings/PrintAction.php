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

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Print action for recurring
 *
 * Class PrintAction
 */
class PrintAction implements AccountInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var TranApi
     */
    protected $tranApi;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var EbizchargeLogger
     */
    protected $ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * PrintAction constructor.
     *
     * @param JsonFactory $resultJsonFactory
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     * @param TranApi $tranApi
     * @param CustomerFactory $customerFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        ManagerInterface $messageManager,
        RequestInterface $request,
        TranApi $tranApi,
        CustomerFactory $customerFactory,
        EbizchargeLogger $ebizchargeLogger
    ) {
        /** @var  resultJsonFactory */
        $this->resultJsonFactory = $resultJsonFactory;
        /** @var  messageManager */
        $this->messageManager = $messageManager;
        /** @var  request */
        $this->request = $request;
        /** @var  tranApi */
        $this->tranApi = $tranApi;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var customerFactory */
        $this->customerFactory = $customerFactory;
    }

    /**
     * Execute Method
     *
     * @return int|ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {

        $transactionParams = $this->request->getParams();
        $printResponse = $this->customerFactory->create()->printEmailTemplate($transactionParams);
        $htmlData = '';

        /** print response */
        if ($printResponse['error'] == false) {
            $htmlData = $printResponse['email_html'];
           // $this->messageManager->addSuccessMessage($printResponse['message']);
        }else{
            $this->messageManager->addErrorMessage($printResponse['message']);
        }
        return $this->resultJsonFactory->create()->setData(
            ['html_data' => $htmlData]
        );
    }
}
