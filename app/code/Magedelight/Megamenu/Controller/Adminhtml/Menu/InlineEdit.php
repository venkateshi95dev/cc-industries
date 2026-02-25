<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Controller\Adminhtml\Menu;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magedelight\Megamenu\Model\Menu;

class InlineEdit extends Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var Menu
     */
    protected $menuModel;

    /**
     * InlineEdit constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param JsonFactory $jsonFactory
     * @param Menu $menuModel
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $jsonFactory,
        Menu $menuModel
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->jsonFactory = $jsonFactory;
        $this->menuModel = $menuModel;
    }

    /**
     * Inline edit element action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                        'messages' => [__('Please correct the data sent.')],
                        'error' => true,
            ]);
        }

        $model = $this->menuModel;

        $this->_eventManager->dispatch(
            'megamenu_menu_prepare_inlinesave',
            ['menu' => $model, 'request' => $this->getRequest()]
        );

        foreach (array_keys($postItems) as $menuId) {
            try {
                if ($postItems[$menuId]['menu_id']) {
                    $model->load($menuId);
                    $model->setData($postItems[$menuId]);
                    $model->save();
                    $messages[] = __('Menu saved.');
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithMenuId($menuId, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithMenuId($menuId, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithMenuId(
                    $menuId,
                    __('Something went wrong while saving the menu.')
                );
                $error = true;
            }
        }
        return $resultJson->setData(['messages' => $messages, 'error' => $error]);
    }

    /**
     * Add menu title to error message
     *
     * @param string $menuId
     * @param string $errorText
     * @return string
     */
    private function getErrorWithMenuId($menuId, $errorText)
    {
        return '[Menu ID: ' . $menuId . '] ' . $errorText;
    }
}
