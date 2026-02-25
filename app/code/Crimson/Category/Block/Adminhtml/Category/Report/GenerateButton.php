<?php

namespace Crimson\Category\Block\Adminhtml\Category\Report;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Category\AbstractCategory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Tree;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class GenerateButton extends AbstractCategory implements ButtonProviderInterface
{


    public function __construct(
        Context $context,
        Tree $categoryTree,
        Registry $registry,
        CategoryFactory $categoryFactory,
        protected UrlInterface $urlBuilder,
        array $data = []
    )
    {
        parent::__construct($context, $categoryTree, $registry, $categoryFactory, $data);
    }

    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Categories by Generations Report'),
            'on_click' => sprintf(
                "location.href = '%s';",
                $this->urlBuilder->getUrl('category_report/report/generate', [])
            ),
            'class' => 'save primary',
            'sort_order' => 1
        ];
    }
}
