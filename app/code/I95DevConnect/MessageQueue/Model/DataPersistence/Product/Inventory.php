<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Product;

use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product\Reverse\Stock;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use Magento\CatalogInventory\Api\StockItemRepositoryInterfaceFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;

use function Magento\Framework\Exception\LocalizedException;

/**
 * Description of Inventory
 */
class Inventory
{
    /**
     * @var StockItemRepositoryInterfaceFactory
     */
    public $stockItemRepo;

    /**
     * @var Product\Reverse\Stock
     */
    public $productStock;

    /**
     * @var AbstractProduct
     */
    public $abstractProduct;

    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var Validate
     */
    public $validate;

    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var StockRegistryInterface
     */
    public $stockRegistry;

    /**
     * @var string[]
     */
    public $validateFields = [
        'sku' => 'i95dev_prod_005',
        'qty' => 'i95dev_prod_020',
    ];

    /**
     * @var string
     */
    public $stringData;

    /**
     * @var int
     */
    public $productId;

    /**
     * @var string
     */
    public $sku;

    /**
     * Inventory constructor.
     *
     * @param StockItemRepositoryInterfaceFactory $stockItemRepo
     * @param Product\Reverse\Stock $productStock
     * @param AbstractProduct $abstractProduct
     * @param Manager $eventManager
     * @param Validate $validate
     * @param AbstractDataPersistence $abstractDataPersistence
     * @param Data $dataHelper
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct( // NOSONAR
        StockItemRepositoryInterfaceFactory $stockItemRepo,
        Stock $productStock,
        AbstractProduct $abstractProduct,
        Manager $eventManager,
        Validate $validate,
        AbstractDataPersistence $abstractDataPersistence,
        Data $dataHelper,
        StockRegistryInterface $stockRegistry
    ) {
        $this->stockItemRepo = $stockItemRepo;
        $this->productStock = $productStock;
        $this->abstractProduct = $abstractProduct;
        $this->abstractDataPersistence = $abstractDataPersistence;
        $this->dataHelper = $dataHelper;
        $this->validate = $validate;
        $this->eventManager = $eventManager;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Create Inventory
     *
     * @param string $stringData
     * @param string $entityCode
     *
     * @return I95DevResponseInterface
     */
    public function create($stringData, $entityCode)
    {
        $this->stringData = $stringData;

        try {
            $this->sku = $this->dataHelper->getValueFromArray("sku", $this->stringData);
            $this->validate->validateFields = $this->validateFields;
            $this->validateData();

            $stockItem = $this->productStock->setStockInformation(
                $this->stringData,
                true
            );

            $stockItem = $this->stockRegistry->updateStockItemBySku($this->sku, $stockItem);
            if (!(is_numeric($stockItem))) {
                throw LocalizedException(__("inventory_sync_error"));
            }

            $this->dataHelper->unsetGlobalValue('i95_observer_skip');
            $aftereventname = 'erpconnect_messagequeuetomagento_aftersave_' . $entityCode;
            $this->eventManager->dispatch($aftereventname, ['currentObject' => $this]);

            return $this->abstractDataPersistence->setResponse(
                Data::SUCCESS,
                "Record Successfully Synced",
                $this->productId
            );
        } catch (LocalizedException $ex) {
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                $ex->getMessage(),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Validate data
     *
     * @throws LocalizedException
     */
    public function validateData()
    {
        $this->validate->validateData($this->stringData);
        $this->productId = $this->abstractProduct->getProductPrimaryId($this->sku);

        if ($this->productId < 1) {
            $message = "Sku not Exists::" . $this->sku;
            throw new LocalizedException(
                __($message),
                null,
                108
            );
        }
    }
}
