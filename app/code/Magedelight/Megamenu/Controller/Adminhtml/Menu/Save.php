<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Controller\Adminhtml\Menu;

use Magedelight\Megamenu\Model\Menu;
use Magedelight\Megamenu\Model\MenuItems;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Save extends Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magedelight_Megamenu::save';

    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var Menu
     */
    protected $menuModel;

    /**
     * @var MenuItems
     */
    protected $menuItemModel;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     * @param Menu $menuModel
     * @param MenuItems $menuItemModel
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        DataPersistorInterface $dataPersistor,
        Menu $menuModel,
        MenuItems $menuItemModel,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->menuModel = $menuModel;
        $this->menuItemModel = $menuItemModel;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $menuData = json_decode($data['menu_data_json'], true);

            if ($data['totalMenus'] > 0 && ($menuData===null)) {
                $this->messageManager->addErrorMessage(
                    __("Something went wrong while saving data, please try again")
                );
                return $resultRedirect->setPath(
                    '*/*/edit',
                    ['menu_id' => $this->getRequest()->getParam('menu_id')]
                );
            }

            $fileInput = $files = $this->getRequest()->getFiles()->toArray();
            $filesInputProcessed = $this->processFileInput($fileInput);
            // Process file uploads
            if (isset($filesInputProcessed['menu_icon'])) {
                $filename = str_replace(' ', '_', $filesInputProcessed['menu_icon']['name']);
                foreach ($filename as $key => $image) {
                    if (!empty($image)) {
                        try {
                            $type = explode('.', $image);
                            if (isset($type[1]) && in_array($type[1], ['jpg', 'jpeg', 'gif', 'png'])) {
                                $uploader = $this->uploaderFactory->create(['fileId' => "menu_icon[{$key}]"]);
                                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                                $uploader->setAllowRenameFiles(false);
                                $uploader->setFilesDispersion(false);
                                $path = $this->filesystem->getDirectoryRead(
                                    DirectoryList::MEDIA
                                )->getAbsolutePath('menu_icon/');
                                $uploader->save($path);
                            } elseif (isset($data['menu_icon_edit']) && isset($data['menu_icon_edit'][$key])) {
                                $this->messageManager->addErrorMessage(
                                    "Disallowed File Type provided for Menu Icon. JPG, JPEG, PNG, GIF are supported."
                                );
                                $filesInputProcessed['menu_icon']['name'][$key] = $data['menu_icon_edit'][$key];
                            } else {
                                $this->messageManager->addErrorMessage(
                                    "Disallowed File Type provided for Menu Icon. JPG, JPEG, PNG, GIF are supported."
                                );
                                $filesInputProcessed['menu_icon']['name'][$key] = null;
                            }
                        } catch (\Exception $e) {
                            $this->messageManager->addErrorMessage(__($e->getMessage()));
                        }
                    }
                }
            }

            $data = $this->dataProcessor->filter($data);
            $isActive = isset($data['is_active']) && !empty($data['is_active']) ? 1 : 0;
            $data['is_active'] = $isActive;

            $data['menu_id'] = empty($data['menu_id']) ? null : $data['menu_id'];

            if (isset($data['store_id']) && (in_array("0", $data['store_id']))) {
                unset($data['store_id']);
                $data['store_id'] = [0];
            }

            if ($data['customer_groups']) {
                $data['customer_groups'] = implode(',', $data['customer_groups']);
            }

            $model = $this->menuModel;

            $id = $this->getRequest()->getParam('menu_id');
            if ($id) {
                $model->load($id);
            }

            $model->setData($data);
            $this->_eventManager->dispatch(
                'megamenu_menu_prepare_save',
                ['menu' => $model, 'request' => $this->getRequest()]
            );

            if (!$this->dataProcessor->validateRequireEntry($data)) {
                return $resultRedirect->setPath('*/*/edit', ['menu_id' => $model->getMenuId(),
                    '_current' => true]);
            }

            try {
                // Save the original menu
                $form = $model->save();
                $menuId = $form->getMenuId();

                $this->menuItemModel->deleteItems($menuId);

                // Clone the menu data for potential duplication
                $originalMenuData = null;
                if (is_array($menuData) && count($menuData) > 0) {
                    $originalMenuData = json_decode(json_encode($menuData), true);
                }

                // Process menu items for the original menu
                if (is_array($menuData) && count($menuData) > 0) {
                    $menuDataFinal = $menuData['menu_data'];
                    if (isset($filesInputProcessed['menu_icon'])) {
                        $filename = str_replace(' ', '_', $filesInputProcessed['menu_icon']['name']);
                        $this->processMenuData($menuDataFinal, $menuId, $filename, $data);
                    } else {
                        $this->processMenuData($menuDataFinal, $menuId);
                    }
                }

                // Check if we need to duplicate the menu
                if ($this->getRequest()->getParam('save_and_duplicate')) {
                    // Create a duplicate menu
                    $duplicateModel = $this->menuModel;

                    // Get data from the original menu
                    $originalData = $model->load($id)->getData();

                    // Remove the ID to create a new entity
                    unset($originalData['menu_id']);

                    // Modify the name to indicate it's a copy
                    $originalData['menu_name'] = __('Copy of ') . $originalData['menu_name'];

                    $data = [];
                    foreach ($originalData as $key => $value) {
                        if (!isset($data[$key]) && !in_array($key, ['menu_id', 'created_at', 'updated_at'])) {
                            $data[$key] = $value;
                        }
                    }
                    // Set data to the duplicate model
                    $duplicateModel->setData($data);

                    // Save the duplicate
                    $duplicateForm = $duplicateModel->save();
                    $duplicateMenuId = $duplicateForm->getMenuId();

                    // Process menu items for the duplicate
                    if ($originalMenuData !== null) {
                        $menuDataFinal = $originalMenuData['menu_data'];
                        if (isset($filesInputProcessed['menu_icon'])) {
                            $filename = str_replace(' ', '_', $filesInputProcessed['menu_icon']['name']);
                            $this->processMenuData($menuDataFinal, $duplicateMenuId, $filename, $data);
                        } else {
                            $this->processMenuData($menuDataFinal, $duplicateMenuId);
                        }
                    }

                    $this->messageManager->addSuccessMessage(__('You saved and duplicated the menu.'));

                    // Redirect to the edit page of the duplicate
                    return $resultRedirect->setPath('*/*/edit', ['menu_id' => $duplicateMenuId,
                        '_current' => true]);
                }

                // Standard save flow
                $this->messageManager->addSuccessMessage(__('You saved the menu.'));
                $this->dataPersistor->clear('megamenu_menu');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['menu_id' => $model->getMenuId(),
                        '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
            }

            $this->dataPersistor->set('megamenu_menu', $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['menu_id' => $this->getRequest()->getParam('menu_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Process File Input
     *
     * @param array $fileinput
     * @return array
     */
    private function processFileInput($fileinput)
    {
        $output = [];
        foreach ($fileinput as $key => $value) {
            foreach ($value as $subKey => $subValue) {
                foreach ($subValue as $subSubKey => $subSubValue) {
                    $output[$key][$subSubKey][$subKey] = $subSubValue;
                }
            }
        }
        return $output;
    }

    /**
     * Process Menu Data
     *
     * @param array $menuDataFinal
     * @param int $menuId
     * @param string $fileName
     * @param array $data
     * @return void
     */
    private function processMenuData(array $menuDataFinal, $menuId, $fileName = null, $data = null)
    {
        foreach ($menuDataFinal as $i => $menu_item_data) {
            $menu_data = $menuDataFinal[$i];
            if ($fileName !== null && isset($fileName[$i])) {
                if (!empty($fileName[$i])) {
                    $menu_data['menu_icon'] = $fileName[$i];
                } elseif (!empty($data['menu_icon_edit'][$i])) {
                    if (empty($data['menu_icon_delete'][$i])) {
                        $menu_data['menu_icon'] = $data['menu_icon_edit'][$i];
                    }
                }
            }

            if (isset($menu_data['item_name']) && isset($menu_data['item_type'])) {
                $itemsData = $this->prepareMenuItemData($menu_data, $menuId);
                $currentItem = $this->menuItemModel->setData($itemsData)->save();
                $itemId = $currentItem->getItemId();

                foreach ($menuDataFinal as $key => $val) {
                    if (isset($val['item_parent_id']) && $val['item_parent_id'] == $i) {
                        $menuDataFinal[$key]['item_parent_id'] = $itemId;
                    }
                }
            }
        }
    }

    /**
     * Prepare Menu Item Data
     *
     * @param array $menu_data
     * @param int $menuId
     * @return array
     */
    private function prepareMenuItemData(array $menu_data, $menuId)
    {
        return [
            'item_name' => $menu_data['item_name'],
            'item_type' => $menu_data['item_type'],
            'sort_order' => $menu_data['sort_order'],
            'item_parent_id' => $menu_data['item_parent_id'],
            'menu_id' => $menuId,
            'object_id' => $menu_data['object_id'],
            'item_link' => $menu_data['item_link'],
            'item_font_icon' => $menu_data['item_font_icon'],
            'item_class' => $menu_data['item_class'],
            'animation_option' => $menu_data['animation_option'],
            'category_display' => $menu_data['item_all_cat'] ?: null,
            'category_vertical_menu' => $menu_data['item_vertical_menu'] ?: null,
            'category_vertical_menu_bg' => $menu_data['vertical_menu_bgcolor'] ?: null,
            'item_columns' => !empty($menu_data['item_columns']) ?
            json_encode($menu_data['item_columns']) : null,
            'category_columns' => !empty($menu_data['category_columns']) ?
            json_encode($menu_data['category_columns']) : null,
            'vertical_cat_exclude' => !empty($menu_data['verticalcatexclude']) ?
            $menu_data['verticalcatexclude'] : null,
            'vertical_cat_sortby' => !empty($menu_data['vertical_cat_sortby']) ?
            $menu_data['vertical_cat_sortby'] : null,
            'vertical_cat_sortorder' => !empty($menu_data['vertical_cat_sortorder']) ?
            $menu_data['vertical_cat_sortorder'] : null,
            'vertical_cat_level' => !empty($menu_data['vertical_cat_level']) ?
            $menu_data['vertical_cat_level'] : null,
            'product_display' => !empty($menu_data['product_display']) ?
            $menu_data['product_display'] : null,
            'open_in_new_tab' => !empty($menu_data['open_in_new_tab']) ?
            $menu_data['open_in_new_tab'] : null,
            'menu_icon' => !empty($menu_data['menu_icon']) ? $menu_data['menu_icon'] : null,
        ];
    }
}
