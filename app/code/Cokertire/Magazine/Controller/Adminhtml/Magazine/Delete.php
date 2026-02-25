<?php

namespace Cokertire\Magazine\Controller\Adminhtml\Magazine;

class Delete extends  \Cokertire\Magazine\Controller\Adminhtml\Magazine
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
                $magazine = $this->_magazineFactory->create();
                $magazine->load($id);
                $magazine->delete();
                $this->messageManager->addSuccess(__('The Magazine has been deleted.'));
                $resultRedirect->setPath('magazine/magazine/');
                return $resultRedirect;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('magazine/magazine/edit', ['id' => $id]);
                return $resultRedirect;
            }
        }

        $this->messageManager->addError(__('Magazine to delete was not found.'));

        $resultRedirect->setPath('magazine/magazine/');
        return $resultRedirect;
    }
}
