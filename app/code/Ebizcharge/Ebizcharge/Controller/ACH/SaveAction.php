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
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Controller\Address;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\View\Result\PageFactory;

/**
 * Save ACH Bank Action
 *
 * Class SaveAction
 */
class SaveAction extends Address
{
    /**
     * @var TranApi
     */
    protected TranApi $_tran;

    /**
     * Default Method
     *
     * @var mixed
     */
    protected mixed $isDefaultMethod;

    /**
     * @var RegionCollectionFactory
     */
    protected RegionCollectionFactory $regionCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * SaveAction constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AddressInterfaceFactory $addressDataFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param Context $context
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataProcessor
     * @param FormFactory $formFactory
     * @param FormKeyValidator $formKeyValidator
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param Session $customerSession
     * @param TranApi $tranApi
     * @param CustomerFactory $customerFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AddressInterfaceFactory $addressDataFactory,
        AddressRepositoryInterface $addressRepository,
        Context $context,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataProcessor,
        FormFactory $formFactory,
        FormKeyValidator $formKeyValidator,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        RegionCollectionFactory $regionCollectionFactory,
        RegionInterfaceFactory $regionDataFactory,
        Session $customerSession,
        TranApi $tranApi,
        CustomerFactory $customerFactory,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $formKeyValidator,
            $formFactory,
            $addressRepository,
            $addressDataFactory,
            $regionDataFactory,
            $dataProcessor,
            $dataObjectHelper,
            $resultForwardFactory,
            $resultPageFactory
        );

        /** @var  _tran */
        $this->_tran = $tranApi;
        /** @var  regionCollectionFactory */
        $this->regionCollectionFactory = $regionCollectionFactory;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var  searchCriteriaBuilder */
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Execute Method
     *
     * Adds the new payment method to the customer's account, and
     * redirects the user to the "Manage My Payment Methods" page.
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var  $achListActionUrl */
        $achListActionUrl = '*/*/listaction';

        /** validate */
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath($achListActionUrl);
        }

        /** @var $customer */
        $customer = $this->_customerSession->getCustomer();

        /** @var $achBankAccountParams */
        $achBankAccountParams = [
            'ach_route' => $this->getRequest()->getParam('ach_route'),
            'ach_type' => $this->getRequest()->getParam('ach_type'),
            'ach_number' => $this->getRequest()->getParam('ach_number'),
            'ach_holder' => $this->getRequest()->getParam('ach_holder'),
            'is_default' => $this->getRequest()->getParam('default'),
            'firstname' => $this->getRequest()->getParam('firstname'),
            'lastname' => $this->getRequest()->getParam('lastname'),
            'company' => $this->getRequest()->getParam('company'),
            'telephone' => $this->getRequest()->getParam('telephone'),
            'fax' => $this->getRequest()->getParam('fax'),
            'street' => $this->getRequest()->getParam('street'),
            'country_id' => $this->getRequest()->getParam('country_id'),
            'city' => $this->getRequest()->getParam('city'),
            'region' => $this->getRequest()->getParam('region'),
            'region_id' => $this->getRequest()->getParam('region_id'),
            'postcode' => $this->getRequest()->getParam('postcode'),
        ];

        if ($this->_customerSession->getPciAddNewMethodResponse()) {
            $bankAccountResponse = $this->_customerSession->getPciAddNewMethodResponse();
            $this->_customerSession->unsPciAddNewMethodResponse();
        } else {
            /** @var  $bankAccountResponse */
            $bankAccountResponse = $this->_customerFactory->create()
                ->addCustomerBankAccount($customer, $achBankAccountParams);
        }

      //  var_dump("<pre>", $bankAccountResponse);exit;
        /** getting bank account response */
        if ($bankAccountResponse['error'] === false) {
            $this->messageManager->addSuccessMessage($bankAccountResponse['message']);
            $this->ebizchargeLogger->addInfo(__($bankAccountResponse['message']));

            return $this->resultRedirectFactory->create()->setPath($achListActionUrl);

        } else {
            $this->messageManager->addErrorMessage($bankAccountResponse['message']);
            $this->ebizchargeLogger->addError(__($bankAccountResponse['message']));
            return $this->resultRedirectFactory->create()->setPath("*/ach/addaction");
        }
    }
}
