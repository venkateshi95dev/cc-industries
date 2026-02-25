<?php

namespace Crimson\AttributeSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Crimson\AttributeSearch\Model\AttributeSearchConfig;

class SearchDropdown extends Template
{

    public function __construct(
        Template\Context                              $context,
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly AttributeSearchConfig        $config,
        array                                         $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getAttributeOptions(string $attributeCode): array
    {
        try {
            $attribute = $this->attributeRepository->get('catalog_product', $attributeCode);
            $options = $attribute->getOptions();

            $values = [];
            foreach ($options as $option) {
                if ($option->getValue()) {
                    $values[] = [
                        'label' => $option->getLabel(),
                        'value' => $option->getValue()
                    ];
                }
            }

            return $values;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Fetches all attribute codes selected
     */
    public function getEnabledAttributes(): array
    {
        return $this->config->getEnabledAttributes();
    }

    /**
     * Gets the frontend label.
     */
    public function getAttributeLabel(string $attributeCode): string
    {
        try {
            $attribute = $this->attributeRepository->get('catalog_product', $attributeCode);
            return $attribute->getStoreLabel() ?: $attributeCode;
        } catch (\Exception $e) {
            return $attributeCode;
        }
    }

    /**
     * Gets the frontend dropdown title.
     */
    public function getDropdownTitle(string $attributeCode): string
    {
        return $this->config->getDropdownTitle() ?? $this->getAttributeLabel($attributeCode);
    }

    /**
     * Gets the frontend tooltip content.
     */
    public function getTooltipContent(): ?string
    {
        return $this->config->getTooltipContent();
    }

    /**
     * Checks if the module is enabled
     */
    public function isModuleEnabled(): bool
    {
        return $this->config->isModuleEnabled();
    }

    /**
     * Check if should use Fast Simon integration
     */
    public function isFastSimonEnabled(): bool
    {
        return $this->config->isFastSimonEnabled();
    }

    /**
     * Check if Fast Simon Instant Search is enabled
     */
    public function isFastSimonInstantSearchEnabled(): bool
    {
        return $this->config->isFastSimonInstantSearchEnabled();
    }
}
