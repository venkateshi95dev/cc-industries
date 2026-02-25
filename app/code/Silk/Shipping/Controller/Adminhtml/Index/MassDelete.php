<?php

namespace Silk\Shipping\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \Silk\Shipping\Controller\Adminhtml\Index
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $methodIds = $this->getRequest()->getParam('id');
        if (!is_array($methodIds)) {
            $this->messageManager->addError(__('Please select shipping method.'));
        } else {
            try {
                foreach ($methodIds as $searchId) {
                    $model = $this->_silkMethodsModelFactory->create()->load($searchId);
                    $model->delete();
                }
                $this->messageManager->addSuccess(__('Total of %1 record(s) were deleted.', count($methodIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('mappingshipping/*/');
        return $resultRedirect;
    }
}
