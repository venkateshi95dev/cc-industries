<?php

namespace Cokertire\Distributors\Controller\Adminhtml\Distributors;

class Delete extends \Cokertire\Distributors\Controller\Adminhtml\Distributors
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
                $distributors = $this->_distributorsFactory->create();
                $distributors->load($id);
                $distributors->delete();
                $this->messageManager->addSuccess(__('The Distributors has been deleted.'));
                $resultRedirect->setPath('distributors/distributors/');
                return $resultRedirect;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('distributors/distributors/edit', ['id' => $id]);
                return $resultRedirect;
            }
        }

        $this->messageManager->addError(__('Distributors to delete was not found.'));

        $resultRedirect->setPath('distributors/distributors/');
        return $resultRedirect;
    }
}
