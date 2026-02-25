<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_TierPrice
 */

namespace I95DevConnect\TierPrice\Model\DataPersistence;

use Exception;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\CustomerGroup\Create as CustomerGroupCreate;
use Magento\Catalog\Api\TierPriceStorageInterfaceFactory;
use Magento\Catalog\Api\Data\TierPriceInterfaceFactory;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\AbstractProduct;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;

/**
 * To sync tier prices
 */
class TierPrice
{
    /**
     * @var $magentoPriceLevelModel
     */
    public $magentoPriceLevelModel;

    /**
     *
     * @var Data
     */
    public $dataHelper;

    /**
     *
     * @var CustomerGroupCreate
     */
    public $customerGroupCreate;

    /**
     *
     * @var TierPriceStorageInterfaceFactory
     */
    public $tierPriceStorage;

    /**
     *
     * @var TierPriceInterfaceFactory
     */
    public $tierPriceFactory;

    /**
     *
     * @var LoggerInterface
     */
    public $logger;

    /**
     *
     * @var string
     */
    public $sku = null;

    /**
     *
     * @var int
     */
    public $productId = null;

    /**
     *
     * @var array
     */
    public $stringData;

    /**
     *
     * @var array
     */
    public $tierPriceData = [];

    /**
     *
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     *
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     *
     * @var AbstractProduct
     */
    public $abstractProduct;

    /**
     *
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     *
     * @param Data                             $dataHelper
     * @param CustomerGroupCreate              $customerGroupCreate
     * @param TierPriceStorageInterfaceFactory $tierPriceStorage
     * @param TierPriceInterfaceFactory        $tierPriceFactory
     * @param AbstractProduct                  $abstractProduct
     * @param AbstractDataPersistence          $abstractDataPersistence
     * @param LoggerInterface                  $logger
     * @param SearchCriteriaBuilder            $searchCriteriaBuilder
     * @param GroupRepositoryInterface         $groupRepository
     */
    public function __construct( // NOSONAR
        Data $dataHelper,
        CustomerGroupCreate $customerGroupCreate,
        TierPriceStorageInterfaceFactory $tierPriceStorage,
        TierPriceInterfaceFactory $tierPriceFactory,
        AbstractProduct $abstractProduct,
        AbstractDataPersistence $abstractDataPersistence,
        LoggerInterface $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->dataHelper = $dataHelper;
        $this->customerGroupCreate = $customerGroupCreate;
        $this->tierPriceStorage = $tierPriceStorage;
        $this->tierPriceFactory = $tierPriceFactory;
        $this->abstractProduct = $abstractProduct;
        $this->abstractDataPersistence = $abstractDataPersistence;
        $this->logger = $logger;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Create tier prices
     *
     * @param  string $stringData
     * @return I95DevResponseInterface
     */
    public function create($stringData)
    {
        try {
            $this->stringData = $stringData;
            $this->validateData();
            $tierPrices = $this->prepareTierPriceData();
            $result = $this->tierPriceStorage->create()->replace($tierPrices);
            if (is_array($result) && empty($result)) {
                $this->productId = $this->abstractProduct->getProductPrimaryId($this->sku);
            } else {
                throw new LocalizedException(
                    __(
                        json_encode(
                            $result,
                            JSON_UNESCAPED_UNICODE
                        )
                    )
                );
            }
        } catch (CouldNotSaveException | LocalizedException | Exception $ex) {
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __($ex->getMessage())
            );
        }

        return $this->abstractDataPersistence->setResponse(
            Data::SUCCESS,
            "Record Successfully Synced",
            $this->productId
        );
    }

    /**
     * To validate ERP data
     *
     * @throws Exception
     */
    public function validateData()
    {
        if (!$this->dataHelper->isEnabled()) {
            throw new LocalizedException(__("i95dev_gen_001"));
        }
        $this->sku = $this->dataHelper->getValueFromArray("targetId", $this->stringData);
        $this->productId = $this->abstractProduct->getProductPrimaryId($this->sku);
        if ($this->productId < 1) {
            throw new LocalizedException(__('i95dev_prod_016'));
        }
        $tiers = $this->dataHelper->getValueFromArray("tierPrices", $this->stringData);
        if (!empty($tiers)) {
            $this->validateCustomerGroup($tiers);
        } else {
            throw new LocalizedException(__("tier_price_01"));
        }
    }

    /**
     * Checks customer group is exists in i95dev_customer_group table, If not create a new customer Group.
     *
     * @createdBy Debashis S. Gopal
     *
     * @param array $tiers
     *
     * @throws LocalizedException
     * @throws Exception
     */
    public function validateCustomerGroup($tiers)
    {
        foreach ($tiers as $tierData) {
            $targetGroupCode = $this->dataHelper->getValueFromArray("priceLevelKey", $tierData);
            if (!isset($targetGroupCode) || $targetGroupCode == '') {
                throw new LocalizedException(__("tier_price_02"));
            }
            $customerGroup = $this->customerGroupCreate->getI95DevCustomerGroupByTargetId($targetGroupCode);
            if (empty($customerGroup)) {
                $groupData = $this->dataHelper->checkInDefaultCustomerGroups($targetGroupCode);
                if (!empty($groupData)) {
                    continue;
                }
                $groupData = ['targetId' => $targetGroupCode];
                $result = $this->customerGroupCreate->createCustomerGroup($groupData, 'CustomerGroup');
                if (!$result->resultData) {
                    $this->logger->createLog(
                        __METHOD__,
                        "Error Occured while creating new customer group",
                        LoggerInterface::I95EXC,
                        'critical'
                    );
                }
            }
        }
    }

    /**
     * Prepare Tier price data for Api
     *
     * @createdBy Debashis S. Gopal
     */
    public function prepareTierPriceData()
    {
        $tiers = $this->dataHelper->getValueFromArray("tierPrices", $this->stringData);
        $i = 0;
        $tierPrices = [];
        if (!empty($tiers)) {
            foreach ($tiers as $tier) {
                $minQty = $this->dataHelper->getValueFromArray("minQty", $tier);
                if (!$minQty || $minQty === 0) {
                    $minQty = 1;
                }
                $tierPrice = $this->tierPriceFactory->create();
                $tierPrice->setPrice($this->dataHelper->getValueFromArray("price", $tier))
                    ->setPriceType("fixed")
                    ->setWebsiteId(0)
                    ->setSku($this->sku)
                    ->setCustomerGroup($this->dataHelper->getValueFromArray("priceLevelKey", $tier))
                    ->setQuantity($minQty);
                $tierPrices[$i] = $tierPrice;
                $i++;
            }
        }
        return $tierPrices;
    }
}
