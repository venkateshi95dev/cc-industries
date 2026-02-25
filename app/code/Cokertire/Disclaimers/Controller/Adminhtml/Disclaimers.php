<?php

namespace Cokertire\Disclaimers\Controller\Adminhtml;

abstract class Disclaimers extends \Magento\Backend\App\Action
{
    /**
     * Distributors Factory
     * 
     * @var \Cokertire\Disclaimers\Model\DisclaimersFactory
     */
    protected $_disclaimersFactory;

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
     * @param \Cokertire\Disclaimers\Model\DisclaimersFactory $DisclaimersFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Cokertire\Disclaimers\Model\DisclaimersFactory $DisclaimersFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_disclaimersFactory           = $DisclaimersFactory;
        $this->_coreRegistry          = $coreRegistry;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Init Distributors
     *
     * @return \Cokertire\Distributors\Model\Distributors
     */
    protected function _initPost()
    {
        $id  = (int) $this->getRequest()->getParam('id');
        $disclaimers    = $this->_disclaimersFactory->create();
        if ($id) {
            $disclaimers->load($id);
        }
        $this->_coreRegistry->register('cokertire_disclaimers', $disclaimers);
        return $disclaimers;
    }
}
