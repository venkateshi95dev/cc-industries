<?php

namespace Silk\Shipping\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Delete extends \Silk\Shipping\Controller\Adminhtml\Index
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $methodId = (int)$this->getRequest()->getParam('id');
        try {
            $methodModel = $this->_silkMethodsModelFactory->create()->load($methodId);
            $methodModel->delete();
            $this->messageManager->addSuccess(__('The shipping method has been deleted.'));
            return $resultRedirect->setPath('mappingshipping/*/');
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addError(__('This shipping method no longer exists.'));
            return $resultRedirect->setPath('mappingshipping/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong deleting this shipping method.'));
        }

        return $resultRedirect->setUrl($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }
}
