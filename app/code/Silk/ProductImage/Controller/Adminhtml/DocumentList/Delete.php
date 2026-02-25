<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Controller\Adminhtml\DocumentList;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Delete CMS document action.
 */
class Delete extends \Silk\ProductImage\Controller\Adminhtml\DocumentList implements HttpPostActionInterface
{
    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('document_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            $title = "";
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Silk\ProductImage\Model\Document::class);
                $model->load($id);

                $title = $model->getTitle();
                $model->delete();

                // display success message
                $this->messageManager->addSuccessMessage(__('The document has been deleted.'));

                // go to grid
                $this->_eventManager->dispatch('adminhtml_cmsdocument_on_delete', [
                    'title' => $title,
                    'status' => 'success'
                ]);

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_cmsdocument_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['document_id' => $id]);
            }
        }

        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a document to delete.'));

        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
