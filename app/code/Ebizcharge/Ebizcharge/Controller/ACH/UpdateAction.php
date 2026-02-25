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

namespace Ebizcharge\Ebizcharge\Controller\ACH;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

/**
 * Update ACH account Action
 *
 * Class UpdateAction
 */
class UpdateAction implements AccountInterface
{
    /**
     * @var TranApi
     */
    protected TranApi $tranApi;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var Validator
     */
    protected FormKeyValidator $formKeyValidator;

    /**
     * @var RedirectFactory
     */
    protected RedirectFactory $redirectFactory;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManager;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var Session
     */
    protected Session $_customerSession;

    /**
     * UpdateAction constructor.
     *
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $customerSession
     * @param CustomerFactory $customerFactory
     * @param TranApi $tranApi
     * @param FormKeyValidator $formKeyValidator
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        CustomerFactory $customerFactory,
        TranApi $tranApi,
        Validator $formKeyValidator,
        EbizchargeLogger $ebizchargeLogger
    ) {
        /** @var  messageManager */
        $this->messageManager = $messageManager;
        /** @var  redirectFactory */
        $this->redirectFactory = $redirectFactory;
        /** @var  request */
        $this->request = $request;
        /** @var  scopeConfig */
        $this->scopeConfig = $scopeConfig;
        /** @var  tranApi */
        $this->tranApi = $tranApi;
        /** @var  formKeyValidator */
        $this->formKeyValidator = $formKeyValidator;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _customerSession */
        $this->_customerSession = $customerSession;
    }

    /**
     * Saves the updated payment information.
     *
     * @return ResponseInterface|Redirect|ResultInterface|void
     * @throws LocalizedException
     */
    public function execute()
    {
        /** Validating the results */
        if (!$this->formKeyValidator->validate($this->request)) {
            return $this->redirectFactory->create()->setPath('*/*/listaction');
        }

        /** @var  $cid */
        $cid = $this->request->getParam('cid');
        $customerFactory = $this->_customerFactory->create();
        $customer = $customerFactory->load($cid);
        $customerToken = $customer->getEcCustToken();
        $mid = $this->request->getParam('mid');

        /** if Customer Id
         * and Method Id
         * are given
         */
        if ($cid && $mid) {

            /** @var $bankAccountParams */
            $bankAccountParams = [
                'ach_method_name' => $this->request->getParam('ach_method_name'),
                'ach_route' => $this->request->getParam('ach_route'),
                'ach_type' => $this->request->getParam('ach_type'),
                'ach_holder' => $this->request->getParam('ach_holder'),
                'ach_number' => $this->request->getParam('ach_number'),
                'is_default' => $this->request->getParam('default'),
                'bank_account_method_id' => $this->request->getParam('mid'),
                'customer_token' => $customerToken,
            ];

            /** @var $bankAccountUpdatedResponse */
            $bankAccountUpdatedResponse = $this->_customerFactory->create()
                ->updateCustomerBankAccount($customer, $bankAccountParams);

            /** sending response back to layout renderer */
            if ($bankAccountUpdatedResponse['error'] == false) {
                $this->messageManager->addSuccessMessage($bankAccountUpdatedResponse['message']);
            } else {
                $this->messageManager->addErrorMessage($bankAccountUpdatedResponse['message']);
            }

            return $this->redirectFactory->create()->setPath('*/*/listaction');
        }
    }
}
