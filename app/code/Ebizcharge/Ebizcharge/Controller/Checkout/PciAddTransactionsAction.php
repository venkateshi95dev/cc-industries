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

use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Model\Config as ConfigModel;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * PCI Compliance Place Order Action class
 *
 * Class PciAddTransactionsAction
 */
class PciAddTransactionsAction extends Action
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var CustomerRepository
     */
    protected $_customerRepository;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var ConfigModel
     */
    protected $_configModel;

    /**
     * @var TranApi
     */
    protected $_soapApiModel;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManagerInterface;

    /**
     * @var Validator
     */
    protected $_formValidator;

    /**
     * @var JsonFactory
     */
    protected $_jsonFactory;

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var PageFactory
     */
    private $_pageFactory;

    /**
     * @param Context $context
     * @param CustomerRepository $customerRepository
     * @param CustomerFactory $customerFactory
     * @param CheckoutSession $checkoutSession
     * @param ConfigModel $configModel
     * @param Session $customerSession
     * @param TranApi $soapApiModel
     * @param StoreManagerInterface $storeManagerInterface
     * @param Validator $formKeyValidator
     * @param JsonFactory $jsonFactory
     * @param PageFactory $pageFactory     */
    public function __construct(
        Context $context,
        CustomerRepository $customerRepository,
        CustomerFactory $customerFactory,
        CheckoutSession $checkoutSession,
        ConfigModel $configModel,
        Session $customerSession,
        TranApi $soapApiModel,
        StoreManagerInterface $storeManagerInterface,
        Validator $formKeyValidator,
        JsonFactory $jsonFactory,
        PageFactory $pageFactory

    ) {
        /** Parent Constructor */
        parent::__construct($context);

        /** @var  _pageFactory */
        $this->_pageFactory = $pageFactory;
        /** @var customerSession */
        $this->customerSession = $customerSession;
        /** @var  _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var  _customerRepository */
        $this->_customerRepository = $customerRepository;
        /** @var  _checkoutSession */
        $this->_checkoutSession = $checkoutSession;
        /** @var  _soapApiModel */
        $this->_soapApiModel = $soapApiModel;
        /** @var  _configModel */
        $this->_configModel = $configModel;
        /** @var  _storeManagerInterface */
        $this->_storeManagerInterface = $storeManagerInterface;
        /** @var  _formValidator */
        $this->_formValidator = $formKeyValidator;
        /** @var  _jsonFactory */
        $this->_jsonFactory = $jsonFactory;

    }

    /**
     * Execute Method
     *
     * Renders the page found in the XML layout file, sets
     * a page title, and sets the active link to EBizCharge.
     *
     * @return ResultInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        /** @var result factory settings $resultPage */
        $jsonFactory = $this->_jsonFactory->create();
        $requestParams = $this->getRequest()->getParams();

        $this->_checkoutSession->setTransactionData($requestParams);
        $this->_checkoutSession->setEnvType(PaymentInterface::PAYMENT_ENV_TYPE_FRONTEND);

       /** @var $transactionResult */
        $jsonFactory->setData($requestParams);

        return $jsonFactory;
    }
}
