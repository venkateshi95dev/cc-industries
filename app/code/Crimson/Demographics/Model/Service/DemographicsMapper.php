<?php

namespace Crimson\Demographics\Model\Service;

use Crimson\Demographics\Helper\Data;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class DemographicsMapper
 * @package Crimson\Demographics\Model\Service
 */
class DemographicsMapper
{

    /**
     * @var CollectionFactory
     */
    protected $_attributeOptionCollectionFactory;

    /**
     * @var Attribute
     */
    protected $_entityAttribute;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Data
     */
    protected $_helper;

    public function __construct(
        CollectionFactory $attributeOptionCollectionFactory,
        Attribute $entityAttribute,
        LoggerInterface $logger,
        Data $helper
    ) {
        $this->_attributeOptionCollectionFactory = $attributeOptionCollectionFactory;
        $this->_entityAttribute = $entityAttribute;
        $this->_logger = $logger;
        $this->_helper = $helper;
    }

    /**
     * @param null $ids
     * @param null $storeId
     * @return null|string
     */
    public function getDemographicsMappedByIds($ids = null, $storeId = null): ?string
    {
        if (!$ids) {
            return null;
        }

        if (is_string($ids)) {
            $optionIds = explode(',', $ids);
        } else if (is_array($ids)) {
            $optionIds = $ids;
        } else {
            return null;
        }

        try {
            $carDemosAttribute = $this->_entityAttribute->loadByCode('customer', 'car_demos');

            /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $collection */
            $collection = $this->_attributeOptionCollectionFactory->create()
                ->setPositionOrder('asc')
                ->addFieldToFilter('tdv.option_id', ['in' => $optionIds])
                ->setAttributeFilter($carDemosAttribute->getAttributeId())
                ->setStoreFilter()
                ->load();

            $options = [];
            foreach ($collection as $option) {
                foreach ($this->_helper->getMappingConfig($storeId) as $mapping) {
                    if (stripos($option->getDefaultValue(), $mapping['generation']) !== false) {
                        $options[] = $mapping['mach_value'];
                    }
                }
            }

            if ($options) {
                return implode('ü', $options);
            }

            return null;

        } catch (\Exception $e) {
            $this->_logger->critical('Unable to get demographics options: '.$e->getMessage());
            return null;
        }
    }
}
