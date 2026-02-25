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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Order\Edit;

use Magento\Sales\Controller\Adminhtml\Order\Create\Save as BaseSave;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\PaymentException;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\Escaper;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Order as EbizOrderModel;


class Save extends BaseSave
{

    /**
     * Indicates how to process post data
     */
    private const ACTION_SAVE = 'save';
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var OrderFactory
     */
    protected EbizOrderModel $ebizOrderModel;

    /**
     * @var ProductHelper
     */
    protected ProductHelper $productHelper;

    /**
     * @param Action\Context $context
     * @param ProductHelper $productHelper
     * @param Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param EbizOrderModel $ebizOrderModel
     */
    public function __construct(
        Action\Context   $context,
        ProductHelper    $productHelper,
        Escaper          $escaper,
        PageFactory      $resultPageFactory,
        ForwardFactory   $resultForwardFactory,
        EbizchargeLogger $ebizchargeLogger,
        EbizOrderModel   $ebizOrderModel
    )
    {
        /** parent construct */
        parent::__construct($context, $productHelper, $escaper, $resultPageFactory, $resultForwardFactory);


        $productHelper->setSkipSaleableCheck(true);
        /** @var  escaper */
        $this->escaper = $escaper;
        /** @var  resultPageFactory */
        $this->resultPageFactory = $resultPageFactory;
        /** @var  resultForwardFactory */
        $this->resultForwardFactory = $resultForwardFactory;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var  ebizOrderModel */
        $this->ebizOrderModel = $ebizOrderModel;
    }


    /**
     * Saving quote and create order
     *
     * @return \Magento\Framework\Controller\ResultInterface
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $path = 'sales/*/';
        $pathParams = [];

        try {
            // check if the creation of a new customer is allowed
            if (!$this->_authorization->isAllowed('Magento_Customer::manage')
                && !$this->_getSession()->getCustomerId()
                && !$this->_getSession()->getQuote()->getCustomerIsGuest()
            ) {
                return $this->resultForwardFactory->create()->forward('denied');
            }
            $this->_getOrderCreateModel()->getQuote()->setCustomerId($this->_getSession()->getCustomerId());
            $this->_processActionData('save');
            $paymentData = $this->getRequest()->getPost('payment');
            $orderEntityId = $this->_getSession()->getData("order_id") ?? "";

            if ($paymentData) {
                $paymentData['checks'] = [
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_INTERNAL,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL,
                ];
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            }

            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($this->getRequest()->getPost('order'))
            ;


            /** @var process edit order $isOrderEditVoid */
            $isOrderEditVoidYes = $this->ebizOrderModel->processEditOrderQuickSale($orderEntityId, $this->getRequest()->getPost(),  $this->_getOrderCreateModel());

            if ($isOrderEditVoidYes) {

/**
                $order = $this->_getOrderCreateModel()
                    ->setIsValidate(true)
                    ->importPostData($this->getRequest()->getPost('order'))
                    ->createOrder();
*/
                $order->createOrder();
                $orderId = $order->getId();
                $this->messageManager->addSuccessMessage(__('Your order has been edited successfully.'));
            } else {
                $orderId = $this->_getSession()->getOrder()->getEntityId();
                $this->messageManager->addSuccessMessage(__('Your order has been edited successfully.'));
            }

            $this->_getSession()->clearStorage();

            if ($this->_authorization->isAllowed('Magento_Sales::actions_view')) {
                $pathParams = ['order_id' => $orderId];
                $path = 'sales/order/view';
            } else {
                $path = 'sales/order/index';
            }
        } catch (PaymentException $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addErrorMessage($message);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // customer can be created before place order flow is completed and should be stored in current session
            $this->_getSession()->setCustomerId((int)$this->_getSession()->getQuote()->getCustomerId());
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addErrorMessage($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Order editing error: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath($path, $pathParams);
    }
}
