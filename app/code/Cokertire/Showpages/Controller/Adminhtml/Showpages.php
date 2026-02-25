<?php

namespace Cokertire\Showpages\Controller\Adminhtml;

abstract class Showpages extends \Magento\Backend\App\Action
{
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

    /**
     * Result redirect factory
     *
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $_resultRedirectFactory;

    /**
     * constructor
     *
     * @param \Cokertire\Showpages\Model\ShowpagesFactory $showpagesFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Cokertire\Showpages\Model\ShowpagesFactory $showpagesFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_ShowpagesFactory           = $showpagesFactory;
        $this->_coreRegistry          = $coreRegistry;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Init Showpages
     *
     * @return \Cokertire\Showpages\Model\Showpages
     */
    protected function _initShowpages()
    {
        $showpagesId  = (int) $this->getRequest()->getParam('id');
        /** @var \Cokertire\Showpages\Model\Showpages $showpages */
        $showpages    = $this->_ShowpagesFactory->create();
        if ($showpagesId) {
            $showpages->load($showpagesId);
        }
        $this->_coreRegistry->register('coker_showpages', $showpages);
        return $showpages;
    }
}
