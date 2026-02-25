<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Observer;

use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\Generic;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Crimson\CorvetteCentralOSCO\UI\DataProvider\Product\Form\Modifier\Packages;

/**
 * Observer class to save product
 */
class ProductSaveBeforeObserver implements ObserverInterface
{
    public const MAGLOGNAME = 'MagentoToERP';
    public const ERPLOGNAME = 'ERPToMagento';
    public const I95EXC = 'i95devApiException';
    public const PRODUCT_SAVE_FLAG = 'product_save_processed';

    /**
     * @var Data
     */
    public $data;

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var ItemFactory
     */
    public $itemFactory;

    /**
     * @var Product
     */
    public $productFactory;

    /**
     * @var Generic
     */
    public $generic;

    /**
     * @var Http
     */
    public $request;

    /**
     * @var StockRegistryInterface
     */
    public $stockData;

    /**
     * @var Data
     */
    public $msgHelper;

    /**
     * ProductSaveBeforeObserver constructor.
     *
     * @param Data $data
     * @param Registry $coreRegistry
     * @param ProductFactory $itemFactory
     * @param StockRegistryInterface $stockData
     * @param Product $productFactory
     * @param Data $msgHelper
     * @param Http $request
     * @param Generic $generic
     */
    public function __construct( // NOSONAR
        Data $data,
        Registry $coreRegistry,
        ProductFactory $itemFactory,
        StockRegistryInterface $stockData,
        Product $productFactory,
        Data $msgHelper,
        Http $request,
        Generic $generic
    ) {
        $this->data = $data;
        $this->coreRegistry = $coreRegistry;
        $this->itemFactory = $itemFactory;
        $this->stockData = $stockData;
        $this->productFactory = $productFactory;
        $this->msgHelper = $msgHelper;
        $this->generic = $generic;
        $this->request = $request;
    }

    /**
     * Save i95Dev Custom attributes
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $is_enabled = $this->data->isEnabled();
        if (!$is_enabled) {
            return;
        }
        $product = $observer->getEvent()->getProduct();
        if ($this->data->getGlobalValue('i95_observer_skip') || $this->request->getParam('isI95DevRestReq') == 'true') {
            
            $existingPackagesData = $product->getOrigData(Packages::PRODUCT_ATTRIBUTE_CODE);
            $product->setData(Packages::PRODUCT_ATTRIBUTE_CODE, $existingPackagesData);
            return;
        }
        $supportedArray = $this->generic->getSupportedTypesForProduct();
        try {
            
            $productType = $product->getData("type_id");
            if (in_array($productType, $supportedArray)) {
                $productId = $this->productFactory->getIdBySku(trim($product->getData("sku")));
                if ($productId != "") {
                    $stockItem = $this->stockData->getStockItemBySku($product->getData("sku"));
                    if ($stockItem) {
                        $this->data->setGlobalValue('product_qty', $stockItem->getQty());
                        $this->data->setGlobalValue('product_managestock', $stockItem->getManageStock());
                    }
                }
            }
        } catch (LocalizedException $ex) {
            $this->data->logger->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, 'critical');
        }
    }
}
