<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/11/2019
 */
namespace Crimson\Brand\Controller\Adminhtml\Brand;

use Crimson\Brand\Controller\Adminhtml\Brand;

class Delete extends Brand
{
    /**
     * @var string
     */
    const ADMIN_RESOURCE = 'Crimson_Brand::edit';

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('brand_id');
        if ($id) {
            $name = "";
            try {

                $brand = $this->brandRepository->getById($id);
                $name = $brand->getName();
                $this->brandRepository->delete($brand);

                $this->messageManager->addSuccess(__('The Brand has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_crimson_brand_brand_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
                $resultRedirect->setPath('brand/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_crimson_brand_brand_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('brand/*/edit', ['brand_id' => $id]);
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('Brand to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('brand/*/');
        return $resultRedirect;
    }
}
