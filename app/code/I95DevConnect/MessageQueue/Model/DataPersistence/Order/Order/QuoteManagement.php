<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order;

use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\Generic;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use I95DevConnect\MessageQueue\Model\SalesOrderFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CurrencyInterface;
use Magento\Quote\Model;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for setting quote informations to a quote.
 */
class QuoteManagement extends AbstractOrder
{
    public const I95_OBSERVER_SKIP = 'i95_observer_skip';
    public const DISCOUNT = 'discount';

    /**
     * @var QuoteFactory
     */
    public $quoteFactory;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var SalesOrderFactory
     */
    public $customSalesOrder;

    /**
     * @var CustomerInterface
     */
    public $customerApiData;

    /**
     *
     * @var Quote
     */
    public $quoteModel;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var CurrencyInterface
     */
    public $currencyApiInterface;

    /**
     *
     * @var CustomerInterface[]
     */
    public $customer;

    /**
     * @var CustomerFactory
     */
    public $magentoCustomerModel;

    /**
     *
     * @param LoggerInterfaceFactory $logger
     * @param Generic $genericHelper
     * @param QuoteFactory $quoteFactory
     * @param Data $dataHelper
     * @param CustomerFactory $magentoCustomerModel
     * @param CustomerInterface $customerApiData
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $currencyApiInterface
     * @param Validate $validate
     */
    public function __construct( // NOSONAR
        LoggerInterfaceFactory $logger,
        Generic $genericHelper,
        QuoteFactory $quoteFactory,
        Data $dataHelper,
        CustomerFactory $magentoCustomerModel,
        CustomerInterface $customerApiData,
        StoreManagerInterface $storeManager,
        CurrencyInterface $currencyApiInterface,
        Validate $validate
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->dataHelper = $dataHelper;
        $this->magentoCustomerModel = $magentoCustomerModel;
        $this->customerApiData = $customerApiData;
        $this->storeManager = $storeManager;
        $this->currencyApiInterface = $currencyApiInterface;
        parent::__construct($logger, $genericHelper, $validate);
    }

    /**
     * Set storeId and currency to quote
     *
     * @author Debashis S. Gopal
     */
    public function setStoreDetails()
    {
        $storeId = $this->storeManager->getDefaultStoreView()->getStoreId();
        $currencyEntity = $this->setCurrency();
        $this->quoteModel->setStoreId($storeId);
        $this->quoteModel->setCurrency($currencyEntity);
    }

    /**
     * Sets store currency data to quote
     *
     * @return CurrencyInterface
     * @throws NoSuchEntityException
     * @author Debashis S. Gopal
     */
    public function setCurrency()
    {
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $currencyRate = $this->storeManager->getStore()->getCurrentCurrencyRate();
        $currencyInterface = $this->currencyApiInterface;
        $currencyInterface->setGlobalCurrencyCode($currencyCode);
        $currencyInterface->setBaseCurrencyCode($currencyCode);
        $currencyInterface->setStoreCurrencyCode($currencyCode);
        $currencyInterface->setQuoteCurrencyCode($currencyCode);
        $currencyInterface->setStoreToBaseRate($currencyRate);
        $currencyInterface->setStoreToQuoteRate($currencyRate);
        $currencyInterface->setBaseToGlobalRate($currencyRate);
        $currencyInterface->setBaseToQuoteRate($currencyRate);
        return $currencyInterface;
    }

    /**
     * Get customer data using targetCustomerId and Initialize customer object.
     *
     * @param string $targetCustomerId
     * @throws LocalizedException
     * @author Debashis S. Gopal
     */
    public function getCustomerData($targetCustomerId)
    {
        $customerCollection = $this->magentoCustomerModel->create()
            ->getCollection()
            ->addAttributeToFilter('target_customer_id', $targetCustomerId);
        $customerCollection->getSelect()->limit(1);
        if ($customerCollection->getSize() > 0) {
            $this->customer = $customerCollection->getFirstItem();
        } else {
            throw new LocalizedException(__('i95dev_order_030'), null, 108);
        }
    }

    /**
     * Add discount in quote items
     *
     * @param array $discountEntity
     */
    public function setDiscountForQuoteItems($discountEntity)
    {
        $discountAmount = 0;

        foreach ($discountEntity as $eachDiscount) {
            if ($eachDiscount['discountType'] == self::DISCOUNT) {
                $discountAmount = $eachDiscount['discountAmount'];
            }
        }
        $objShippingAddress = $this->quoteModel->getShippingAddress();
        $total = $objShippingAddress->getSubtotal();
        if ($discountAmount > 0 && $total > 0) {
            $objShippingAddress->setDiscountDescription('ERP Discount');
            $objShippingAddress->addTotal([
                    'code' => self::DISCOUNT,
                    'title' => "Custom Discount",
                    'value' => -$discountAmount
                ]);
            $totalDiscountAmount = $discountAmount;
            $subtotalWithDiscount = $objShippingAddress->getSubtotal() - $discountAmount;
            $baseTotalDiscountAmount = $discountAmount;
            $baseSubtotalWithDiscount = $objShippingAddress->getBaseSubtotal() - $baseTotalDiscountAmount;
            $objShippingAddress->setDiscountAmount(-$totalDiscountAmount);
            $objShippingAddress->setSubtotalWithDiscount($subtotalWithDiscount);
            $objShippingAddress->setBaseDiscountAmount(-$baseTotalDiscountAmount);
            $objShippingAddress->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);
            $objShippingAddress->setGrandTotal($objShippingAddress->getGrandTotal() - $totalDiscountAmount);
            $objShippingAddress->setBaseGrandTotal($objShippingAddress->getBaseGrandTotal() - $baseTotalDiscountAmount);
            $lineDiscount = $this->getERPLineItemsDiscounts();
            foreach ($this->quoteModel->getAllItems() as $item) {
                //We apply discount amount based on the ratio between the GrandTotal and the RowTotal
                $rate = ($item->getPrice() * $item->getQty()) / $total;
                $ratedisc = round($discountAmount * $rate, 2);
                if (array_key_exists($item->getId(), $lineDiscount)) {
                    $itemdiscountAmount = $lineDiscount[$item->getId()];
                } else {
                    $itemdiscountAmount = $ratedisc;
                }
                $item->setDiscountAmount($itemdiscountAmount);
                $item->setBaseDiscountAmount($itemdiscountAmount)->save();
            }
        } else {
            $objShippingAddress->setDiscountAmount(0);
            $objShippingAddress->setBaseDiscountAmount(0);
            foreach ($this->quoteModel->getAllItems() as $item) {
                $item->setDiscountAmount(0);
                $itembasediscountamount = 0;
                $item->setBaseDiscountAmount($itembasediscountamount)->save();
            }
        }
    }

    /**
     * Retrieve line item level discount from erp DATA string
     *
     * @return array
     * @author By Arushi Bansal
     */
    public function getERPLineItemsDiscounts()
    {
        $lineDiscount = [];
        foreach ($this->quoteModel->getAllItems() as $item) {
            $discountAmount = 0;
            foreach ($this->stringData['orderItems'] as $itemData) {
                if ($this->checkIfDiscountKeyExists($itemData, $item)) {
                    $discountEntity = $this->dataHelper->getValueFromArray(self::DISCOUNT, $itemData);
                    if (!empty($discountEntity)) {
                        $discountAmount = $this->getSummativeDiscount($discountEntity, $discountAmount);
                    }
                    $lineDiscount[$item->getId()] = $discountAmount;
                }
            }
        }
        return $lineDiscount;
    }

    /**
     * Get sum of all discount types discount
     *
     * @param array $discountEntity
     * @param float $discountAmount
     * @return mixed
     */
    public function getSummativeDiscount($discountEntity, $discountAmount)
    {
        foreach ($discountEntity as $eachDiscount) {
            if ($eachDiscount['discountType'] == self::DISCOUNT) {
                $discountAmount += $eachDiscount['discountAmount'];
            }
        }
        return $discountAmount;
    }

    /**
     * Check if discount key exists
     *
     * @param array $itemData
     * @param object $item
     * @return bool
     */
    public function checkIfDiscountKeyExists($itemData, $item)
    {
        return (strtolower(trim($itemData['sku'])) == strtolower(trim($item->getSku()))) && array_key_exists(
            self::DISCOUNT,
            $itemData
        );
    }

    /**
     * Adding customer details to quote.
     *
     * @author Debashis S. Gopal
     */
    public function setCustomerDetails()
    {
        $customerData = $this->customer->getData();
        $customerObj = $this->customerApiData;
        $customerObj->setId($customerData['entity_id']);
        foreach ($customerData as $key => $data) {
            if (!is_array($data)) {
                $customerObj->setData($key, $data);
            }
        }
        $this->quoteModel->setCustomer($customerObj);
        $this->quoteModel->setCustomerId($customerData['entity_id']);
        $this->quoteModel->setCustomerEmail($customerData['email']);
    }

    /**
     * Add shipping address and billing address to quote
     *
     * @author Debashis S. Gopal
     */
    public function setAddressDetails()
    {
        $billingAddress = $this->orderBillingAddress->addBillingAddress();
        $shippingAddress = $this->orderShippingAddress->addShippingAddress();
        $this->quoteModel->setShippingAddress($shippingAddress);
        $this->quoteModel->getBillingAddress()->addData($billingAddress);
    }

    /**
     * Adding Shipping method and shippig amount.
     *
     * @throws LocalizedException
     * @author Debashis S. Gopal
     */
    public function setShippingDetails()
    {
        $this->quoteModel->getShippingAddress()
            ->setShippingMethod($this->stringData['shippingMethod'])
            ->setShippingAmount($this->shippingAmount)
            ->setBaseShippingAmount($this->shippingAmount);
        $this->quoteModel->getShippingAddress()->setCollectShippingRates(true);
        $this->quoteModel->setTotalsCollectedFlag(false);
        $this->quoteModel->collectTotals();
        $this->quoteModel->getShippingAddress()->setCollectShippingRates(true);
        $this->quoteModel->getShippingAddress()->collectShippingRates();
        $shippingMethodRates = $this->quoteModel->getShippingAddress()
            ->getShippingRateByCode($this->stringData['shippingMethod']);
        if (!is_object($shippingMethodRates)) {
            throw new LocalizedException(
                __("i95dev_quote_invalid_shippingMethod"),
                null,
                104
            );
        }
    }

    /**
     * Deactivate the quote once it is converted to order.
     *
     * @param Model $createdQuote
     * @author Debashis S. Gopal
     */
    public function deactivateQuote($createdQuote)
    {
        $createdQuote->setIsActive(0);
        $this->dataHelper->unsetGlobalValue(self::I95_OBSERVER_SKIP);
        $this->dataHelper->setGlobalValue(self::I95_OBSERVER_SKIP, true);
        $createdQuote->save();
        $this->dataHelper->unsetGlobalValue(self::I95_OBSERVER_SKIP);
    }

    /**
     * Update Totals with shipping amount.
     *
     * @param Address $objShippingAddress
     * @author Debashis S. Gopal
     */
    public function updateShippingAmountInTotal($objShippingAddress)
    {
        $objShippingAddress->setGrandTotal($objShippingAddress->getSubtotal() + $this->shippingAmount);
        $objShippingAddress->setBaseGrandTotal($objShippingAddress->getBaseSubtotal() + $this->shippingAmount);
        $this->quoteModel->setGrandTotal($objShippingAddress->getSubtotal() + $this->shippingAmount);
        $this->quoteModel->setBaseGrandTotal($objShippingAddress->getBaseSubtotal() + $this->shippingAmount);
    }
}
