<?php

namespace Crimson\BuyNow\Model\Config\Source\Product;

use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;

class BrandItemAttributeOptions implements OptionSourceInterface
{

    CONST BRAND_ITEM_ATTRIBUTE_CODE = "brand_item";

    public function __construct(
        protected ProductAttributeOptionManagementInterface $productAttributeOptionManagementInterface,
    ) {}

    public function toOptionArray(): array
    {
        $options = [];
        $attributeOptions = $this->getAttributeOptions();
        foreach ($attributeOptions as $attrOption) {
            if (empty($attrOption['value']) || empty($attrOption['label'])) {
                continue;
            }

            $options = array_merge(
                $options,
                [
                    [
                        'value' => $attrOption['value'],
                        'label' => $attrOption['label']
                    ]
                ]
            );
        }

        return $options;
    }

    protected function getAttributeOptions(): array
    {
        try {
            return $this->productAttributeOptionManagementInterface->getItems(BrandItemAttributeOptions::BRAND_ITEM_ATTRIBUTE_CODE);
        } catch (\Exception $e) {
            return [];
        }
    }
}
