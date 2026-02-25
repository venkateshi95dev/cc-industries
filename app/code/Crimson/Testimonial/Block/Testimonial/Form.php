<?php

namespace Crimson\Testimonial\Block\Testimonial;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;

class Form extends Template
{
    /**
     * @var \Crimson\Testimonial\Helper\Data
     */
    protected $_testimonialData;

     /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Crimson\Testimonial\Model\Testimonial
     **/
    protected $_testimonialCollection;

    /**
     * @var \Crimson\Testimonial\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Crimson\Testimonial\Helper\Data $testimonialData,
        // \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Crimson\Testimonial\Model\Testimonial $testimonialCollection,
        \Crimson\Testimonial\Helper\Data $_helper,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->_testimonialData       = $testimonialData;
        $this->scopeConfig            = $context->getScopeConfig();
        $this->_testimonialCollection = $testimonialCollection;
        $this->_helper   = $_helper;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }


    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Create New Testimonial'));
        parent::_prepareLayout();
    }

    public function _toHtml()
    {
        $enable = $this->_helper->getConfig('general/enable');
        if(!$enable) { return;
        }
        $enable = $this->_helper->getConfig('general/enable_form');
        if(!$enable) { return;
        }
        $require_loggedin = $this->_helper->getConfig('general/form_require_customer');
        if($require_loggedin && !$this->customerSession->isLoggedIn()) {
            return;
        }
        return parent::_toHtml();
    }

    function getMediaBaseUrl()
    {
        /*
            *
            *
            * @var \Magento\Framework\ObjectManagerInterface $om
        */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /*
            *
            *
            * @var \Magento\Store\Model\StoreManagerInterface $storeManager
        */
        $storeManager = $om->get('Magento\Store\Model\StoreManagerInterface');
        /*
            *
            *
            * @var \Magento\Store\Api\Data\StoreInterface|\Magento\Store\Model\Store $currentStore
        */
        $currentStore = $storeManager->getStore();
        return $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

    }

    function getRatingHtml()
    {
        return $this->_testimonialData->getStarRating();

    }
}
