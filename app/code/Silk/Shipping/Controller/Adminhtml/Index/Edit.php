<?php

namespace Silk\Shipping\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Silk\Shipping\Controller\Adminhtml\Index
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $methodId = $this->getRequest()->getParam('id');
        $this->_coreRegistry->register('shipping_method_id', $methodId);
        if ($methodId) {
            try {
                $method = $this->_silkMethodsModelFactory->create()->load($methodId);
                $pageTitle = sprintf("%s Shipping Method", $method->getThnMethodCode());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addError(__('This shipping method no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('mappingshipping/*/');
            }
        } else {
            $pageTitle = __('New Shipping Method');
        }

        $breadcrumb = $methodId ? __('Edit Shipping Method') : __('New Shipping Method');
        $resultPage = $this->initResultPage();
        $resultPage->addBreadcrumb($breadcrumb, $breadcrumb);
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Shipping Methods'));
        $resultPage->getConfig()->getTitle()->prepend($pageTitle);
        return $resultPage;
    }
}
