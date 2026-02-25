<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Api\TaxClassRepositoryInterface;

/**
 * Class which Send Product Info to ERP.
 * @updatedBy Debashis S. Gopal. Changed Api code to Interface
 */
class Info
{
    public const WEIGTH = "weight";

    /**
     *
     * @var Manager
     */
    public $eventManager;

    /**
     *
     * @var Data
     */
    public $dataHelper;

    /**
     *
     * @var ProductRepositoryInterface
     */
    public $productRepository;

    /**
     *
     * @var StockRegistryInterface
     */
    public $stockRegistry;

    /**
     *
     * @var TaxClassRepositoryInterface
     */
    public $taxClassRepository;

    /**
     *
     * @var array
     * @updatedBy Debashis S. Gopal. Missing price field added.
     */
    public $fieldMapInfo = [
        'reference' => 'name',
        'name' => 'name',
        'sku' => 'sku',
        'typeId' => 'type_id',
        'createdAt' => 'created_at',
    ];

    /**
     *
     * @var ProductInterface
     */
    public $productInfo;

    /**
     *
     * @var array
     */
    public $productData;

    public const STORE_ID = 0;

    /**
     *
     * @param Manager $eventManager
     * @param Data $dataHelper
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param TaxClassRepositoryInterface $taxClassRepository
     */
    public function __construct(
        Manager $eventManager,
        Data $dataHelper,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        TaxClassRepositoryInterface $taxClassRepository
    ) {
        $this->eventManager = $eventManager;
        $this->dataHelper = $dataHelper;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->taxClassRepository = $taxClassRepository;
    }

    /**
     * Retrieves product based on product id
     *
     * @param int $productId
     * @return array
     */
    public function getInfo($productId)
    {
        $this->validateData($productId);
        $this->prepareErpData();
        $productInfoEvent = "erpconnect_forward_productinfo";
        $this->eventManager->dispatch($productInfoEvent, ['currentObject' => $this]);
        return $this->productData;
    }

    /**
     * Validate product exists or not and if exist initialize $this->productInfo
     *
     * @param int $productId
     * $createdBy Debashis S. Gopal
     * @throws LocalizedException
     */
    public function validateData($productId)
    {
        try {
            // @ Updated by Hrusikesh Manna Set Store Id 0 to get product information by id
            $this->productInfo = $this->productRepository->getById($productId, false, self::STORE_ID);
        } catch (NoSuchEntityException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Prepare product data array for ERP.
     *
     * @createdBy Debashis S. Gopal
     */
    public function prepareErpData()
    {
        $this->productData = $this->dataHelper->prepareInfoArray($this->fieldMapInfo, $this->productInfo->__toArray());
        $this->productData['price'] = (float)$this->productInfo['price'];
        $this->productData[self::WEIGTH] = isset($this->productInfo[self::WEIGTH]) ?
            (float)$this->productInfo[self::WEIGTH] : 0;
        $websiteIds = $this->productInfo->getExtensionAttributes()->getWebsiteIds();
        $this->productData['websiteIds'] = implode(",", $websiteIds);
        $description = $this->productInfo->getCustomAttribute('description');
        $shortDescription = $this->productInfo->getCustomAttribute('short_description');
        $cost = $this->productInfo->getCustomAttribute('cost');
        /** @updatedBy Debashis S. Gopal. Checking of taxClassId > 0 added. **/
        $taxClass = $this->productInfo->getCustomAttribute('tax_class_id');
        $this->productData['taxClassId'] = 0;
        if ($taxClass && $taxClass->getValue() > 0) {
            $this->productData['taxClassId'] = (int)$taxClass->getValue();
        }
        /** @updatedBy kavya.k. sending 0 instead of null if product has no cost . **/
        $this->productData['cost'] = isset($cost) ? $cost->getValue() : 0;
        $this->productData['description'] = isset($description) ?
                rtrim(str_replace("&nbsp;", "", strip_tags($description->getValue()))) : '';
        $this->productData['shortDescription'] = isset($shortDescription) ?
                rtrim(str_replace("&nbsp;", "", strip_tags($shortDescription->getValue()))) : '';
        $stockData = $this->stockRegistry->getStockItemBySku(urlencode($this->productInfo->getSku()));
        if (!empty($stockData)) {
            $this->productData['backorders'] = $stockData['backorders'];
        }
    }
}
