<?php

namespace Silk\Shipping\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class NewAction extends \Silk\Shipping\Controller\Adminhtml\Index
{
    /**
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        return $resultForward->forward('edit');
    }
}
