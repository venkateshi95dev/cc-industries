<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Reverse;

use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\Generic;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\AbstractOrder;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;

/**
 * Class Add item information while creating an order
 */
class Item extends AbstractOrder
{
    public const I95EXC = 'i95devApiException';
    public const PRICE = "price";

    /**
     *
     * @var []
     */
    public $quoteItems;

    /**
     *
     * @var []
     */
    public $itemData;

    /**
     *
     * @var ProductFactory
     */
    public $magentoProductModel;

    /**
     *
     * @var orderItems[]
     */
    public $orderItems;

    /**
     *
     * @var itemEntity[]
     */
    public $itemEntity = [];

    /**
     *
     * @var subTotals
     */
    public $subTotals = 0;

    /**
     *
     * @var totalQty
     */
    public $totalQty = 0;

    /**
     * @var object
     */
    public $genericHelper;

    /**
     * @var int
     */
    public $orderItemsCount = 0;

    /**
     * @var int
     */
    public $orderQuantity = 0;

    /**
     * @var int
     */
    public $itemsCount;

    /**
     * @var int
     */
    public $itemQuantity;

    /**
     * @var object
     */
    public $quoteModel;

    /**
     * @var ProductFactory
     */
    public $productFactory;

    /**
     * @var ProductFactory
     */
    public $simpleproduct;

    /**
     * @var ProductFactory
     */
    public $parentProduct;

    /**
     * @var AttributeFactory
     */
    public $entityAttributeFactory;

    /**
     * @var ProductRepositoryInterfaceFactory
     */
    public $productRepository;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var QuoteFactory
     */
    protected $quote;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRep;

    /**
     * @var Manager
     */
    protected $eventManager;

    /**
     * @var Validate
     */
    public $validate;

    /**
     * @var DataObject
     */
    public $buyOptions;

    /**
     *
     * @param Data $dataHelper
     * @param ProductFactory $magentoProductModel
     * @param LoggerInterfaceFactory $logger
     * @param DateTime $date
     * @param QuoteFactory $quote
     * @param CartRepositoryInterface $cartRep
     * @param Generic $genericHelper
     * @param Manager $eventManager
     * @param ProductRepositoryInterfaceFactory $productRepository
     * @param Validate $validate
     */
    public function __construct( // NOSONAR
        Data $dataHelper,
        ProductFactory $magentoProductModel,
        LoggerInterfaceFactory $logger,
        DateTime $date,
        QuoteFactory $quote,
        CartRepositoryInterface $cartRep,
        Generic $genericHelper,
        Manager $eventManager,
        ProductRepositoryInterfaceFactory $productRepository,
        Validate $validate
    ) {
        $this->dataHelper = $dataHelper;
        $this->magentoProductModel = $magentoProductModel;
        $this->date = $date;
        $this->quote = $quote;
        $this->cartRep = $cartRep;
        $this->eventManager = $eventManager;
        $this->productRepository = $productRepository;
        parent::__construct(
            $logger,
            $genericHelper,
            $validate
        );
    }

    /**
     * Add Item Informations To quote.
     *
     * @param Quote $quoteModel
     * @return Quote $this->quoteModel
     * @throws LocalizedException
     */
    public function addItemsToQuote($quoteModel)
    {
        try {
            $this->quoteModel = $quoteModel;
            foreach ($this->quoteItems as $itemData) {
                $this->addSimpleProduct($itemData);
            }
            $this->quoteModel->setItemsCount($this->itemsCount);
            $this->quoteModel->setItemsQty($this->itemQuantity);
            $this->quoteModel->setIsVirtual(0);
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, 'critical');
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
        return $this->quoteModel;
    }

    /**
     * Adding Item to quote
     *
     * @param array $itemData
     * @throws LocalizedException
     * @author Divya Koona. Removed unused code for discount amount.
     */
    public function addSimpleProduct($itemData)
    {
        $this->itemData = $itemData;
        $sku = $itemData['sku'];
        $product = $this->getProductBySku($sku);
        if (!empty($product)) {
            $buyOptionsList = [];
            $this->simpleproduct = $this->magentoProductModel->create()->loadByAttribute("sku", $sku);
            $productData = $this->simpleproduct->getData();
            $this->parentProduct = $this->magentoProductModel->create()->load($this->simpleproduct->getId());
            $buyOptionsList = [
                'qty' => $itemData['qty'],
                '_processing_params' => []
            ];
            $buyOptionsList = $this->setCustomPrice($itemData, $productData[self::PRICE], $buyOptionsList);

            $this->buyOptions = new DataObject($buyOptionsList);

            $beforeQuoteSave = 'erpconnect_messagequeuetomagento_beforesave_quoteitem';
            $this->eventManager->dispatch($beforeQuoteSave, ['quoteObject' => $this]);

            $response = $this->quoteModel->addProduct($this->parentProduct, $this->buyOptions);
            if (is_string($response)) {
                throw new LocalizedException(
                    __("Error Occurred while adding %1 :- %2", $sku, $response),
                    null,
                    105
                );
            }
            $this->itemsCount++;
            $this->itemQuantity += $itemData['qty'];
        } else {
            $message = __('i95dev_quote_product_valid') . "(" . $sku . ")";
            throw new LocalizedException(
                __($message),
                null,
                104
            );
        }
    }

    /**
     * Set custom price
     *
     * @param array $itemData
     * @param float $productPrice
     * @param array $buyOptionsList
     * @return array
     */
    public function setCustomPrice($itemData, $productPrice, $buyOptionsList)
    {
        $markdownPrice = (
            isset($itemData['transactionMarkdownPrice']) &&
            !empty($itemData['transactionMarkdownPrice']) ?? ''
        );
        $baseMarkdownPrice = ($itemData['markdownPrice'] ?? '');

        $price = 0;
        if (isset($itemData['transactionPrice'])) {
            if ($markdownPrice != '') {
                $price = $itemData['transactionPrice'] - $markdownPrice;
            } else {
                $price = $itemData['transactionPrice'] ?? 0;
            }
        }

        if ($baseMarkdownPrice != '') {
            $basePrice = $itemData['price'] - $baseMarkdownPrice;
        } else {
            $basePrice = $itemData['price'];
        }

        /* @updatedBy Ranjith Rasakatla. Fix for 22920201(Order level item price mapping
         * issue in Magento for the NAV created custom price/Tier price order)
         */
        if ($productPrice != $basePrice) {
            if (isset($itemData['transactionPrice'])) {
                $buyOptionsList['custom_price'] = $price;
            } else {
                $buyOptionsList['custom_price'] = $basePrice;
            }
        }
        $this->parentProduct->setPrice($basePrice);
        return $buyOptionsList;
    }

    /**
     * Gets product by product sku
     *
     * @param string $sku
     * @return array
     * @throws LocalizedException
     * @author Divya Koona. Removed API call and converted into interfaces
     */
    public function getProductBySku($sku)
    {
        try {
            $result = $this->productRepository->create()->get($sku);
            return $result->__toArray();
        } catch (NoSuchEntityException $ex) {
            return [];
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Validate request Order item data
     *
     * @param array $stringData
     * @throws LocalizedException
     * @author Divya Koona.
     */
    public function validateData($stringData)
    {
        $this->stringData = $stringData;
        $orderItemsArray = $this->dataHelper->getValueFromArray("orderItems", $this->stringData);
        $finalItems = [];
        if (!empty($orderItemsArray)) {
            foreach ($orderItemsArray as $itemData) {
                $this->validateSkuRequirement($itemData);
                $sku = $itemData['sku'];
                $item = $this->getProductBySku($sku);
                $this->validateItemsOfErpString($itemData, $item);
                $itemInformation = $itemData;
                if (key_exists($sku, $finalItems)) {
                    $existingItem = $finalItems[$sku];
                    if ($existingItem[self::PRICE] == $itemInformation[self::PRICE]) {
                        $itemInformation['qty'] += $existingItem['qty'];
                    } else {
                        $message = 'SKU ::' . $sku . " Having Different Price (" . $existingItem[self::PRICE] .
                            "," . $itemInformation[self::PRICE] . ")";
                        throw new LocalizedException(
                            __($message),
                            null,
                            104
                        );
                    }
                }
                $finalItems[$sku] = $itemInformation;
            }
        }
        $this->quoteItems = $finalItems;
    }

    /**
     * Validate Quote Item
     *
     * @param string $erpItemType
     * @param string $mageItemType
     * @param string $item
     */
    public function validateQuoteItem($erpItemType, $mageItemType, $item)
    {
        if ($erpItemType != '' && strtolower($erpItemType) != strtolower($mageItemType)) {
            throw new LocalizedException(
                __("i95dev_order_029"),
                null,
                104
            );
        }
        if (isset($item['status']) && $item['status'] !== '1') {
            throw new LocalizedException(
                __("i95dev_order_027"),
                null,
                104
            );
        } else {
            $beforeQuoteItemValidate = 'erpconnect_messagequeuetomagento_validatequoteitem';
            $this->eventManager->dispatch($beforeQuoteItemValidate, ['quoteObject' => $this, 'item' => $item]);
        }
    }

    /**
     * Check for sku existence in items array
     *
     * @param array $itemData
     * @return void
     */
    public function validateSkuRequirement($itemData)
    {
        if ($this->dataHelper->getValueFromArray('sku', $itemData) == '') {
            throw new LocalizedException(
                __("i95dev_order_032"),
                null,
                108
            );
        }
    }

    /**
     * Validate the items of ERP string in Magento
     *
     * @param array $itemData
     * @param array $item
     * @return void
     */
    public function validateItemsOfErpString($itemData, $item)
    {
        if (empty($item)) {
            throw new LocalizedException(
                __("i95dev_order_028"),
                null,
                108
            );
        } else {
            $erpItemType = $this->dataHelper->getValueFromArray("typeId", $itemData);
            $mageItemType = $this->dataHelper->getValueFromArray("type_id", $item);
            $this->validateQuoteItem($erpItemType, $mageItemType, $item);
        }
    }
}
