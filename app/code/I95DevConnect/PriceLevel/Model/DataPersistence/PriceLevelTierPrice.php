<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Model\DataPersistence;

use Exception;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\CustomerGroup\Create as CustomerGroupCreate;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\AbstractProduct;
use I95DevConnect\PriceLevel\Model\ItemPriceListDataFactory;
use I95DevConnect\TierPrice\Model\DataPersistence\TierPrice;
use Magento\Catalog\Api\Data\TierPriceInterfaceFactory;
use Magento\Catalog\Api\TierPriceStorageInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;

/**
 * This class over rides \I95DevConnect\TierPrice\Model\DataPersistence\TierPrice,
 * It syncs erp tier prices to i9dev_tier_rices.(ERP Specific)
 * @updatedBy Debashis S. Gopal
 */
class PriceLevelTierPrice extends TierPrice
{
    /**
     *
     * @var ItemPriceListDataFactory
     */
    public $i95devTierPriceFactory;

    /**
     *
     * @var PriceLevel
     */
    public $priceLevelCreate;

    /**
     *
     * @var string
     */
    public $sku;

    /**
     *
     * @var int
     */
    public $productId;

    /**
     *
     * @var obj
     */
    public $tierPrice;

    /**
     * @var AbstractProduct
     */
    public $abstractProduct;

    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;

    /**
     * @var GroupRepositoryInterface
     */
    public $groupRepository;

    /**
     * @param Data $dataHelper
     * @param CustomerGroupCreate $customerGroupCreate
     * @param TierPriceStorageInterfaceFactory $tierPriceStorage
     * @param TierPriceInterfaceFactory $tierPriceFactory
     * @param AbstractProduct $abstractProduct
     * @param AbstractDataPersistence $abstractDataPersistence
     * @param LoggerInterface $logger
     * @param ItemPriceListDataFactory $i95devTierPriceFactory
     * @param PriceLevel $priceLevelCreate
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct( // NOSONAR
        Data $dataHelper,
        CustomerGroupCreate $customerGroupCreate,
        TierPriceStorageInterfaceFactory $tierPriceStorage,
        TierPriceInterfaceFactory $tierPriceFactory,
        AbstractProduct $abstractProduct,
        AbstractDataPersistence $abstractDataPersistence,
        LoggerInterface $logger,
        ItemPriceListDataFactory $i95devTierPriceFactory,
        PriceLevel $priceLevelCreate,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->i95devTierPriceFactory = $i95devTierPriceFactory;
        $this->priceLevelCreate = $priceLevelCreate;
        parent::__construct(
            $dataHelper,
            $customerGroupCreate,
            $tierPriceStorage,
            $tierPriceFactory,
            $abstractProduct,
            $abstractDataPersistence,
            $logger,
            $searchCriteriaBuilder,
            $groupRepository
        );
    }

    /**
     * Create i95Dev ItemPriceLis
     *
     * @param string $stringData
     * @return I95DevResponseInterface
     * @throws Exception
     */
    public function create($stringData)
    {
        try {
            $this->stringData = $stringData;
            $this->validateData();
            $tiers = $this->dataHelper->getValueFromArray("tierPrices", $this->stringData);
            $this->deleteExistingTiers();
            if (!empty($tiers)) {
                foreach ($tiers as $tierData) {
                    $qty = $this->dataHelper->getValueFromArray("minQty", $tierData);
                    if (!$qty || $qty === 0) {
                        $qty = 1;
                    }
                    if (isset($qty)) {
                        $this->saveTierPrice($tierData, $qty);
                    } else {
                        return $this->abstractDataPersistence->setResponse(
                            Data::ERROR,
                            "minQty should not be Empty",
                            $this->productId
                        );
                    }
                }
            }
            return $this->abstractDataPersistence->setResponse(
                Data::SUCCESS,
                "Record Successfully Synced",
                $this->productId
            );
        } catch (LocalizedException $ex) {
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __($ex->getMessage())
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function validateData()
    {
        if (!$this->dataHelper->isEnabled()) {
            throw new LocalizedException(__("i95dev_gen_001"));
        }
        $this->sku = $this->dataHelper->getValueFromArray("targetId", $this->stringData);
        $this->productId = $this->abstractProduct->getProductPrimaryId($this->sku);

        if ((int)$this->productId < 1) {
            throw new LocalizedException(__('i95dev_prod_016'));
        }
        $tiers = $this->dataHelper->getValueFromArray("tierPrices", $this->stringData);
    }

    /**
     * Save tier prices
     *
     * @param array $tierData
     * @param int $qty
     * @throws Exception
     */
    public function saveTierPrice($tierData, $qty)
    {
        $price = $this->dataHelper->getValueFromArray("price", $tierData);
        $originalFromDate = $this->dataHelper->getValueFromArray("fromDate", $tierData);
        $salesType = $this->dataHelper->getValueFromArray("salesType", $tierData);
        $salesCode = $this->dataHelper->getValueFromArray("salesCode", $tierData);
        $uom = $this->dataHelper->getValueFromArray("uofM", $tierData);
        if (isset($originalFromDate) && $originalFromDate !== '') {
            $fromDate = date("Y-m-d", strtotime($originalFromDate));
        } else {
            $fromDate = null;
        }
        $originalToDate = $this->dataHelper->getValueFromArray("toDate", $tierData);
        if (isset($originalToDate) && $originalToDate !== '') {
            $toDate = date("Y-m-d", strtotime($originalToDate));
        } else {
            $toDate = null;
        }
        $this->tierPrice = $this->i95devTierPriceFactory->create();
        $this->tierPrice->setData("sku", $this->sku);
        $this->tierPrice->setData("sales_type", $salesType);
        $this->tierPrice->setData("sales_code", $salesCode);
        $this->tierPrice->setData("from_date", $fromDate);
        $this->tierPrice->setData("to_date", $toDate);
        $this->tierPrice->setData("qty", $qty);
        $this->tierPrice->setData("uom", $uom);
        $this->tierPrice->setData("price", $price);
        $this->tierPrice->save();
    }

    /**
     * Deletes existing tiers of an item
     */
    public function deleteExistingTiers()
    {
        $existingTierPriceCollectionList = $this->i95devTierPriceFactory->create()->getCollection()
            ->addFieldToFilter('sku', $this->sku);
        if ($existingTierPriceCollectionList->getSize() > 0) {
            foreach ($existingTierPriceCollectionList as $eachTier) {
                $eachTier->delete();
            }
        }
    }
}
