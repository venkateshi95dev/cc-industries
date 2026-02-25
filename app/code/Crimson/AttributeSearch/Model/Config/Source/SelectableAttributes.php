<?php
namespace Crimson\AttributeSearch\Model\Config\Source;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class SelectableAttributes implements OptionSourceInterface
{

    public function __construct(
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder

    ) {
    }

    /**
     *
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('frontend_input', ['select', 'multiselect'], 'in')
            ->create();
        try {
            $attributeList = $this->attributeRepository->getList('catalog_product', $searchCriteria);
            $attributes = $attributeList->getItems();

            foreach ($attributes as $attribute) {
                $frontendInput = $attribute->getFrontendInput();
                if (in_array($frontendInput, ['select', 'multiselect'], true)) {
                    $attributeCode = $attribute->getAttributeCode();
                    $attributeLabel = $attribute->getStoreLabel() ?: $attributeCode;
                    $options[] = [
                        'value' => $attributeCode,
                        'label' => $attributeLabel
                    ];
                }
            }
        } catch (\Exception $e) {
        }

        return $options;
    }
}
