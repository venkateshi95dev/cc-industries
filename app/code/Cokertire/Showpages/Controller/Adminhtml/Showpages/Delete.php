<?php

namespace Cokertire\Showpages\Controller\Adminhtml\Showpages;

class Delete extends \Cokertire\Showpages\Controller\Adminhtml\Showpages
{
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->_resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $showpages    = $this->_ShowpagesFactory->create();
                $showpages->load($id);
                $showpages->delete();
                $this->messageManager->addSuccess(__('The Showpages has been deleted.'));
                $resultRedirect->setPath('showpages/showpages/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $resultRedirect->setPath('showpages/showpages/edit', ['id' => $id]);
                return $resultRedirect;
            }
        }

        $this->messageManager->addError(__('Showpages to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('showpages/showpages/');
        return $resultRedirect;
    }
}
