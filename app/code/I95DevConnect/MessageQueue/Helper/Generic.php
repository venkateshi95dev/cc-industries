<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 * @updatedBy Divya Koona. Added getCustomerById,getCustomerAddressById functions
 * @updatedBy Ranjith Rasakatla. Added single class for Payment
 */

namespace I95DevConnect\MessageQueue\Helper;

use Exception;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Model\ChequeNumberFactory;
use I95DevConnect\MessageQueue\Model\SalesInvoice;
use I95DevConnect\MessageQueue\Model\SalesOrder;
use I95DevConnect\MessageQueue\Model\SalesShipment;
use Magento\Customer\Api\AddressRepositoryInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterfaceFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Generic helper class
 */
class Generic extends AbstractHelper
{
    public const SOURCE_ORDER_ID = 'source_order_id';
    public const PAYMENTMETHOD = 'checkmo';
    public const TARGET_CHEQUE_NUMBER = 'target_cheque_number';

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var customSalesOrder
     */
    public $customSalesOrder;

    /**
     * @var customSalesShipment
     */
    public $customSalesShipment;

    /**
     * @var customSalesInvoice
     */
    public $customSalesInvoice;

    /**
     *
     * @var fileresolver
     */
    public $fileResolver;

    /**
     *
     * @var AddressRepositoryInterfaceFactory
     */
    public $addressRepository;

    /**
     *
     * @var CustomerRepositoryInterfaceFactory
     */
    public $customerRepository;

    /**
     *
     * @var SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;

    /**
     * @var AddressFactory
     */
    public $customerAddressModel;

    /**
     * @var string
     */
    public $chequeNumberFactory;

    /**
     * Generic constructor.
     * @param LoggerInterface $logger
     * @param SalesOrder $customSalesOrder
     * @param SalesShipment $customSalesShipment
     * @param SalesInvoice $customInvoiceOrder
     * @param FileResolverInterface $fileResolver
     * @param AddressRepositoryInterfaceFactory $addressRepository
     * @param CustomerRepositoryInterfaceFactory $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AddressFactory $customerAddressModel
     * @param ChequeNumberFactory $chequeNumber
     * @param Context $context
     */
    public function __construct( // NOSONAR
        LoggerInterface $logger,
        SalesOrder $customSalesOrder,
        SalesShipment $customSalesShipment,
        SalesInvoice $customInvoiceOrder,
        FileResolverInterface $fileResolver,
        AddressRepositoryInterfaceFactory $addressRepository,
        CustomerRepositoryInterfaceFactory $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AddressFactory $customerAddressModel,
        ChequeNumberFactory $chequeNumber,
        Context $context
    ) {
        $this->logger = $logger;
        $this->customSalesOrder = $customSalesOrder;
        $this->customSalesShipment = $customSalesShipment;
        $this->customSalesInvoice = $customInvoiceOrder;
        $this->fileResolver = $fileResolver;
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerAddressModel = $customerAddressModel;
        $this->chequeNumberFactory = $chequeNumber;
        parent::__construct($context);
    }

    /**
     * Getting supported product types for an order creation
     *
     * @return array
     * @updatedBy Arushi Bansal
     */
    public function getSupportedProductTypesForOrder()
    {
        return $this->getSuportedProductTypes('Order');
    }

    /**
     * Generic function for getting supported product types
     *
     * @param object $entity
     *
     * @return array
     * @createdBy Arushi Bansal
     */
    public function getSuportedProductTypes($entity)
    {
        $supportedProductsArray = $supportedProductTypes = [];
        try {
            $xmlData = $this->fileResolver->get("settings.xml", 'global');

            if (count($xmlData) > 0) {
                foreach ($xmlData as $content) {
                    $xml = simplexml_load_string($content);
                    $currentEntity = json_decode(json_encode((array)$xml), 1);
                    if (isset($currentEntity['SupportedProductTypes'][$entity])) {
                        $supportedProductTypes[] = $currentEntity['SupportedProductTypes'][$entity];
                    }
                }
                $supportedProductTypes = implode(',', $supportedProductTypes);
            }
            $supportedProductsArray = explode(',', $supportedProductTypes);
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                LoggerInterface::CRITICAL
            );
        }
        return $supportedProductsArray;
    }

    /**
     * Getting supported product types for a product
     *
     * @return array
     * @updatedBy Arushi Bansal
     */
    public function getSupportedTypesForProduct()
    {
        return $this->getSuportedProductTypes('Product');
    }

    /**
     * Get source order id
     *
     * @param string $targetOrderId
     * @return string
     */
    public function getSourceOrderId($targetOrderId)
    {
        $customOrder = $this->customSalesOrder
            ->getCollection()
            ->addFieldToSelect(self::SOURCE_ORDER_ID)
            ->addFieldToFilter('target_order_id', $targetOrderId);

        $customOrder->getSelect()->limit(1);
        $customOrder = $customOrder->getData();
        return isset($customOrder[0][self::SOURCE_ORDER_ID]) ? $customOrder[0][self::SOURCE_ORDER_ID] : '';
    }

    /**
     * Set target order status
     *
     * @param string $targetOrderId
     * @param string $targetOrderStatus
     *
     * @return boolean
     * @throws Exception
     */
    public function setTargetOrderStatus($targetOrderId, $targetOrderStatus)
    {
        $customOrderModel = $this->customSalesOrder;
        $customOrderData = $customOrderModel->getCollection()
            ->addFieldToSelect('id')
            ->addFieldToFilter('target_order_id', $targetOrderId);

        $customOrderData->getSelect()->limit(1);
        $customOrderData = $customOrderData->getData();

        $id = isset($customOrderData[0]['id']) ? $customOrderData[0]['id'] : '';
        $customOrder = $customOrderModel->load($id);
        $customOrder->settargetOrderStatus($targetOrderStatus);
        $customOrder->save();
        return true;
    }

    /**
     * Get custom shipment id
     *
     * @param string $sourceShipmentId
     * @return string
     * @noinspection DuplicatedCode
     */
    public function getCustomShipmentById($sourceShipmentId)
    {
        try {
            $shipment = '';
            $shipmentModel = $this->customSalesShipment;
            $shipmentData = $shipmentModel->getCollection()
                ->addFieldToSelect('id')
                ->addFieldToFilter('source_shipment_id', $sourceShipmentId);
            $shipmentData->getSelect()->limit(1);
            $shipmentData = $shipmentData->getData();

            $shipmentId = (isset($shipmentData[0]['id']) ? $shipmentData[0]['id'] : '');
            $shipment = $shipmentModel->load($shipmentId);
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                LoggerInterface::CRITICAL
            );
        }

        return $shipment;
    }

    /**
     * Get custom invoice
     *
     * @param string $sourceInvoiceId
     * @return string
     */
    public function getCustomInvoiceById($sourceInvoiceId)
    {
        try {
            $invoice = '';
            $invoiceModel = $this->customSalesInvoice;
            $invoiceData = $invoiceModel->getCollection()
                ->addFieldToSelect('id')
                ->addFieldToFilter('source_invoice_id', $sourceInvoiceId);
            $invoiceData->getSelect()->limit(1);
            $invoiceData = $invoiceData->getData();

            $invoiceId = (isset($invoiceData[0]['id']) ? $invoiceData[0]['id'] : '');
            $invoice = $invoiceModel->load($invoiceId);
        } catch (LocalizedException $ex) {
            $this->createLog(__METHOD__, $ex->getMessage(), 'i95devException', 'critical');
        }

        return $invoice;
    }

    /**
     * Returns customer address based on address id
     *
     * @param int $addressId
     * @return array
     * @throws LocalizedException
     * @createdBy Divya Koona.
     */
    public function getCustomerAddressById($addressId)
    {
        try {
            $result = $this->addressRepository->create()->getById($addressId);
            return $result->__toArray();
        } catch (NoSuchEntityException $ex) {
            throw new LocalizedException(__('i95dev_addr_020 %1', $addressId));
        } catch (LocalizedException $ex) {
            throw new LocalizedException($ex->getMessage());
        }
    }

    /**
     * Fetch customer by customerId
     *
     * @param int $customerId
     * @return array
     * @throws LocalizedException
     * @createdBy Divya Koona.
     */
    public function getCustomerById($customerId)
    {
        try {
            $result = $this->customerRepository->create()->getById($customerId);
            return $result->__toArray();
        } catch (NoSuchEntityException $ex) {
            throw new LocalizedException(
                __('i95dev_cust_012'),
                null,
                108
            );
        } catch (LocalizedException $ex) {
            throw new LocalizedException($ex->getMessage());
        }
    }

    /**
     * Get customer info by $targetCustomerId
     *
     * @param type $targetCustomerId
     * @return array
     * @throws LocalizedException
     * @updatedBy Divya Koona. Removed Magento REST API call.
     */
    public function getCustomerInfoByTargetId($targetCustomerId , $email = null, $websiteId = null)
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('target_customer_id', $targetCustomerId, 'eq')
                ->create();
            $searchResults = $this->customerRepository->create()->getList($searchCriteria);
            $customerInfo = $searchResults->getItems();

            // If not found by target_customer_id and email is provided, try by email + website_id
            if (empty($customerInfo) && $email !==null && $websiteId !== null) {
                $searchCriteria = $this->searchCriteriaBuilder
                    ->addFilter('email', $email, 'eq')
                    ->addFilter('website_id', $websiteId, 'eq')
                    ->create();
                $searchResults = $this->customerRepository->create()->getList($searchCriteria);
                $customerInfo = $searchResults->getItems();
            }
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                LoggerInterface::CRITICAL
            );
            throw new LocalizedException(__($ex->getMessage()));
        }
        return $customerInfo;
    }

    /**
     * Retrieve address by target address id.
     *
     * @param string $targetBillingAddressId
     * @param int $customerId
     * @return array
     * @throws LocalizedException
     */
    public function getAddressByTargetAddressId($targetBillingAddressId, $customerId)
    {
        $customerAddress = $this->customerAddressModel->create();
        $adderssCollection = $customerAddress->getCollection()
            ->addFieldToSelect(['region_id', 'region', 'country_id'])
            ->addFieldToFilter("parent_id", $customerId)
            ->addFieldToFilter("target_address_id", $targetBillingAddressId);

        $adderssCollection->getSelect()->limit(1);

        if (empty($adderssCollection->getData()) && $adderssCollection->getSize() == 0) {
            throw new LocalizedException(__('i95dev_addr_016'));
        } else {
            return $adderssCollection->getData()[0];
        }
    }

    /**
     * Retrieves target payment
     *
     * @param object $order
     * @return string
     */
    public function getCheckNumber($order)
    {
        $checkNumber = '';
        if ($order) {
            $paymentMethodData = $order->getPayment();
            $paymentMethod = $paymentMethodData->getMethod();
            if ($paymentMethod == self::PAYMENTMETHOD) {
                $sourceId = $order->getId();
                $checkModelData = $this->chequeNumberFactory->create()->getCollection()
                    ->addFieldToSelect(self::TARGET_CHEQUE_NUMBER)
                    ->addFieldToFilter('source_order_id', $sourceId);
                $checkModelData->getSelect()->limit(1);
                $checkModelData = $checkModelData->getData();
                $checkNumber = (isset($checkModelData[0][self::TARGET_CHEQUE_NUMBER]) ?
                    $checkModelData[0][self::TARGET_CHEQUE_NUMBER] : '');
            }
        }
        return $checkNumber;
    }

    /**
     * Get target order id
     *
     * @param string $sourceOrderId
     * @return string
     */
    public function getTargetOrderId($sourceOrderId)
    {
        $loadCustomOrder = $this->customSalesOrder->getCollection();
        $loadCustomOrder->addFieldToSelect(['target_order_id'])
            ->addFieldToFilter(
                'source_order_id',
                $sourceOrderId
            )
            ->setOrder('id', 'DESC');
        $loadCustomOrder->getSelect()->limit(1);

        if ($loadCustomOrder->getSize() > 0) {
            return $loadCustomOrder->getFirstItem()->getTargetOrderId();
        } else {
            return "";
        }
    }
}
