<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Controller\Adminhtml\Menu;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magedelight\Megamenu\Model\Menu;

class Delete extends Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magedelight_Megamenu::delete';

    /**
     * @var Menu
     */
    private $menuModel;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Menu $menuModel
     */
    public function __construct(
        Context $context,
        Menu $menuModel
    ) {
        parent::__construct($context);
        $this->menuModel = $menuModel;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('menu_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $model = $this->menuModel;
                $model->load($id);
                $title = $model->getMenuName();
                $model->delete($id);
                $this->messageManager->addSuccessMessage(__('The menu has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_megamenu_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_megamenu_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['menu_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a menu to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
