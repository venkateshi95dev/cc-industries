<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\Coker\Block\Adminhtml\Form\Field;


use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * Class Categories
 */
class Categories extends Select
{
    protected $_categoryFactory;
    protected $_categoryCollectionFactory;

    public function __construct(
        Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->_categoryFactory = $categoryFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
    }

    private function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }
    private function _toArray()
    {
        $categories = $this->getCategoryCollection(true, false, false, false);

        $catagoryList = array();
        foreach ($categories as $category)
        {
            $catagoryList[$category->getEntityId()] = __($this->_getParentName($category->getPath()) . $category->getName());
        }

        return $catagoryList;
    }
    private function _getParentName($path = '')
    {
        $parentName = '';
        $rootCats = array(1,2);

        $catTree = explode("/", $path);
        // Deleting category itself
        array_pop($catTree);

        if($catTree && (count($catTree) > count($rootCats)))
        {
            foreach ($catTree as $catId)
            {
                if(!in_array($catId, $rootCats))
                {
                    $category = $this->_categoryFactory->create()->load($catId);
                    $categoryName = $category->getName();
                    $parentName .= $categoryName . ' -> ';
                }
            }
        }

        return $parentName;
    }
    private function toOptionArray()
    {
        $arr = $this->_toArray();
        $ret[] = [
            'value'=>'',
            'label'=>__('--- select ---')
        ];

        foreach ($arr as $key => $value)
        {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $ret;
    }
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $options = $this->toOptionArray();
            $this->setOptions($options);


        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
