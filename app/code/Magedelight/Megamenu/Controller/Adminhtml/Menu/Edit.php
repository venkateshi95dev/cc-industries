<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Controller\Adminhtml\Menu;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magedelight\Megamenu\Model\Menu;

class Edit extends Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magedelight_Megamenu::edit';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Menu
     */
    private $menuModel;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Menu $menuModel
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        Menu $menuModel
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->menuModel = $menuModel;
    }

    /**
     * Init actions
     *
     * @return Page
     */
    protected function _initAction()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magedelight_Megamenu::menu');
        $resultPage->addBreadcrumb(__('Mega Menu'), __('Menu'));
        $resultPage->getConfig()->getTitle()->prepend(__('Menu'));
        return $resultPage;
    }

    /**
     * Edit Menu
     *
     * @return Redirect|Page
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('menu_id');
        $model = $this->menuModel;
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This menu no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->registry->register('megamenu_menu', $model);

        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Menu') : __('New Menu'),
            $id ? __('Edit Menu') : __('New Menu')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Menus'));
        $resultPage->getConfig()->getTitle()
                ->prepend($model->getMenuId() ? $model->getMenuName() : __('New Menu'));

        return $resultPage;
    }
}
