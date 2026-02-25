<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/11/2019
 */
namespace Crimson\Brand\Model\Config\Source;

use \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use \Magento\Framework\Data\Collection;
use \Crimson\Brand\Model\ResourceModel\Brand\CollectionFactory;

class BrandOptions extends AbstractSource
{
    /**
     * @var CollectionFactory
     */
    protected $_brandCollectionFactory;

    /**
     * BrandOptions constructor.
     * @param CollectionFactory $brandCollectionFactory
     */
    public function __construct(
        CollectionFactory $brandCollectionFactory
    ) {
        $this->_brandCollectionFactory = $brandCollectionFactory;
    }

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $brandCollection = $this->_brandCollectionFactory->create();
        $brandOptions = [];
        if ($brandCollection->getSize()) {
            array_push($brandOptions, ['value' => '', 'label' => ' ']);
            $brandCollection->setOrder('name', Collection::SORT_ORDER_ASC);
            foreach ($brandCollection as $brand) {
                /** @var \Crimson\Brand\Api\Data\BrandInterface $brand */
                array_push($brandOptions, ['value' => $brand->getId(), 'label' => $brand->getName()]);
            }
        } else {
            array_push($brandOptions, ['value' => '', 'label' => ' ']);
        }
        if (!$this->_options) {
            $this->_options = $brandOptions;
        }
        return $this->_options;
    }
}
