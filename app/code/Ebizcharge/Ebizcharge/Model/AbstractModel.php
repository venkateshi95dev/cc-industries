<?php

/**
 * Century Business Solutions.
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
 *
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 *
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model;

use Amasty\GiftCardAccount\Api\GiftCardAccountRepositoryInterface;
use Ebizcharge\Ebizcharge\Helper\Data;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Order\Invoice;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Product as EbizResourceProduct;
use Exception;
use Magento\Checkout\Model\Cart as MageCartModel;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;

/**
 * Abstract Model
 */
abstract class AbstractModel
{
    /**
     * Enable Recurring
     */
    public const ENABLE_RECURRING = 1;
    /**
     *
     * Recurring Indefinitely
     */
    public const REC_INDEFINITELY = 1;
    /**
     * @var MageCartModel
     */
    protected MageCartModel $cartModel;
    /**
     * @var ConfigFactory
     */
    protected ConfigFactory $configFactory;
    /**
     * @var JsonSerializer
     */
    protected JsonSerializer $jsonSerializer;
    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;
    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $quoteItemOptionCollection;
    /**
     * @var CollectionFactory
     */
    protected $quoteItemCollectionFactory;
    /**
     * @var Option
     */
    protected Option $itemOptionResource;
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;
    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManagerInterface;
    /**
     * @var QuoteFactory
     */
    protected QuoteFactory $_quoteFactory;
    /**
     * @var WriterInterface
     */
    protected WriterInterface $configWriter;
    /**
     * @var QuoteRepository
     */
    protected QuoteRepository $_quoteRepository;
    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;
    /**
     * @var SessionManagerInterface
     */
    protected SessionManagerInterface $sessionManagerInterface;
    /**
     * @var EbizResourceProduct
     */
    protected EbizResourceProduct $ebizResourceProduct;
    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;
    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;
    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;
    /**
     * @var Invoice
     */
    protected Invoice $invoiceFactory;
    /**
     * @var InvoiceCollectionFactory
     */
    protected InvoiceCollectionFactory $invoiceCollectionFactory;
    /**
     * @var ItemFactory
     */
    protected ItemFactory $quoteItemFactory;
    /**
     * @var SerializerInterface|SerializerInterface
     */
    protected SerializerInterface $serializer;
    /**
     * @var
     */
    protected $backendQuoteSession;
    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $recurringFactory;
    /**
     * @var OrderExtensionFactory
     */
    protected OrderExtensionFactory $orderExtensionFactory;
    /**
     * @var mixed|null
     */
    protected $giftCardAccountRepositoryInterface = null;

    /**
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $objectManager;

    /**
     * @param ConfigFactory $configFactory
     * @param JsonSerializer $jsonSerializer
     * @param CheckoutSession $checkoutSession
     * @param MageCartModel $cartModel
     * @param QuoteFactory $quoteFactory
     * @param RequestInterface $request
     * @param ManagerInterface $messageManagerInterface
     * @param SessionManagerInterface $sessionManagerInterface
     * @param CollectionFactory $quoteItemOptionCollection
     * @param Option $itemOptionResource
     * @param WriterInterface $configWriter
     * @param OrderFactory $orderFactory
     * @param CustomerFactory $customerFactory
     * @param QuoteRepository $quoteRepository
     * @param EbizResourceProduct $ebizResourceProduct
     * @param CustomerSession $customerSession
     * @param OrderRepositoryInterface $orderRepository
     * @param Invoice $invoiceFactory
     * @param InvoiceCollectionFactory $invoiceCollectionFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param RecurringFactory $recurringFactory
     * @param ItemFactory $quoteItemFactory
     * @param SerializerInterface $serializer
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ConfigFactory            $configFactory,
        JsonSerializer           $jsonSerializer,
        CheckoutSession          $checkoutSession,
        MageCartModel            $cartModel,
        QuoteFactory             $quoteFactory,
        RequestInterface         $request,
        ManagerInterface         $messageManagerInterface,
        SessionManagerInterface  $sessionManagerInterface,
        CollectionFactory        $quoteItemOptionCollection,
        Option                   $itemOptionResource,
        WriterInterface          $configWriter,
        OrderFactory             $orderFactory,
        CustomerFactory          $customerFactory,
        QuoteRepository          $quoteRepository,
        EbizResourceProduct      $ebizResourceProduct,
        CustomerSession          $customerSession,
        OrderRepositoryInterface $orderRepository,
        Invoice                  $invoiceFactory,
        InvoiceCollectionFactory $invoiceCollectionFactory,
        EbizchargeLogger         $ebizchargeLogger,
        RecurringFactory         $recurringFactory,
        ItemFactory              $quoteItemFactory,
        SerializerInterface      $serializer,
        OrderExtensionFactory    $orderExtensionFactory,
        ObjectManagerInterface   $objectManager
    )
    {
        /** @var $checkoutSession */
        $this->checkoutSession = $checkoutSession;
        /** @var $configFactory */
        $this->configFactory = $configFactory;
        /** @var $jsonSerializer */
        $this->jsonSerializer = $jsonSerializer;
        /** @var $ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var $cartModel */
        $this->cartModel = $cartModel;
        /** @var $quoteItemCollectionFactory */
        $this->quoteItemCollectionFactory = $quoteItemOptionCollection;
        /** @var $itemOptionResource */
        $this->itemOptionResource = $itemOptionResource;
        /** @var $request */
        $this->request = $request;
        /** @var $messageManagerInterface */
        $this->messageManagerInterface = $messageManagerInterface;
        /** @var  $_quoteFactory */
        $this->_quoteFactory = $quoteFactory;
        /** @var $configWriter */
        $this->configWriter = $configWriter;
        /** @var  $_orderFactory */
        $this->_orderFactory = $orderFactory;
        /** @var  $_quoteRepository */
        $this->_quoteRepository = $quoteRepository;
        /** @var $sessionManagerInterface */
        $this->sessionManagerInterface = $sessionManagerInterface;
        /** @var $ebizResourceProduct */
        $this->ebizResourceProduct = $ebizResourceProduct;
        /** @var $customerFactory */
        $this->customerFactory = $customerFactory;
        /** @var $customerSession */
        $this->customerSession = $customerSession;
        /** @var $orderRepository */
        $this->orderRepository = $orderRepository;
        /** @var $invoiceFactory */
        $this->invoiceFactory = $invoiceFactory;
        /** @var $invoiceCollectionFactory */
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        /**
         * quote item factory
         */
        $this->quoteItemFactory = $quoteItemFactory;
        /**
         * Serializer
         */
        $this->serializer = $serializer;
        /** @var $recurringFactory */
        $this->recurringFactory = $recurringFactory;
        /** @var $orderExtensionFactory */
        $this->orderExtensionFactory = $orderExtensionFactory;
        /** @var $objectManager */
        $this->objectManager = $objectManager;

        if (class_exists(GiftCardAccountRepositoryInterface::class)) {
            /** @var $giftCardAccountRepositoryInterface */
            $this->giftCardAccountRepositoryInterface = $this->objectManager
                ->get(GiftCardAccountRepositoryInterface::class);
        }
    }

    /**
     * @param $buyRequest
     * @param $storeId
     * @return bool
     */
    public function isRecurringExist($buyRequest = null, $storeId = 0)
    {
        $isRecurringExist = false;
        if ($this->isRecurringEnabled($storeId)) {
            if (isset($buyRequest["recurring"]) && isset($buyRequest["recurring"]["rec_activate"]) && (int)($buyRequest["recurring"]["rec_activate"]) === 1) {
                $isRecurringExist = true;
            }
        }
        return $isRecurringExist;
    }

    public function isRecurringEnabled($storeId = null): bool
    {
        $storeId = $storeId ?? $this->getStoreId();
        $configFactory = $this->configFactory->create();
        return (bool)$configFactory->isRecurringActive($storeId);
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->getEbizchargeConfig()->getStore()->getId() ?? 0;
    }

    /**
     * @return mixed
     */
    public function getStore()
    {
        return $this->getEbizchargeConfig()->getStore();
    }

    /**
     * @return mixed
     */
    public function getEbizchargeConfig()
    {
        return $this->configFactory->create();
    }

    /**
     * @param $startDate
     * @return string
     * @throws Exception
     */
    public function getIndefiniteRecurringDate($startDate = null): string
    {
        return $this->recurringFactory->create()->prepareIndefiniteRecurringDate($startDate);
    }

    /**
     * @param $quoteItem
     * @return bool
     */
    public function isRecurredItem($quoteItem = null): bool
    {
        return $this->recurringFactory->create()->isRecurredItem($quoteItem);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function isUploadOrdersEnabled($storeId = null)
    {
        $storeId = $storeId ?? $this->getStoreId();
        return $this->getEbizchargeConfig()->isUplaodOrdersEnabled($storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function isEconnectUploadEnabled($storeId = null)
    {
        $storeId = $storeId ?? $this->getStoreId();
        return $this->getEbizchargeConfig()->isEconnectUploadEnabled($storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function isModuleActive($storeId = null)
    {
        $storeId = $storeId ?? $this->getStoreId();
        return $this->getEbizchargeConfig()->isActive($storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function isUploadItemsEnabled($storeId = null)
    {
        $storeId = $storeId ?? $this->getStoreId();
        return $this->getEbizchargeConfig()->isUploadItemsEnabled($storeId);
    }

    /**
     * @param $quoteItems
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function reArrangeSubscribedCartItems($quoteItems = []): array
    {
        return $this->_quoteFactory->create()->reArrangeSubscribedCartItems($quoteItems);
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteRecurredItems()
    {

        $subscribedItems = [];
        $quote = $this->checkoutSession->getQuote();
        if ($quote && is_object($quote)) {
            $quoteItems = $quote->getAllVisibleItems() ?? [];
            /** @var  $quoteItems */
            if (count($quoteItems) > 0) {
                foreach ($quoteItems as $index => $quoteItem) {
                    $buyRequestData = $quoteItem->getBuyRequest();
                    $recurringData = $buyRequestData->getRecurring() ?? [];
                    $recurringData = (array)$recurringData;
                    if (isset($recurringData['rec_activate']) && !empty($recurringData['rec_activate']) && isset($recurringData['rec_frequency']) && !empty($recurringData['rec_frequency'])) {
                        $subscribedItems[] = $quoteItem;
                    }
                }
            }
        }

        return $subscribedItems;
    }

    /**
     * @return CartInterface|\Magento\Quote\Model\Quote|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuote()
    {
        try {
            if ($this->isAdmin()) {
                return $this->backendQuoteSession->getQuote();
            }
            return $this->checkoutSession->getQuote();
        } catch (Exception $e) {
            /** logging error and exception */
            $this->ebizchargeLogger->addError(__("Exception occurred " . $e->getMessage()));
        }
        return null;
    }

    public function isAdmin(): bool
    {
        if (Data::getAreaCode() === Area::AREA_ADMINHTML) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteUnRecurredItems()
    {

        $unSubscribedItems = [];
        $quote = $this->checkoutSession->getQuote();
        if ($quote && is_object($quote)) {
            $quoteItems = $quote->getAllVisibleItems() ?? [];
            /** @var  $quoteItems */
            if (count($quoteItems) > 0) {
                foreach ($quoteItems as $index => $quoteItem) {
                    $buyRequestData = $quoteItem->getBuyRequest();
                    $recurringData = $buyRequestData->getRecurring() ?? [];
                    $recurringData = (array)$recurringData;
                    if (isset($recurringData['rec_activate']) && !empty($recurringData['rec_activate']) && isset($recurringData['rec_frequency']) && !empty($recurringData['rec_frequency'])) {
                    } else {
                        $unSubscribedItems[] = $quoteItem;
                    }
                }
            }
        }

        return $unSubscribedItems;
    }

    /**
     * Get Subscribed Quote Items Data from DB.
     *
     * @param null|mixed $quoteCustomOptionId
     * @param null|mixed $quoteProductId
     */
    public function getSubscribedQuoteItemsDataFromDb($quoteCustomOptionId = null, $quoteProductId = null): array
    {
        /** @var  $quoteItemCollection */
        $quoteItemCollection = $this->quoteItemCollectionFactory->create();

        return $quoteItemCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter('item_id', $quoteCustomOptionId)
            ->addFieldToFilter('product_id', $quoteProductId)
            ->getData();
    }

    /**
     * Search from Array Keys
     *
     * @param array $arrayStack
     * @param string $searchKey
     * @param array $searchResults
     * @return array
     */
    public function searchArrayByKey(
        array  $arrayStack,
        string $searchKey,
        array  &$searchResults = []
    ): array
    {
        foreach ($arrayStack as $key => $value) {

            if ($key === $searchKey) {
                $searchResults[$key] = $value;
            }
            if (is_array($value)) {
                $this->searchArrayByKey($value, $searchKey, $searchResults);
            }
        }
        return $searchResults;
    }
}
