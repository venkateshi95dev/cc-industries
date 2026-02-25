<?php
namespace  Silk\Shipping\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Save extends \Silk\Shipping\Controller\Adminhtml\Index
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $postData = $this->getRequest()->getPostValue();
        if ($postData) {
            try {
                $methodModel= $this->_silkMethodsModelFactory->create();
                $methodModel->setData($postData);
                $methodModel->save();

                $this->messageManager->addSuccess(__('You saved the shipping method.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('mappingshipping/*/edit', ['id' => $methodModel->getId()]);
                }
                return $resultRedirect->setPath('mappingshipping/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t save this shipping method right now.'));
            }
            return $resultRedirect->setUrl($this->_redirect->getRedirectUrl($this->getUrl('*')));
        }
        return $resultRedirect->setPath('mappingshipping/*/');
    }
}
