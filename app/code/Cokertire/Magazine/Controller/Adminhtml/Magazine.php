<?php

namespace Cokertire\Magazine\Controller\Adminhtml;

abstract class Magazine extends \Magento\Backend\App\Action
{
    /**
     * Magazine Factory
     *
     * @var \Cokertire\Magazine\Model\MagazineFactory
     */
    protected $_magazineFactory;

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
     * @param \Cokertire\Magazine\Model\MagazineFactory $magazineFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Cokertire\Magazine\Model\MagazineFactory  $magazineFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_magazineFactory           = $magazineFactory;
        $this->_coreRegistry          = $coreRegistry;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Init Magazine
     *
     * @return \Cokertire\Magazine\Model\Magazine
     */
    protected function _initPost()
    {
        $Id  = (int) $this->getRequest()->getParam('id');
        $object    = $this->_magazineFactory->create();
        if ($Id) {
            $object->load($Id);
        }
        $this->_coreRegistry->register('ct_magazine', $object);
        return $object;
    }
}
