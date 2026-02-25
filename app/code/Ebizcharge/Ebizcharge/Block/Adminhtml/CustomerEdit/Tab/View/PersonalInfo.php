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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\CustomerEdit\Tab\View;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config as ConfigModel;
use Ebizcharge\Ebizcharge\Model\Customer;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo as CustomerPersonalInfo;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Logger;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;

/**
 * Personal Info Blocl class
 *
 * Class PersonalInfo
 */
class PersonalInfo extends CustomerPersonalInfo
{
    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var ConfigModel
     */
    protected ConfigModel $_configModel;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * PersonalInfo constructor.
     *
     * @param Context $context
     * @param AccountManagementInterface $accountManagement
     * @param GroupRepositoryInterface $groupRepository
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param Address $addressHelper
     * @param DateTime $dateTime
     * @param CustomerFactory $customerFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param ConfigModel $configModel
     * @param Registry $registry
     * @param Mapper $addressMapper
     * @param DataObjectHelper $dataObjectHelper
     * @param Logger $customerLogger
     * @param array $data
     */
    public function __construct(
        Context                    $context,
        AccountManagementInterface $accountManagement,
        GroupRepositoryInterface   $groupRepository,
        CustomerInterfaceFactory   $customerDataFactory,
        Address                    $addressHelper,
        DateTime                   $dateTime,
        CustomerFactory            $customerFactory,
        EbizchargeLogger           $ebizchargeLogger,
        ConfigModel                $configModel,
        Registry                   $registry,
        Mapper                     $addressMapper,
        DataObjectHelper           $dataObjectHelper,
        Logger                     $customerLogger,
        array                      $data = []
    ) {
        parent::__construct(
            $context,
            $accountManagement,
            $groupRepository,
            $customerDataFactory,
            $addressHelper,
            $dateTime,
            $registry,
            $addressMapper,
            $dataObjectHelper,
            $customerLogger,
            $data
        );

        /** @var  _configModel */
        $this->_configModel = $configModel;
        /** @var  _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Get Ebiz Customer Id
     *
     * @return array|mixed|string|null
     */
    public function getEbizCustId()
    {
        $ebizCustID = "";
        try {
            $customer = $this->getEbizCustomer();
            $ebizCustID = $customer->getEcCustId();

        } catch (NoSuchEntityException $e) {
            $this->_ebizchargeLogger->addCritical(__("Customer not found " . $e->getMessage()));
        }
        return $ebizCustID;
    }

    /**
     * Get Division Id
     *
     * @return string
     */
    public function getDivisionId()
    {
        $divisionID = "";
        try {
            $customer = $this->getEbizCustomer();
            $divisionID = $customer->getEcDivisionId();

        } catch (NoSuchEntityException $e) {
            $this->_ebizchargeLogger->addCritical(__("Customer not found " . $e->getMessage()));
        }
        return $divisionID;
    }

    /**
     * Get EbizCustomer
     *
     * @return Customer
     */
    public function getEbizCustomer()
    {
        $customerId = $this->getCustomerId();
        return $this->_customerFactory->create()->load($customerId);
    }

    /**
     * Get Ebiz Hub Token
     *
     * @return array|Phrase|mixed
     */
    public function getEbizHubToken()
    {
        $customer = $this->getEbizCustomer();
        $customerToken = __("Not synced");
        if ($customer) {
            return $customer->getEcCustToken() ?? $customerToken;
        }
        return $customerToken;
    }

    /**
     * Get Ebiz Hub Internal Id
     *
     * @return array|Phrase|mixed
     */
    public function getEbizHubInternalId()
    {
        $customer = $this->getEbizCustomer();
        $customerInternalId = __("Not synced");
        if ($customer) {
            return $customer->getEcCustInternalId() ?? $customerInternalId;
        }
        return $customerInternalId;
    }

    /**
     * Get Ebiz Created In
     *
     * @return string
     */
    public function getEbizCreatedIn()
    {
        $customer = $this->getEbizCustomer();
        $createdIn = "";
        if ($customer) {
            $createdIn = $customer->getCreatedIn();
        }
        return $createdIn;
    }

    /**
     * Is EBizCharge is Active
     *
     * @param mixed $storeId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isEbizActive(mixed $storeId = "0")
    {
        $storeId = $storeId !== "0" ? $storeId : $this->getStoreId();
        return $this->_configModel->isActive($storeId);
    }

    /**
     * Get Store Id
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->getCustomer() ? $this->getCustomer()->getStoreId() : 0;
    }

    /**
     * Retrieve shipping address html
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getShippingAddressHtml()
    {
        try {
            $address = $this->accountManagement->getDefaultShippingAddress($this->getCustomer()->getId());
        } catch (NoSuchEntityException $e) {
            return $this->escapeHtml(__('The customer does not have default billing address.'));
        }

        if ($address === null) {
            return $this->escapeHtml(__('The customer does not have default billing address.'));
        }

        return $this->addressHelper->getFormatTypeRenderer(
            'html'
        )->renderArray(
            $this->addressMapper->toFlatArray($address)
        );
    }
}
