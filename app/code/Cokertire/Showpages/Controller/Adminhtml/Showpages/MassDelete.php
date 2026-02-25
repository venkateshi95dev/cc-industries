<?php

namespace Cokertire\Showpages\Controller\Adminhtml\Showpages;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Mass Action Filter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;

    /**
     * Collection Factory
     *
     * @var \Cokertire\Showpages\Model\ResourceModel\Showpages\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * constructor
     *
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Cokertire\Showpages\Model\ResourceModel\Showpages\CollectionFactory $collectionFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Cokertire\Showpages\Model\ResourceModel\Showpages\CollectionFactory $collectionFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_filter            = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }


    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());

        $delete = 0;
        foreach ($collection as $item) {
            $item->delete();
            $delete++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $delete));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
