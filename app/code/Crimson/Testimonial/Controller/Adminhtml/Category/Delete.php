<?php

namespace Crimson\Testimonial\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;

class Delete extends Action
{


    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Crimson_Testimonial::category_delete');
    }


    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('category_id');
        /*
            *
            *
            * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
        */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $model = $this->_objectManager->create('Crimson\Testimonial\Model\Category');
                $model->load($id);
                $title = $model->getTitle();
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The category has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_cmspage_on_delete',
                    [
                        'title' => $title,
                        'status' => 'success',
                    ]
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_cmspage_on_delete',
                    [
                        'title' => $title,
                        'status' => 'fail',
                    ]
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['category_id' => $id]);
            }//end try
        }//end if

        // display error message
        $this->messageManager->addError(__('We can\'t find a category to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
