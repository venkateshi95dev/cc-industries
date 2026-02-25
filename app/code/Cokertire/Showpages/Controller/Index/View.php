<?php

namespace Cokertire\Showpages\Controller\Index;

use Magento\Framework\App\Action\Context;

class View extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

    /**
     * Showpages Factory
     *
     * @var \Cokertire\Showpages\Model\ShowpagesFactory
     */
    protected $_ShowpagesFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public function __construct(Context $context,
        \Cokertire\Showpages\Model\ShowpagesFactory $showpagesFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->_ShowpagesFactory           = $showpagesFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry          = $coreRegistry;
        parent::__construct($context);
    }

    public function execute()
    {

        $showpagesId  = $this->getRequest()->getParams();
        reset($showpagesId);
        $showpagesId = key($showpagesId);
        /** @var \Cokertire\Showpages\Model\Showpages $showpages */
        $showpages    = $this->_ShowpagesFactory->create();
        if ($showpagesId) {
            $showpages->load($showpagesId);
        }
        $this->_coreRegistry->register('current_showpages', $showpages);
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($showpages->getShowTitleMeta());
        return $resultPage;
    }
}
