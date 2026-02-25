<?php
namespace Silk\CKDocument\Model\CKDocument\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class IsActive implements OptionSourceInterface
{
    /**
     * @var \Silk\CKDocument\Model\CKDocument
     */
    protected $cmsCKDocument;

    /**
     * Constructor
     *
     * @param \Silk\CKDocument\Model\CKDocument $cmsCKDocument
     */
    public function __construct(\Silk\CKDocument\Model\CKDocument $cmsCKDocument)
    {
        $this->cmsCKDocument = $cmsCKDocument;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->cmsCKDocument->getAvailableStatuses();
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
