<?php

namespace Crimson\Testimonial\Block\Widget;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class CustomWidget extends Template implements BlockInterface
{

    /**
     * Name of request parameter for page number value
     */
    const PAGE_VAR_NAME = 'ntmp';

    /**
     * Instance of pager block
     *
     * @var \Magento\Catalog\Block\Product\Widget\Html\Pager
     */
    protected $_pager;

    /**
     * @var \Crimson\Testimonial\Helper\Data
     */
    protected $_helper;
     /**
      * @var \PHPUnit_Framework_MockObject_MockObject
      */
    protected $_resource;
    /**
     * @var \Crimson\Testimonial\Model\ResourceModel\Testimonial\CollectionFactory
     */
    protected $_testimonialCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Crimson\Testimonial\Model\ResourceModel\Testimonial\CollectionFactory $testimonialCollectionFactory,
        \Crimson\Testimonial\Helper\Data $_helper,
        \Magento\Framework\App\ResourceConnection $resource,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->_helper   = $_helper;
        $this->urlHelper = $urlHelper;
        $this->_testimonialCollectionFactory = $testimonialCollectionFactory;
        $this->_resource = $resource;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function _toHtml()
    {
        $enable = $this->_helper->getConfig('general/enable');
        if(!$enable) { return;
        }

        $template = '';
        $layout   = $this->getConfig('layout');
        switch ($layout) {
        case 'topmeta':
            $template = 'Crimson_Testimonial::widget/topmeta.phtml';
            break;
        case 'bottommeta':
            $template = 'Crimson_Testimonial::widget/bottommeta.phtml';
            break;
        case 'alltop':
            $template = 'Crimson_Testimonial::widget/alltop.phtml';
            break;
        case 'allbottom':
            $template = 'Crimson_Testimonial::widget/allbottom.phtml';
            break;
        case 'topimage':
            $template = 'Crimson_Testimonial::widget/topimage.phtml';
            break;
        case 'bottomimage':
            $template = 'Crimson_Testimonial::widget/bottomimage.phtml';
            break;
        case 'style1':
            $template = 'Crimson_Testimonial::widget/style1.phtml';
            break;
        case 'style2':
            $template = 'Crimson_Testimonial::widget/style2.phtml';
            break;
        case 'style3':
            $template = 'Crimson_Testimonial::widget/style3.phtml';
            break;
        case 'style4':
            $template = 'Crimson_Testimonial::widget/style4.phtml';
            break;
        case 'slide1':
            $template = 'Crimson_Testimonial::widget/slide1.phtml';
            break;
        case 'slide2':
            $template = 'Crimson_Testimonial::widget/slide2.phtml';
            break;
        case 'grid':
            $template = 'Crimson_Testimonial::widget/grid.phtml';
            break;
        case 'grid1':
            $template = 'Crimson_Testimonial::widget/grid1.phtml';
            break;
        case 'grid2':
            $template = 'Crimson_Testimonial::widget/grid2.phtml';
            break;
        case 'list':
            $template = 'Crimson_Testimonial::widget/list.phtml';
            break;
        }//end switch

        if($blockTemplate = $this->getConfig('block_template')) {
            $template = $blockTemplate;
        }

        $this->setTemplate($template);
        $orderBy  = $this->getConfig('order_by');
        $category = $this->getConfig('category');

        if (is_string($category)) {
            $cats = explode(',', $category);
        } else {
            $cats = [];
        }
        $item_per_page = (int) $this->getConfig('item_per_page');
        $store = $this->_storeManager->getStore();
        $testimonialCollection = $this->_testimonialCollectionFactory->create()
            ->setPageSize($item_per_page)
            ->addStoreFilter($store)
            ->addFieldToFilter('is_active', '1');
        if(count($cats) > 0 && $cats[0]) {
            $testimonialCollection->getSelect("main_table.testimonial_id")
                ->joinLeft(
                    [
                     'cat' => $this->_resource->getTableName('ves_testimonial_testimonial_category'),
                    ],
                    'cat.testimonial_id = main_table.testimonial_id',
                    ["testimonial_id" => "testimonial_id"]
                )->where('cat.category_id IN (?)', $cats);
            $testimonialCollection->getSelect()->order("main_table.testimonial_id DESC")->group('cat.testimonial_id');
        }

        if($orderBy == 'rating') {
            $testimonialCollection->setOrder('rating', 'DESC');
        }else if($orderBy == 'position') {
            $testimonialCollection->setOrder('position', 'ASC');
        }else if($orderBy == 'random') {
            $testimonialCollection->getSelect()->order('rand()');
        }else if($orderBy == 'recent') {
            $testimonialCollection->setOrder('main_table.create_time', 'DESC');
        }

        if($this->getConfig('grid_pagination') || $layout == 'grid' || $layout == 'grid1' || $layout == 'grid2' || $layout == 'list') {
            $currentPage = $this->getCurrentPage();
            $testimonialCollection->setCurPage($currentPage);
        } else {
            $numberItem    = (int) $this->getConfig('number_item');
            $testimonialCollection->setPageSize($numberItem);
        }

        $this->setTestimonialCollection($testimonialCollection);
        return parent::_toHtml();
    }


    /**
     * Get number of current page based on query value
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return abs((int) $this->getRequest()->getParam(self::PAGE_VAR_NAME));
    }


    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $numberItem    = (int) $this->getConfig('number_item');
        $item_per_page = (int) $this->getConfig('item_per_page');
        $name          = 'ves.testimonial.widget'.time().uniqid();
        if (!$this->_pager) {
            $this->_pager = $this->getLayout()->createBlock(
                'Magento\Catalog\Block\Product\Widget\Html\Pager',
                $name
            );
            $this->_pager->setUseContainer(true)
                ->setShowAmounts(false)
                ->setShowPerPage(false)
                ->setPageVarName(self::PAGE_VAR_NAME)
                ->setLimit($item_per_page)
                ->setTotalLimit($numberItem)
                ->setCollection($this->getTestimonialCollection());
        }

        if ($this->_pager instanceof AbstractBlock) {
            return $this->_pager->toHtml();
        }
    }


    public function setTestimonialCollection($collection)
    {
        $this->_collection = $collection;
        return $this;
    }


    public function getTestimonialCollection()
    {
        return $this->_collection;
    }


    public function getConfig($key, $default = '')
    {
        if($this->hasData($key) && $this->getData($key)) {
            return $this->getData($key);
        }

        return $default;
    }

    public function getCustomerInfo(){
        if($this->customerSession->getCustomerGroupId() || $this->customerSession->isLoggedIn()){
            return $this->customerSession->getCustomer();
        }
        return false;
    }
}
