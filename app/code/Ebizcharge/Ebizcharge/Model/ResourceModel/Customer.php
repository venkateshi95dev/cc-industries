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

namespace Ebizcharge\Ebizcharge\Model\ResourceModel;

use Ebizcharge\Ebizcharge\Api\Data\CustomerInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Customer\Api\Data\CustomerInterface as CustomerInterfaceAlias;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\ResourceModel\Customer as coreCustomer;
use Magento\Eav\Model\Entity\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Validator\Factory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customer Resource Model
 *
 * Class Customer
 */
class Customer extends coreCustomer
{
    /**
     * Ebizcharge Customer Sync Status Active
     *
     * @const: EBIZ_CUSTOMER_SYNC_STATUS_ACTIVE
     */
    public const EBIZ_CUSTOMER_SYNC_STATUS_ACTIVE = 1;

    /**
     * @var AddressFactory
     */
    protected AddressFactory $_addressFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $sessionManagerInterface;

    /**
     * @param Context $context
     * @param Snapshot $entitySnapshot
     * @param RelationComposite $entityRelationComposite
     * @param ScopeConfigInterface $scopeConfig
     * @param Factory $validatorFactory
     * @param DateTime $dateTime
     * @param AddressFactory $addressFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customerFactory
     * @param SessionManagerInterface $sessionManagerInterface
     * @param AccountConfirmation|null $accountConfirmation
     * @param $data
     */
    public function __construct(
        Context                 $context,
        Snapshot                $entitySnapshot,
        RelationComposite       $entityRelationComposite,
        ScopeConfigInterface    $scopeConfig,
        Factory                 $validatorFactory,
        DateTime                $dateTime,
        AddressFactory          $addressFactory,
        EbizchargeLogger        $ebizchargeLogger,
        StoreManagerInterface   $storeManager,
        CustomerFactory         $customerFactory,
        SessionManagerInterface $sessionManagerInterface,
        AccountConfirmation     $accountConfirmation = null,
                                $data = []
    )
    {
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _addressFactory */
        $this->_addressFactory = $addressFactory;
        /** @var  _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var  sessionManagerInterface */
        $this->sessionManagerInterface = $sessionManagerInterface;

        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $scopeConfig,
            $validatorFactory,
            $dateTime,
            $storeManager,
            $data,
            $accountConfirmation
        );
    }

    /**
     * Load By Ebizcharge Customer Id
     *
     * @param mixed $ebizCustomerId
     * @return string
     */
    public function loadByEbizchargeCustomerId($ebizCustomerId)
    {
        /** @var $mainTable */
        $mainTable = $this->getEntityTable();
        /** @var $connection */
        $connection = $this->getConnection();
        /** @var $where */
        $where = $connection->quoteInto(CustomerInterface::EBIZCHARGE_CUSTOMER_ID . " = ?", $ebizCustomerId);
        /** @var $select */
        $select = $connection->select()->from($mainTable, ['entity_id'])->where($where);

        return $connection->fetchOne($select);
    }

    /**
     * Load By Ebizchare Customer Internal Id
     *
     * @param string $ebizCustomerInternalId
     * @return string
     */
    public function loadByEbizchargeCustomerInternalId(string $ebizCustomerInternalId)
    {
        /** @var $mainTable */
        $mainTable = $this->getEntityTable();
        /** @var $connection */
        $connection = $this->getConnection();
        /** @var $where */
        $where = $connection->quoteInto(
            CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID . " = ?",
            $ebizCustomerInternalId
        );
        $select = $connection->select()->from($mainTable, ['entity_id'])->where($where);

        return $connection->fetchOne($select);
    }

    /**
     * Is Ebiz Customer Exists Locally
     *
     * @param string $ebizCustomerInternalId
     * @return string
     */
    public function isEbizCustomerExistsLocally(string $ebizCustomerInternalId)
    {
        /** @var $mainTable */
        $mainTable = $this->getEntityTable();
        /** @var $connection */
        $connection = $this->getConnection();

        /** @var  $select */
        $select = $connection->select()->from($mainTable, ['entity_id'])
            ->where($connection->quoteInto(
                CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID . " = ?",
                $ebizCustomerInternalId
            ));
        return $connection->fetchOne($select);
    }


    /**
     * @param $customer
     * @return bool
     */
    public function isEbizFieldsExistsLocal($customer = null)
    {
        $ebizFieldsExists = true;
        $isDownload = (bool)$customer->getIsDownload();
        if (
            !$customer->getEcCustInternalId() ||
            !$customer->getEcCustId() ||
            !$customer->getEcCustToken() ||
            !$customer->getEcSoftwareId() ||
            !$customer->getEcDivisionId()
        ) {
            $ebizFieldsExists = false;
        }


        return $ebizFieldsExists;

    }

    /**
     * @param DataObject $customer
     * @return $this|Customer
     * @throws NoSuchEntityException
     */
    protected function _afterSave(DataObject $customer)
    {
        parent::_afterSave($customer);
        if (!$this->_customerFactory->create()->uploadCustomerIsActive()) {
            return $this;
        }
        $customerId = $customer->getId();
        $ebizCustomerId = $customer->getEcCustId();
        if($customer->getIsFromCron() || $customer->getIsDownload()){
            return $this;
        }

        /** if customer is instance of Customer */
        $customer = $this->_customerFactory->create()->load($customerId);
        /**
         * check EBizCharge Customer ID if NAV or any other ERP has not updated the customer ID yet
         */
        /** save Customer */
        $this->saveEbizchargeFields($customer);
        $this->_ebizchargeLogger->addInfo(__("Customer to be synced when registered customer ID:" . $customerId));

        return $this;
    }

    /**
     * Save EBizCharge Fields and Sync the Customer to Hub
     *
     * @param $mageCustomer
     * @return $this
     */
    public function saveEbizchargeFields($mageCustomer = null)
    {
        if (!$mageCustomer) {
            return $this;
        }
        try {
            /**
             * checking customer exists locally
             */
             $customerId = $mageCustomer->getId();
             $customerFactory = $this->_customerFactory->create();
             $customer = $customerFactory->load($customerId);
             $ebizCustomer = $customerFactory->syncCustomerToEBizChargeHub($mageCustomer);

            if (count($ebizCustomer) > 0 &&
                isset($ebizCustomer[CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS]) &&
                $ebizCustomer[CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS] === CustomerInterface::EBIZCHARGE_CUSTOMER_ADD_SYNC_RESPONSE_STATUS_SUCCESS

            ) {
                /** @var  $bind */
                $bind = [
                    CustomerInterface::EBIZCHARGE_CUSTOMER_SYNC_STATUS => "1",
                    CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID => $ebizCustomer[CustomerInterface::EBIZCHARGE_CUSTOMER_INTERNAL_ID],
                    CustomerInterface::EBIZCHARGE_CUSTOMER_ID => $ebizCustomer[CustomerInterface::EBIZCHARGE_CUSTOMER_ID],
                    CustomerInterface::EBIZCHARGE_DIVISION_ID => $ebizCustomer[CustomerInterface::EBIZCHARGE_DIVISION_ID],
                    CustomerInterface::EBIZCHARGE_SOFTWARE_ID => $ebizCustomer[CustomerInterface::EBIZCHARGE_SOFTWARE_ID],
                    CustomerInterfaceAlias::CREATED_IN => $ebizCustomer[CustomerInterface::EBIZCHARGE_SOFTWARE_ID],
                    CustomerInterface::EBIZCHARGE_CUSTOMER_TOKEN => $ebizCustomer[CustomerInterface::EBIZCHARGE_CUSTOMER_TOKEN],
                    CustomerInterface::EBIZCHARGE_CUSTOMER_LAST_SYNC_DATE => $ebizCustomer[CustomerInterface::EBIZCHARGE_CUSTOMER_LAST_SYNC_DATE]
                ];
                /** updating the extra fields via Customer Resource Model */
                $this->getConnection()->update($this->getEntityTable(), $bind, $this->getConnection()->quoteInto(
                    CustomerInterface::EBIZCHARGE_CUSTOMER_ENTITY_ID . " = ?",
                    $customerId
                    )
                );
            }

        } catch (LocalizedException $exception) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during syncing customer to EBizCharge Hub. error:" . $exception->getMessage()));
        }
        return $this;
    }
}
