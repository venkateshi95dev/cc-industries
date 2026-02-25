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

namespace Ebizcharge\Ebizcharge\Controller;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Abstract customer controller
 *
 * Class AbstractCustomerController
 */
abstract class AbstractCustomerController extends \Magento\Customer\Controller\AbstractAccount implements HttpGetActionInterface
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var string
     */
    public $pageTitle = null;

    /**
     * @var Session
     */
    protected $session;

    protected $ebizchargeLogger;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var Redirect
     */
    protected $_redirectModel;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerFactory $customerFactory
     * @param ManagerInterface $messageManager
     * @param Redirect $redirectModel
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context                     $context,
        Session                     $customerSession,
        PageFactory                 $resultPageFactory,
        CustomerRepositoryInterface $customerRepository,
        DataObjectHelper            $dataObjectHelper,
        CustomerFactory             $customerFactory,
        ManagerInterface            $messageManager,
        Redirect                    $redirectModel,
        EbizchargeLogger            $ebizchargeLogger
    )
    {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerRepository = $customerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->ebizchargeLogger = $ebizchargeLogger;
        $this->_customerFactory = $customerFactory;
        $this->_messageManager = $messageManager;
        $this->_redirectModel = $redirectModel;


    }

    /**
     * Forgot customer account information page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        /** @var  $customerId */
        $customerId = $this->session->getCustomerId();
        /** @var $customer */
        $customer = $this->_customerFactory->create()->load($customerId);

        if (!$customer->getDefaultBillingAddress() || !$customer->getDefaultShippingAddress()) {
            $this->_messageManager->addErrorMessage(__(
                "Please first add Default Billing and Shipping Addresses..."
            ));

            return $this->_redirectModel->setPath('customer/address/new/');
        }

        $resultPage->getConfig()->getTitle()->set(__('Account Information'));
        return $resultPage;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    protected function getPageTitle(): ?string
    {
        if ($this->pageTitle === null) {

            /** Logging title to the logger */
            $this->ebizchargeLogger->addCritical(__('Child class ' . get_called_class() .
                ' failed to define static ' . $this->pageTitle . ' property'));
            // phpcs:ignore
            throw new \Exception('Child class ' . get_called_class() . ' failed to define static ' . $this->pageTitle . ' property');
        }
        return $this->pageTitle;
    }


    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    private function isLoggedIn(): bool
    {
        $currentSession = $this->session;
        return $currentSession->isLoggedIn();
    }
}
