<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/17/2019
 */
namespace Crimson\Brand\Plugin\Model\Rule\Condition;

use Magento\Framework\Model\AbstractModel;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductPlugin
 * @package Crimson\Brand\Plugin\Model\Rule\Condition
 */
class ProductPlugin
{
    const BRANDS_ATTRIBUTE_CODE = 'brands';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * ProductPlugin constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    public function aroundValidate(Product $subject, callable $proceed, AbstractModel $model)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $model->getProduct();

        if ($subject->getAttribute() == self::BRANDS_ATTRIBUTE_CODE && !$product->hasData($subject->getAttribute())) {
            $brandValue = $product->getResource()->getAttributeRawValue(
                $product->getId(),
                $subject->getAttribute(),
                $this->_storeManager->getStore()->getId()
            );
            $product->setData(self::BRANDS_ATTRIBUTE_CODE,$brandValue);
        }
        return $proceed($model);
    }
}
