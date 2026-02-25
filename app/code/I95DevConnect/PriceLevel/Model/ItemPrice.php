<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Model;

use DateTime;
use I95DevConnect\PriceLevel\Helper\Data;
use I95DevConnect\PriceLevel\Model\ResourceModel\ItemPriceListData;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Model Class for Item Price
 */
class ItemPrice extends AbstractModel
{
    public const CUSTOMER = 0;
    public const CUSTOMER_PRICE_GROUP = 1;
    public const ALL_CUSTOMERS = 2;

    /**
     * @var array
     */
    private $pricesBySku;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var Data
     */
    public $data;

    /**
     * Customer object
     *
     * @var CustomerFactory
     */
    public $customerFactory;

    /**
     *
     * @var ItemPriceListDataFactory
     */
    public $priceListFactory;

    /**
     *

     * @var PriceLevelDataFactory
     */
    public $priceLevelFactory;

    /**
     *
     * @var string
     */
    public $sku;

    /**
     *
     * @var int
     */
    public $qty;

    /**
     * @var ResourceModel\ItemPriceListData
     */
    public $itemPriceResource;

    /**
     * @var TimezoneInterface
     */
    public $timezoneInterface;
    /**
     *
     * @param LoggerInterface $logger
     * @param Data $data
     * @param CustomerFactory $customerFactory
     * @param ItemPriceListDataFactory $priceListFactory
     * @param PriceLevelDataFactory $priceLevelFactory
     * @param ItemPriceListData $itemPriceResource
     * @param Context $context
     * @param Registry $registry
     * @param TimezoneInterface $timezoneInterface
     */
    public function __construct( // NOSONAR
        LoggerInterface $logger,
        Data $data,
        CustomerFactory $customerFactory,
        ItemPriceListDataFactory $priceListFactory,
        PriceLevelDataFactory $priceLevelFactory,
        ItemPriceListData $itemPriceResource,
        Context $context,
        Registry $registry,
        TimezoneInterface $timezoneInterface
    ) {
        $this->logger = $logger;
        $this->data = $data;
        $this->customerFactory = $customerFactory;
        $this->priceListFactory = $priceListFactory;
        $this->priceLevelFactory = $priceLevelFactory;
        $this->itemPriceResource = $itemPriceResource;
        $this->timezoneInterface = $timezoneInterface;
        parent::__construct($context, $registry);
    }

    /**
     * Get product price level price
     *
     * @updatedBy Debashis S. Gopal. Method definition changed.
     * Directly getting $product instead or loading it using repository.
     *
     * If we load it here it is going to recursive loop for
     * products with special prices(Addressed inMagento version 2.2.8)
     *
     * @param \Magento\Catalog\Api\Data\Productinterface $product
     * @param int $customerId
     * @param int $qty
     * @return float|int $finalPrice
     */
    public function getItemFinalPrice($product, $customerId, $qty)
    {
        $this->sku = $product->getSku();
        $customer = $this->getCustomer($customerId);

        $targetCustomerId = isset($customer['target_customer_id']) ? $customer['target_customer_id'] : '';
        $customerPriceLevel = isset($customer['pricelevel']) ? $customer['pricelevel'] : '';
        return $this->getItemTierPrice($targetCustomerId, $customerPriceLevel, $this->sku, $qty);
    }

    /**
     * Get customer from customer id
     *
     * @param int $customerId
     * @return string $priceLevel
     */
    public function getCustomer($customerId)
    {
        $customer = $this->customerFactory->create()->load($customerId);
        return ['target_customer_id' => $customer->getTargetCustomerId(), 'pricelevel' => $customer->getPricelevel()];
    }

    /**
     * Get product's price levels list
     *
     * @param string $targetCustomerId
     * @param string $customerPriceLevel
     * @param string $sku
     * @param int $qty
     * @return float|int
     */
    public function getItemTierPrice($targetCustomerId, $customerPriceLevel, $sku, $qty)
    {
        $priceList = $this->getItemPriceList($targetCustomerId, $customerPriceLevel, $sku, $qty);
        $priceList->getSelect()->reset(Select::COLUMNS)->columns('min(price) AS min_price');
        return $priceList->getData()[0]['min_price'];
    }

    /**
     * Get item price list
     *
     * @param string $targetCustomerId
     * @param string $customerPriceLevel
     * @param string $sku
     * @param int|null $qty
     * @return mixed
     */
    public function getItemPriceList($targetCustomerId, $customerPriceLevel, $sku, $qty = null)
    {
        $todayDate = $this->timezoneInterface->date()->format('Y-m-d');
        $parentSkus = $this->data->getParentIdsByChildSku($sku);
        if (!empty($parentSkus)) {
            $parentSku = isset($parentSkus[0]) ? $parentSkus[0] : '';
            $priceList = $this->priceListFactory->create()->getCollection()
                ->addFieldToFilter('sku', ['in' => [$parentSku, $sku]]);
        } else {
            $priceList = $this->priceListFactory->create()->getCollection()
                ->addFieldToFilter('sku', $sku);
        }
        $priceList->addFieldToFilter(
            'sales_code',
            [
                    ['eq' => $customerPriceLevel],
                    ['eq' => $targetCustomerId],
                    ['sales_code', 'null' => '']
                ]
        )
            ->addFieldToFilter(
                'from_date',
                [
                    ['lteq' => $todayDate],
                    ['from_date', 'null' => '']
                ]
            )
            ->addFieldToFilter(
                'to_date',
                [
                    ['gteq' => $todayDate],
                    ['to_date', 'null' => '']
                ]
            );
        if ($qty != null) {
            return $priceList->addFieldtoFilter('qty', ["lteq" => $qty]);
        } else {
            return $priceList;
        }
    }

    /**
     * Get product's price levels list
     *
     * @param string $customerId
     * @param string $SKU
     * @return array
     */
    public function getItemPriceListDisplay($customerId, $SKU)
    {
        $customer = $this->getCustomer($customerId);
        $targetCustomerId = isset($customer['target_customer_id']) ? $customer['target_customer_id'] : '';

        $customerPriceLevel = isset($customer['pricelevel']) ? $customer['pricelevel'] : '';
        $now = new DateTime();
        $todayDate = $now->format('Y-m-d');

        $result = [];
        foreach ($this->_getPriceBySku($SKU) as $price) {
            $endDate = new DateTime($price['to_date'] ?? '');
            $toDate = $endDate->format('Y-m-d');
            if ((self::CUSTOMER == $price['sales_type'] &&
                strcasecmp(trim($targetCustomerId), $price['sales_code']) == 0
                && ($price['to_date'] == '' || $toDate > $todayDate)) ||
                (self::CUSTOMER_PRICE_GROUP == $price['sales_type'] &&
                    strcasecmp(trim($customerPriceLevel), $price['sales_code']) == 0
                    && ($price['to_date'] == '' || $toDate > $todayDate)) ||
                (self::ALL_CUSTOMERS == $price['sales_type'] &&
                    $price['sales_code'] == null
                    && ($price['to_date'] == '' || $toDate > $todayDate)) &&
                ((int)$price['qty'] != 1)
            ) {
                    $result[] = $price;
            }
        }
        return $result;
    }

    /**
     * Get Prices by SKU
     *
     * @param string $sku
     * @return array
     * @throws LocalizedException
     */
    private function _getPriceBySku($sku)// phpcs:ignore
    {
        $sku = strtolower($sku);

        if (null === $this->pricesBySku) {
            $this->pricesBySku = $this->itemPriceResource->getRowsBySku();
        }

        return $this->pricesBySku[$sku] ?? [];
    }
}
