<?php

namespace Silk\Shipping\Controller\Adminhtml;

use Magento\Framework\Controller\ResultFactory;

/**
 * THN Shipping Methods admin controller
 */
abstract class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Silk_Shipping::mappingshipping';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var  \Silk\Shipping\Model\MethodsFactory
     */
    protected $_silkMethodsModelFactory;


    /**
     * @param \Magento\Backend\App\Action\Context    $context
     * @param \Magento\Framework\Registry            $coreRegistry
     * @param \Silk\Shipping\Model\MethodsFactory $silkMethodsModelFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Silk\Shipping\Model\MethodsFactory $silkMethodsModelFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_silkMethodsModelFactory = $silkMethodsModelFactory;
        parent::__construct($context);
    }

    /**
     * Initialize action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initResultPage()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Silk_Shipping::manage')
            ->addBreadcrumb(__('Silk'), __('Silk'))
            ->addBreadcrumb(__('Manage hipping Methods'), __('Manage Shipping Methods'));
        return $resultPage;
    }
}
