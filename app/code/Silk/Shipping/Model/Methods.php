<?php

namespace Silk\Shipping\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Methods extends AbstractModel implements IdentityInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     *  Cache tag
     */
    const CACHE_TAG = 'mappping_shipping_methods';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Silk\Shipping\Model\ResourceModel\Methods');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    public function loadMappingByStore($value, $field, $store_id)
    {
        //$store_id = is_null($store_id) ? $this->storeManager->getStore()->getStoreId() : $store_id;
        $item = $this->getCollection()->addFieldToFilter($field, $value)->addFieldToFilter('store_id', $store_id)->getFirstItem();
        if ($store_id != 0 && is_null($item->getId())) {
            $item = $this->getCollection()->addFieldToFilter($field, $value)->addFieldToFilter('store_id', 0)->getFirstItem();
        }

        return $item;
    }
    /**
     * Get used shipping methods
     *
     * @param  array | int $storeId
     * @return \Silk\Shipping\Model\ResourceModel\Methods\Collection
     */
    public function getUsedShippingMethodsCollection($storeId)
    {
        if (!$this->hasData('used_shipping_methods_cache') ||
               !$this->getData('used_shipping_methods_cache')) {
            $collection = $this->getCollection()
                               ->addStoreFilter($storeId)
                               ->addStatusFilter();

            if ($collection->getItems()) {
                $this->setData('used_shipping_methods_cache', $collection->getItems());
            }
        }

        return $this->getData('used_shipping_methods_cache');
    }

    /**
     * Get free shipping methods
     *
     * @param  int | array $storeId
     * @return array
     */
    public function getFreeShippingMethods($storeId)
    {
        $shippingMethods = $this->getUsedShippingMethodsCollection($storeId);
        $collection = [];
        if ($shippingMethods) {
            foreach ($shippingMethods as $shippingMethod) {
                if ($shippingMethod->getThnIsFreeshipping()) {
                    $collection[] = $shippingMethod;
                }
            }
        }

        return $collection ;
    }

    /**
     * Get costs shipping methods
     *
     * @param  int | array  $storeId
     * @return array
     */
    public function getCostsShippingMethods($storeId)
    {
        $shippingMethods = $this->getUsedShippingMethodsCollection($storeId);
        $collection = [];
        if ($shippingMethods) {
            foreach ($shippingMethods as $shippingMethod) {
                if (!$shippingMethod->getThnIsFreeshipping()) {
                    $collection[] = $shippingMethod;
                }
            }
        }

        return $collection ;
    }

    /**
     * Check if shipping methods are valid
     *
     * @param  array  $shippingMethods
     * @param  int $storeId
     * @return array
     */
    public function checkShippingMethodAreValid(array $inputShippingMethods, $storeId)
    {
        if (!$this->hasData('used_shipping_method_codes_cache') ||
               !$this->getData('used_shipping_method_codes_cache')) {
            $shippingMethods = $this->getUsedShippingMethodsCollection($storeId);
            $codes = [];
            if ($shippingMethods) {
                foreach ($shippingMethods as $shippingMethod) {
                    $codes[] = $shippingMethod->getThnMethodCode();
                }
            }

            $this->setData('used_shipping_method_codes_cache', $codes);
        }

        $codes = $this->getData('used_shipping_method_codes_cache');
        if ($codes) {
            foreach ($inputShippingMethods as $code => $shippingMethod) {
                $nameComponents = explode('_', $code);
                $carrierCode = array_shift($nameComponents);
                // carrier method code can contains more one name component
                $methodCode = implode('_', $nameComponents);
                if (!in_array($methodCode, $codes)) {
                    unset($inputShippingMethods[$code]);
                }
            }
        } else {
            return [];
        }

        return $inputShippingMethods;
    }
}
