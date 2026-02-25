<?php
namespace Crimson\CorvetteCentral\Block;

use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\Template\FilterProvider;

class CmsBanner extends Template
{
    protected $page;
    protected $filterProvider;

    public function __construct(
        Template\Context $context,
        Page $page,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->page = $page;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    public function getBannerContent()
    {
        $banner = $this->page->getData('custom_banner');
        if (!empty($banner)) {
            return $this->filterProvider->getPageFilter()->filter($banner);
        }
        return '';
    }
}
