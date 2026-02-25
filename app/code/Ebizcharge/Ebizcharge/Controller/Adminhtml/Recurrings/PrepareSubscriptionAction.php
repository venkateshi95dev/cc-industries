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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\OrderFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Prepare Subscriptions Action
 *
 * Class PrepareSubscriptionAction
 */
class PrepareSubscriptionAction extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $_resultJsonFactory;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;
    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;
    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param OrderFactory $orderFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        OrderFactory $orderFactory,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct($context);

        /** @var  resultJsonFactory */
        $this->_resultJsonFactory = $resultJsonFactory;
        /** @var _orderFactory **/
        $this->_orderFactory = $orderFactory;
        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Execute Method
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id');

        if ($customerId) {
            /**
             * Quote Data
             */
            $quoteData = $this->_orderFactory->create()->prepareRecurringQuote($this->getRequest());

            return $this->_resultJsonFactory->create()
                ->setData(['html_data' => $quoteData]);
        }
        return $this->_resultJsonFactory->create()
            ->setData(['html_data' => null]);
    }
}
