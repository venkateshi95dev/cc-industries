<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use I95DevConnect\DiscountGroups\Helper\Data;

class ApplyDiscount
{
    /**
     * @var Customer
     */
    protected $customerModel;

    /**
     * @var Product
     */
    protected $productModel;

    /**
     * @var TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @var ProductFactory
     */
    protected $itemFactory;
    /**
     * @var DiscountcalculationFactory
     */
    protected $discountcalculationModel;

    /**
     * @var CustomerRepositoryInterface
     */
    public $customerRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * ApplyDiscount constructor
     *
     * @param ProductFactory $itemFactory
     * @param DiscountcalculationFactory $discountcalculationModel
     * @param TimezoneInterface $timezoneInterface
     * @param Customer $customerModel
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        ProductFactory $itemFactory,
        DiscountcalculationFactory $discountcalculationModel,
        TimezoneInterface $timezoneInterface,
        Customer $customerModel,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        CustomerRepositoryInterface $customerRepository,
        Data $helper
    ) {
        $this->itemFactory = $itemFactory;
        $this->discountcalculationModel = $discountcalculationModel;
        $this->timezoneInterface = $timezoneInterface;
        $this->customerModel = $customerModel;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
    }

    /**
     * Calculates discount percentage
     *
     * @param Object $item
     * @param int $customerId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDiscountPercentage($item, $customerId)
    {
        // enable discount groups module check
        if (!$this->helper->isDiscountGroupsEnabled()) {
            return ["baseDiscountAmount" => 0,"discountAmount" => 0];   
        }  
        if ($item === NULL) {
            return ["baseDiscountAmount" => 0,"discountAmount" => 0];   
        }
        $cartQty = $item->getQty();
        $customer = $this->getCustomerInfo($customerId);
        $targetCustomerId = $customer['targetCustomerId'];
        $cdg = $customer['cdg'];
        $sku = $item->getSku();
        $productDetails = $this->itemFactory->create()->loadByAttribute('sku', $sku);
        $idg = $productDetails->getItemDiscountGroup();
        $currentDate = $this->timezoneInterface->date()->format('Y-m-d');
        $discountData = $this->discountcalculationModel->create()->getCollection()
            ->addFieldtoFilter('qty', ["lteq" => $cartQty])
            ->addFieldtoFilter(['code', 'code'], [$sku, $idg])
            ->addFieldtoFilter(['sales_code', 'sales_code', 'sales_code'], ['', $cdg, $targetCustomerId])
            ->addFieldToFilter(
                'start_dt',
                [
                    ['lteq' => $currentDate],
                    ['start_dt', 'null' => '']
                ]
            )
            ->addFieldToFilter(
                'end_dt',
                [
                    ['gteq' => $currentDate],
                    ['end_dt', 'null' => '']
                ]
            );
        $discountData->getSelect()->reset(Select::COLUMNS)->columns('max(price) AS maxdiscount');
        $discountPercentage = $discountData->getData()[0]['maxdiscount'];
        $baseCustomerDiscountAmount =
            ($item->getBaseRowTotal() - $item->getBaseDiscountAmount()) * ($discountPercentage / 100);
        $store = $this->storeManager->getStore();
        $discountGroupAmount = $this->priceCurrency->convert($baseCustomerDiscountAmount, $store);
        return ["baseDiscountAmount" => $baseCustomerDiscountAmount,"discountAmount" => $discountGroupAmount];
    }

    /**
     * Get customer info from customer id
     *
     * @param int $customerId
     * @return array
     */
    public function getCustomerInfo($customerId)
    {
        $customer = $this->customerModel->load($customerId);
        $targetCustomerId = $customer->getTargetCustomerId();
        $cdg = $customer->getCustomerDiscountGroup();
        return ['targetCustomerId' => $targetCustomerId, 'cdg' => $cdg];
    }
}
