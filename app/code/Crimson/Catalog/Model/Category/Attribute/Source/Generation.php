<?php

namespace Crimson\Catalog\Model\Category\Attribute\Source;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Generation
 * @package Crimson\Catalog\Model\Category\Attribute\Source
 */
class Generation extends AbstractSource
{
    CONST ATTRIBUTE_GENERATION_CODE = 'generation';

    /**
     * Options array
     *
     * @var array
     */
    protected $_options = null;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * GenerationSource constructor.
     *
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository
    )
    {
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * @return array|null
     */
    public function getAllOptions(): ?array
    {
        try {
            if (!$this->_options) {
                $optionsArray = $this->productAttributeRepository->get(self::ATTRIBUTE_GENERATION_CODE)->getOptions();
                if (!empty($optionsArray)) {
                    $finalArrayTemp = [];
                    $finalArray = [];
                    foreach ($optionsArray as $option) {
                        $finalArrayTemp['value'] = $option->getValue();
                        $finalArrayTemp['label'] = $option->getLabel();
                        $finalArray[] = $finalArrayTemp;
                    }

                    $this->_options = $finalArray;
                }
                array_unshift($this->_options, ['value' => '', 'label' => __('Select a generation value')]);
            }
        } catch (\Exception $e) {
            return null;
        }

        return $this->_options;
    }
}
