<?php

namespace Crimson\Testimonial\Block\Widget;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class ButtonWidget extends Template implements BlockInterface
{
    /**
     * @var \Crimson\Testimonial\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;


    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Crimson\Testimonial\Helper\Data $_helper,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->_helper   = $_helper;
        $this->urlHelper = $urlHelper;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }


    protected function _construct()
    {
        parent::_construct();
    }


    public function _toHtml()
    {
        $enable = $this->_helper->getConfig('general/enable');
        if(!$enable) { return;
        }
        $enable_form = $this->_helper->getConfig('general/enable_form');
        if(!$enable_form) { return;
        }
        $require_loggedin = $this->_helper->getConfig('general/form_require_customer');
        if($require_loggedin && !$this->customerSession->getCustomerGroupId() ) {
            return;
        }
        $template = 'Crimson_Testimonial::widget/button_widget.phtml';
        $this->setTemplate($template);
        return parent::_toHtml();
    }

    public function getConfig($key, $default = '')
    {
        if($this->hasData($key) && $this->getData($key)) {
            return $this->getData($key);
        }

        return $default;
    }
}
