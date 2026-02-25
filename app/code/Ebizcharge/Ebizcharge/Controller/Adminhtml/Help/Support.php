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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Help;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Order\Invoice;
use Ebizcharge\Ebizcharge\Model\OrderFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Help Support Action class
 *
 * Class Support
 */
class Support extends Action implements HttpGetActionInterface
{
    /**
     * Century Biz Solution
     *
     * @const: CENTURYBIZ_SOLUTION_URL
     */
    public const CENTURYBIZ_SOLUTION_URL = 'https://www.centurybizsolutions.net/';

    /**
     * @var PageFactory
     */
    protected PageFactory $_pageFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var RedirectInterface
     */
    protected RedirectInterface $_redirectInterface;

    /**
     * @var Redirect
     */
    protected Redirect $_resultRedirectFactory;

    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;

    /**
     * @var Invoice
     */
    protected Invoice $_invoiceModel;

    /**
     * Support constructor.
     *
     * @param Context $context
     * @param RedirectInterface $redirectInterface
     * @param Redirect $resultRedirectFactory
     * @param OrderFactory $orderFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param Invoice $invoiceModel
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        RedirectInterface $redirectInterface,
        Redirect $resultRedirectFactory,
        OrderFactory $orderFactory,
        EbizchargeLogger $ebizchargeLogger,
        Invoice $invoiceModel,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);

        /** @var _pageFactory */
        $this->_pageFactory = $pageFactory;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _redirectInterface */
        $this->_redirectInterface = $redirectInterface;
        /** @var _resultRedirectFactory */
        $this->_resultRedirectFactory = $resultRedirectFactory;
        /** @var  _orderFactory */
        $this->_orderFactory = $orderFactory;
        /** @var  _invoiceModel */
        $this->_invoiceModel = $invoiceModel;
    }

    /**
     * Help and contact us page
     *
     * @return ResultInterface|Page
     */
    public function execute()
    {
        /** temp code
           //  $order = $this->_orderFactory->create()->load(3510036);
        //  $syncOrderToEbizcharge =  $this->_orderFactory->create()->saveOrderToEbizcharge($order);
        //   $this->_invoiceModel->createInvoiceToEbizcharge($order->getEntityId());
         //  exit;
        */

        $resultPage = $this->_pageFactory->create();
        $ebizchargeSolutionLink = $this->getRequest()->getParam('link');

        /** ebizcharge Link then  */
        if ($ebizchargeSolutionLink && $ebizchargeSolutionLink == 'ebizcharge') {
            // phpcs:ignore
            echo '<script>window.open("' . self::CENTURYBIZ_SOLUTION_URL . '", "_blank")</script>';
            $redirectUrl = $this->_redirectInterface->getRedirectUrl();
            $resultRedirect = $this->_resultRedirectFactory->setUrl($redirectUrl);
            return $resultPage;
        }
        $resultPage->setActiveMenu('Ebizcharge_Ebizcharge::help_support');
        $resultPage->getConfig()->getTitle()->prepend(__('EBizCharge Help & Contact Us'));

        return $resultPage;
    }
}
