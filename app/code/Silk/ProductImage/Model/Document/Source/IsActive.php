<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Model\Document\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class IsActive implements OptionSourceInterface
{
    /**
     * @var \Silk\ProductImage\Model\Document
     */
    protected $cmsDocument;

    /**
     * Constructor
     *
     * @param \Silk\ProductImage\Model\Document $cmsDocument
     */
    public function __construct(\Silk\ProductImage\Model\Document $cmsDocument)
    {
        $this->cmsDocument = $cmsDocument;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->cmsDocument->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
