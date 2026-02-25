<?php

namespace Crimson\Testimonial\Controller\Adminhtml\Testimonial;

use Magento\Backend\App\Action;

class NewAction extends Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $resultForwardFactory;


    /**
     * @param \Magento\Backend\App\Action\Context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }


    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Crimson_Testimonial::testimonial_edit');
    }


    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        /*
            *
            *
            * @var \Magento\Backend\Model\View\Result\Forward $resultForward
        */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
