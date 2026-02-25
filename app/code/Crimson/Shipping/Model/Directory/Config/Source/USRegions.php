<?php

namespace Crimson\Shipping\Model\Directory\Config\Source;

use Magento\Directory\Model\ResourceModel\Region\Collection;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;

/**
 * Class USRegions
 * @package Crimson\Shipping\Model\Directory\Config\Source
 */
class USRegions implements OptionSourceInterface
{

    /**
     * @var array
     */
    protected $_regions;

    /**
     * @var CollectionFactory
     */
    protected $_regCollectionFactory;

    public function __construct(
        CollectionFactory $regCollectionFactory
    ) {
        $this->_regCollectionFactory = $regCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if (!$this->_regions) {
            $regionsCollection = $this->_regCollectionFactory->create()
                ->addCountryFilter('US')
                ->load();
            foreach ($regionsCollection as $region) {
                $this->_regions[] = ['label' => $region->getDefaultName(), 'value' => $region->getCode()];
            }
        }

        return $this->_regions;
    }
}
