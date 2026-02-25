<?php

namespace Cokertire\Distributors\Controller\Adminhtml;

abstract class Distributors extends \Magento\Backend\App\Action
{
    /**
     * Distributors Factory
     * 
     * @var \Cokertire\Distributors\Model\DistributorsFactory
     */
    protected $_distributorsFactory;

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
     * @param \Cokertire\Distributors\Model\DistributorsFactory $DistributorsFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Cokertire\Distributors\Model\DistributorsFactory $distributorsFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_distributorsFactory           = $distributorsFactory;
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
        $distributorsId  = (int) $this->getRequest()->getParam('id');
        $distributors    = $this->_distributorsFactory->create();
        if ($distributorsId) {
            $distributors->load($distributorsId);
        }
        $this->_coreRegistry->register('cokertire_distributors', $distributors);
        return $distributors;
    }
}
