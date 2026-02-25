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
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Action\Context $context,
        ProductHelper $productHelper,
        Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context, $productHelper, $escaper, $resultForwardFactory, $resultForwardFactory);
        $productHelper->setSkipSaleableCheck(true);
        $this->escaper = $escaper;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
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
                 ->createOrder();

            //$orderId = $order->getId();
            $orderId = $this->_getSession()->getData("order_id");

            $this->_getSession()->clearStorage();
            $this->messageManager->addSuccessMessage(__('You created the order.'));
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
            $this->messageManager->addExceptionMessage($e, __('Order saving error: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath($path, $pathParams);
    }
}
