<?php

namespace Crimson\CokerWV\ViewModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @package Crimson\CokerWV\ViewModel
 */
class CustomScript implements ArgumentInterface
{
    public function __construct(
        private readonly CategoryFactory $categoryFactory,
        private readonly \Magento\Catalog\Helper\Data $catalogHelper,
        private readonly \Magento\Framework\App\Request\Http $request,
        private readonly \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository,
        private readonly \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
    ) {
    }

    public function getCurrentProduct()
    {
        return $this->catalogHelper->getProduct();
    }
    public function getCurrentCategory()
    {
        return $this->catalogHelper->getCategory();
    }
    public function getPageType()
    {
        return $this->request->getFullActionName();
    }
    public function isHotjarActive()
    {
        return (bool)$this->scopeConfig->getValue("finderattribute/hotjar/is_active", ScopeInterface::SCOPE_STORE);
    }

    public function getProductAttributeSetName($_product)
    {
        $productAttributeSetName = '';
        if (($productAttributeSetId = $_product->getAttributeSetId())
            && ($productAttributeSet = $this->attributeSetRepository->get($productAttributeSetId))) {
            $productAttributeSetName = $productAttributeSet->getAttributeSetName();
        }
        return $productAttributeSetName;
    }

    public function getEtpProduct($_product)
    {
        $etp_eligible = $_product->getAttributeText('etp_eligible');
        $etp = array_column($this->getProductEP('etp'), null, 'etp_eligible');
        if ($etp_eligible && in_array($etp_eligible, array_keys($etp))) {
            try {
                $product = $this->productRepository->get($etp_eligible);
                if($product->getStatus() == 1)
                    return $product;
            }catch (\Throwable $e)
            {
                return null;
            }
        }
        return null;
    }

    public function getEwpProduct($_product)
    {
        $ewp_eligible = $_product->getAttributeText('ewp_eligible');
        $categoryIds = $_product->getCategoryIds();
        $isRim = in_array($this->getRimCategoryId(), $categoryIds);
        if($isRim)
            return null;
        $ewp = array_column($this->getProductEP('ewp'), null, 'ewp');
        if ($ewp_eligible && in_array($ewp_eligible, array_keys($ewp))) {
            try {
                $product = $this->productRepository->get($ewp_eligible);
                if($product->getStatus() == 1)
                    return $product;
            }catch (\Throwable $e)
            {
                return null;
            }
        }
        return null;
    }

    public function getRimCategoryId()
    {
        $rimCat = $this->categoryFactory->create();
        $rimCat = $rimCat->loadByAttribute('url_path', 'wheels/rims');
        return $rimCat->getId();
    }

    public function getProductEP($type)
    {
        $json = $this->scopeConfig->getValue('product_modal/product/' . $type, ScopeInterface::SCOPE_STORE);
        if ($array = json_decode($json, true)) {
            return $array;
        }
    }

    public function getProductEwpAllowedAttributeSetName()
    {
        return $this->scopeConfig->getValue('product_modal/product/ewp_allowed_attribute_set_name', ScopeInterface::SCOPE_STORE);
    }
    public function canShowPhoneCallOut($category)
    {
        $popActive = $this->scopeConfig->getValue('product_modal/condition/is_active', ScopeInterface::SCOPE_STORE);
        if(!$popActive)
            return false;
        $excludeString = $this->scopeConfig->getValue('modal/condition/category', ScopeInterface::SCOPE_STORE);
        if($excludeString && $category){
            $categoryId = $category->getId();
            $excludeIds = $this->jsonSerializer->unserialize($excludeString);
            return !in_array($categoryId, array_values($excludeIds));
        }
        return true;
    }
}
