<?php

namespace Crimson\Testimonial\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Category implements ArrayInterface
{
    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $_categoryFactory;


    /**
     * @param \Lof\Testimonial\Model\Category
     */
    public function __construct(
        \Crimson\Testimonial\Model\Category $categoryFactory
    ) {
        $this->_categoryFactory = $categoryFactory;
    }

    public function toOptionArray()
    {
        $options    = [];
        $collection = $this->_categoryFactory->getCollection();
        foreach ($collection as $_cat) {
            $options[] = [
                          'label' => $_cat->getName(),
                          'value' => $_cat->getCategoryId(),
                         ];
        }

        return $options;
    }
}
