<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Block;

use Magento\Catalog\Model\Category\Image as CategoryImage;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Topmenu as MagentoTopmenu;
use Magedelight\Megamenu\Model\Menu;
use Magento\Framework\View\Element\TemplateFactory;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\Registry;
use Magento\Cms\Model\Page;
use Magedelight\Megamenu\Helper\Data as MegamenuHelper;
use Magedelight\Megamenu\Model\MegamenuManagement;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magedelight\Megamenu\Model\Cache\Type as MegamenuCache;
use Magento\PageCache\Model\Cache\Type as PageCache;

/**
 * @SuppressWarnings(PHPMD)
 */
class Topmenu extends MagentoTopmenu
{
    public const MEGA_MENU_TEMPLATE = 'Magedelight_Megamenu::menu/topmenu.phtml';
    public const ALL_CATEGORY_MENU = 'all-category';
    public const AMAZON_MENU = 'amazon-menu';
    public const PRIMARY_NONE = 0;

    /**
     * @var \Magedelight\Megamenu\Api\Data\ConfigInterface
     */
    public $primaryMenu;

    /**
     * Primary Menu Id Value
     *
     * @var integer
     */
    public $primaryMenuId = 0;

    /**
     * Category Data Value
     *
     * @var array
     */
    public $categoryData;

    /**
     * Column Count
     *
     * @var integer
     */
    protected $mdColumnCount = 10;

    /**
     * Get Description menu
     *
     * @var String
     */
    protected $getDescription;

    /**
     * All Category Menu Obj
     *
     * @var \Magento\Framework\DataObject
     */
    public $allCategoryMenu;

    /**
     * All Category Menu Data stored
     *
     * @var array
     */
    public $allCategoryMenuData = [];

    /**
     * Category Menu Id
     *
     * @var integer
     */
    public $allCategoryMenuId = 0;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CustomerSessionFactory
     */
    protected $customerSession;

    /**
     * @var Page
     */
    protected $page;

    /**
     * @var MegamenuHelper
     */
    protected $helper;

    /**
     * @var MegamenuManagement
     */
    protected $megamenuManagement;

    /**
     * @var Output
     */
    protected $output;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var CategoryImage
     */
    private $image;
    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    private $cacheState;
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    private $cacheFrontendPool;
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * Topmenu constructor.
     *
     * @param Template\Context $context
     * @param NodeFactory $nodeFactory
     * @param TreeFactory $treeFactory
     * @param Registry $registry
     * @param CustomerSessionFactory $customerSession
     * @param Page $page
     * @param MegamenuHelper $helper
     * @param MegamenuManagement $megamenuManagement
     * @param Output $output
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryFactory $categoryFactory
     * @param TemplateFactory $templateFactory
     * @param CategoryImage $image
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        Registry $registry,
        CustomerSessionFactory $customerSession,
        Page $page,
        MegamenuHelper $helper,
        MegamenuManagement $megamenuManagement,
        Output $output,
        CategoryRepositoryInterface $categoryRepository,
        CategoryFactory $categoryFactory,
        TemplateFactory $templateFactory,
        CategoryImage $image,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        array $data = []
    ) {
        parent::__construct($context, $nodeFactory, $treeFactory, $data);
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->page = $page;
        $this->helper = $helper;
        $this->megamenuManagement = $megamenuManagement;
        $this->output = $output;
        $this->categoryRepository = $categoryRepository;
        $this->categoryFactory = $categoryFactory;
        $this->templateFactory = $templateFactory;
        $this->image = $image;
        $this->cacheState = $cacheState;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Get Cache Lifetime
     *
     * @return int
     */
//    protected function getCacheLifetime()
//    {
//        return parent::getCacheLifetime() ?: 3600;
//    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $keyInfo = parent::getCacheKeyInfo();
        $keyInfo[] = $this->getUrl('*/*/*', ['_current' => true, '_query' => '']);
        $keyInfo[] = $this->customerSession->create()->isLoggedIn() ? 'logged_in' : 'not_logged_in';
        return $keyInfo;
    }

    /**
     * Get tags array for saving cache
     *
     * @return array
     */
//    protected function getCacheTags()
//    {
//        return array_merge(parent::getCacheTags(), $this->getIdentities());
//    }

    /**
     * Get current Category
     *
     * @return mixed
     */
    public function getCurrentCat()
    {
        $category = $this->registry->registry('current_category');
        if (isset($category) && !empty($category->getId())) {
            return $category->getId();
        }
        return '';
    }

    /**
     * Get Current Page
     *
     * @return int|string
     */
    public function getCurentPage()
    {
        if ($this->page->getId()) {
            return $pageId = $this->page->getId();
        }
        return '';
    }

    /**
     * Set Custom Template
     *
     * @param string $template
     */
    public function setCustomTemplate($template)
    {
        $this->setTemplate($template);
        if ($this->helper->isEnabled()) {
            $_customerSession = $this->customerSession->create();
            if ($_customerSession->isLoggedIn()) {
                $this->primaryMenu = $this->megamenuManagement
                    ->getMenuData($_customerSession->getCustomerId())->getMenu();
            } else {
                $this->primaryMenu = $this->megamenuManagement->getMenuData()->getMenu();
            }
            $this->primaryMenuId = $this->primaryMenu->getMenuId();

            if ($this->primaryMenu->getIsActive()) {
                if ($this->primaryMenu->getMenuType() == Menu::MEGA_MENU) {
                    $this->setTemplate(self::MEGA_MENU_TEMPLATE);
                }
            } elseif ($this->helper->isHumbergerMenu()) {
                $this->setTemplate(self::MEGA_MENU_TEMPLATE);
            }
        }
    }
    public function getIdentities()
    {
        return [
            MegamenuCache::CACHE_TAG,
            MegamenuCache::CACHE_TAG . '_' . $this->_storeManager->getStore()->getId(),
            PageCache::CACHE_TAG
        ];
    }

    /**
     * Get Html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        $cacheKey = 'megamenu_html_' . $this->_storeManager->getStore()->getId();

        // Check if cache is enabled
        if ($this->cacheState->isEnabled(MegamenuCache::TYPE_IDENTIFIER)) {
            $cache = $this->cacheFrontendPool->get(MegamenuCache::TYPE_IDENTIFIER);
            $cachedHtml = $cache->load($cacheKey);

            if ($cachedHtml) {
                return $cachedHtml;
            }
        }
        $menuHtml = $this->getMegaMenuHtml($outermostClass, $childrenWrapClass, $limit);

        if ($this->cacheState->isEnabled(MegamenuCache::TYPE_IDENTIFIER)) {
            $cache = $this->cacheFrontendPool->get(MegamenuCache::TYPE_IDENTIFIER);
            $cache->save(
                $menuHtml,
                $cacheKey,
                [MegamenuCache::CACHE_TAG],
                86400 // 24 hours lifetime
            );
        }

        return $menuHtml;
    }

    /**
     *  Get All Category Menu Html
     *
     * @param int $menuId
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param integer $limit
     * @return string
     */
    public function getAllCategoryMenuHtml(
        $menuId,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        return $this->getAllCategoryMegaMenuHtml($menuId, $outermostClass, $childrenWrapClass, $limit);
    }

    /**
     * Get All Category Right Menu Html
     *
     * @param int $menuId
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllCategoryRightMenuHtml(
        $menuId,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        return $this->getAllCategoryRightMegaMenuHtml(
            $menuId,
            $outermostClass,
            $childrenWrapClass,
            $limit
        );
    }

    /**
     * Get Burger Html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBurgerHtml(
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        return $this->getHumbergerMenuHtml($outermostClass, $childrenWrapClass, $limit);
    }

    /**
     * Get Humberger Menu Html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     */
    public function getHumbergerMenuHtml($outermostClass, $childrenWrapClass, $limit)
    {
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_before',
            ['menu' => $this->_menu, 'block' => $this, 'request' => $this->getRequest()]
        );

        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);

        $html = $this->_getHtml($this->_menu, $childrenWrapClass, $limit);

        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);

        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_after',
            ['menu' => $this->_menu, 'transportObject' => $transportObject]
        );
        $html = $transportObject->getHtml();
        return $html;
    }

    /**
     * Get Amazon Menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     */
    public function getAmazonMenuHtml()
    {
        // Load the main Amazon menu
        $amazonMenu = $this->megamenuManagement->loadAllMegaMenus()
            ->addFieldToFilter('menu_design_type', self::AMAZON_MENU)
            ->getFirstItem();

        // Check if the menu exists
        if ($amazonMenu) {
            $amazonMenuId = $amazonMenu->getMenuId();
            $amazonMenuItems = $this->megamenuManagement->loadMenuItems(0, 'ASC', $amazonMenuId);
        }

        $amazonMenuArray = [];
        $level = 0;

        // Build the menu array
        foreach ($amazonMenuItems as $menuItem) {
            $itemData = [];
            $itemData['item_name'] = $menuItem->getItemName();
            $itemData['item_type'] = $menuItem->getItemType();
            $itemData['category_id'] = $menuItem->getObjectId();
            $itemData['category_display'] = $menuItem->getCategoryDisplay();
            $itemData['item_font_icon'] = $menuItem->getItemFontIcon();

            if ($menuItem->getItemType() == 'pages' || $menuItem->getItemType() == 'link') {
                $itemData['item_link'] = $menuItem->getItemLink();
            } else {
                $itemData['item_link'] = $this->megamenuManagement->generateMenuUrl($menuItem) ?: '#';
            }
            $itemData['open_in_newtab_text'] = $menuItem->getOpenInNewTab() ? 'target="_blank"' : '';

            if ($menuItem->getItemType() == 'category') {
                $_category = $this->megamenuManagement->getCategoryById($menuItem->getObjectId());
                $itemData['vertical_cat_exclude'] = $menuItem->getVerticalCatExclude();
                $itemData['category_level_count'] = $menuItem->getVerticalCatLevel();
                $itemData['item_label'] = $_category->getMdLabel() ?: '';
                $itemData['menu_title'] = $_category->getMdMenuTitle();
                $itemData['item_label_color'] = $_category->getMdLabelTextColor();
                $itemData['item_label_bg_color'] = $_category->getMdLabelBackgroundColor();
                $itemData['item_label_shape'] = $_category->getMdLabelShape();

                if ($menuItem->getCategoryDisplay()) {
                    $childCategories = $this->megamenuManagement->getChildrenCategories($_category);
                    $itemData['child_category'] = $this->getChildCatTempData($childCategories, $itemData);
                }
            }

            $itemData['item_class'] = 'amazon-menu-item child-level-' . $level;
            $itemData['level'] = $level;

            $amazonMenuArray[] = $itemData;
        }
       /* echo "<pre>";print_r($amazonMenuArray);exit;
        exit;*/

        // Generate HTML using recursive function
        $html = '<ul class="amz-menu md-translateX">';

        foreach ($amazonMenuArray as $menuItem) {
            $html .= $this->buildCategoryHtml($menuItem);
        }
        $html .= '</ul>';

        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        return $transportObject->getHtml();
    }

    /**
     * Recursive function to build the menu HTML.
     *
     * @param array $menuItem
     * @return string
     */
    private function buildCategoryHtml(array $menuItem)
    {
        //echo "<pre>";var_dump($menuItem);
        $liClass = isset($menuItem['child_category']) && count($menuItem['child_category']) > 0 ? 'md-amazon-parent' : '';
        $fontIcon = isset($menuItem['item_font_icon'])
            ? '<span class="megaitemicons">' . $menuItem['item_font_icon'] . '</span>'
            : '';

        $menuTitle = (isset($menuItem['menu_title']) ? $menuItem['menu_title'] : $menuItem['item_name']);

        // Start <li> tag
        $html = '<li class="' . $liClass . '">';

        // Build the <a> tag if item_link exists
        if (!empty($menuItem['item_link'])) {
            $html .= '<a href="' . $menuItem['item_link'] . '" ' . $menuItem['open_in_newtab_text'] . '>';
            $html .= $fontIcon . $menuTitle;
            // Add label if present
            if (!empty($menuItem['item_label'])) {
                $inlineStyle = 'style="color: ' . $menuItem['item_label_color'] . '; background-color:' . $menuItem['item_label_bg_color'] . '"';
                $html .= '<span class="md-label-text ' . $menuItem['item_label_shape'] . '"' . $inlineStyle . '>';
                $html .= $menuItem['item_label'] . '</span>';
            }
            $html .= '</a>';
            $html .= '<span></span>';
        } else {
            // If no link, display item name as plain text
            $html .= '<span>' . $fontIcon . $menuTitle . '</span>';
        }

        // Add child categories recursively
        if (!empty($menuItem['child_category'])) {
            $html .= '<ul class="amz-menu md-translateX-right" data-menu-id="' . $menuItem['category_id'] . '">';
            $html .= "<li><a class='md-menu-back-btn' href='#'>".$menuTitle."</a></li>";
            foreach ($menuItem['child_category'] as $childMenuItem) {
                $html .= $this->buildCategoryHtml($childMenuItem);
            }
            //exit;
            $html .= '</ul>';
        }

        // Close <li> tag
        $html .= '</li>';
        return $html;
    }


    /**
     * Get Child Cat Temp Data
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $childCategories
     * @param array $itemData
     * @param int $currentLevel
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getChildCatTempData($childCategories, $itemData, $currentLevel = 1)
    {
        $childCategoryData = [];

        // Get the maximum level to retrieve from $itemData
        $maxLevel = isset($itemData['category_level_count']) ? (int)$itemData['category_level_count'] : 1;

        foreach ($childCategories as $childCategory) {
            if (!in_array(
                $childCategory->getEntityId(),
                explode(',', $itemData['vertical_cat_exclude'] ?? '')
            )) {
                /** @var $childCategory \Magento\Catalog\Model\Category */
                $childCategory = $this->megamenuManagement
                    ->getCategoryById($childCategory->getEntityId());

                // Prepare the base structure for the current category
                $childCatTempData = [
                    'category_id' => $childCategory->getEntityId(),
                    'item_name' => $childCategory->getName(),
                    'item_link' => $childCategory->getUrl(),
                    'level' => $currentLevel,
                    'open_in_newtab_text'=>$childCategory->getOpenInNewTab()??'',
                    'item_class' => $currentLevel === 1 ? "md-amazon-parent md-amazon-title" : "child-level-$currentLevel",
                    'item_label' => $childCategory->getMdLabel() ?? '',
                    'menu_title' => $childCategory->getMdMenuTitle(),
                    'item_label_color' => $childCategory->getMdLabelTextColor(),
                    'item_label_bg_color' => $childCategory->getMdLabelBackgroundColor(),
                    'item_label_shape' => $childCategory->getMdLabelShape(),
                    'child_category' => [] // Initialize an empty child_category array
                ];

                // Recursively fetch subcategories if the current level is less than the max level
                //echo "<pre>";var_dump($maxLevel);

                if ($currentLevel < $maxLevel) {
                    //echo "<pre>";print_r($childCategory->debug());
                    $subChildCategories = $this->megamenuManagement->getChildrenCategories($childCategory);

                    $childCatTempData['child_category'] = $this->getChildCatTempData($subChildCategories, $itemData, $currentLevel + 1);
                }
                $childCategoryData[] = $childCatTempData;
            }
        }

        return $childCategoryData;
    }

    /**
     * Get Mega Menu Html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMegaMenuHtml($outermostClass, $childrenWrapClass, $limit)
    {
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_before',
            ['menu' => $this->_menu, 'block' => $this, 'request' => $this->getRequest()]
        );
        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);

        $html = $this->_getHtml($this->_menu, $childrenWrapClass, $limit);

        if ($this->helper->isEnabled() && $this->isPrimaryMenuSelected()) {
            $currentStore = $this->_storeManager->getStore()->getStoreId();
            $menuStoreId = $this->primaryMenu->getStoreId();
            if ($this->primaryMenu->getIsActive() && in_array($menuStoreId, [0,$currentStore])) {
                $menuItems = $this->megamenuManagement->loadMenuItems(0, 'ASC');
                if ($this->primaryMenu->getMenuType() == Menu::MEGA_MENU) {
                    $html = '';
                    foreach ($menuItems as $item) {
                        $childrenWrapClass = "level0 nav-1 first parent main-parent";
                        if ($this->isCategoryInactive($item)) {
                            continue;
                        }
                        $html .= $this->setMegamenu($item, $childrenWrapClass);
                    }
                } else {
                    $parent = 'root';
                    $level = 0;
                    $html = $this->setPrimaryMenu($menuItems, $level, $parent, $outermostClass);
                }
            }
        }
        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_after',
            ['menu' => $this->_menu, 'transportObject' => $transportObject]
        );
        $html = $transportObject->getHtml();
        return $html;
    }

    /**
     * Get All Category Mega Menu Html
     *
     * @param int $menuId
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllCategoryMegaMenuHtml($menuId, $outermostClass, $childrenWrapClass, $limit)
    {
        $this->allCategoryMenu = $this->getAllCategoryMenuObj($menuId);
        if ($this->helper->isEnabled() && $this->allCategoryMenu->getIsActive()) {
            $menuItems = $this->megamenuManagement->loadMenuItems(0, 'ASC', $menuId); // static id
            if ($this->allCategoryMenu->getMenuType() == Menu::MEGA_MENU) {
                /* If Megamenu then It will display here */
                $html = '';
                foreach ($menuItems as $item) {
                    $childrenWrapClass = "level0 nav-1 first parent main-parent";
                    if ($this->isCategoryInactive($item)) {
                        continue;
                    }
                    $html .= $this->setAllCategoryLeftMegamenu($menuId, $item, $childrenWrapClass);
                }
            } else {
                $parent = 'root';
                $level = 0;
                $html = $this->setPrimaryMenu($menuItems, $level, $parent, $outermostClass, $menuId);
            }
        } else {
            $html = $this->_getHtml($this->_menu, $childrenWrapClass, $limit);
        }
        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $html = $transportObject->getHtml();
        return $html;
    }

    /**
     * Get All Category Right Mega Menu html
     *
     * @param int $menuId
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllCategoryRightMegaMenuHtml($menuId, $outermostClass, $childrenWrapClass, $limit)
    {
        $this->allCategoryMenu = $this->getAllCategoryMenuObj($menuId);
        if ($this->helper->isEnabled() && $this->allCategoryMenu->getIsActive()) {
            $menuItems = $this->megamenuManagement->loadMenuItems(0, 'ASC', $menuId); // static id
            if ($this->allCategoryMenu->getMenuType() == Menu::MEGA_MENU) {
                /* If Megamenu then It will display here */
                $html = '';
                foreach ($menuItems as $item) {
                    $childrenWrapClass = "level0 nav-1 first parent main-parent";
                    if ($this->isCategoryInactive($item)) {
                        continue;
                    }
                    $html .= $this->setAllCategoryRightMegamenu($item, $childrenWrapClass);
                }
            } else {
                $parent = 'root';
                $level = 0;
                $html = $this->setPrimaryMenu($menuItems, $level, $parent, $outermostClass);
            }
        } else {
            $html = $this->_getHtml($this->_menu, $childrenWrapClass, $limit);
        }
        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $html = $transportObject->getHtml();
        return $html;
    }

    /**
     * Set Primary Menu
     *
     * @param \Magedelight\Megamenu\Api\Data\MegamenuInterface $menuItems
     * @param int $level
     * @param string $parent
     * @param string $outermostClass
     * @param int $menuId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setPrimaryMenu(
        $menuItems,
        $level = 0,
        $parent = '',
        $outermostClass = '',
        $menuId = ''
    ) {
        $megaMenuItemData = [
            'menu_block' => $this,
            'menu_items' => $menuItems,
            'level' => $level,
            'parent_node' => $parent,
            'menu_management' => $this->megamenuManagement,
            'menu' => ($menuId) ? $this->megamenuManagement->loadMenuById($menuId) : '',
            'primary_menu' => $this->primaryMenu
        ];
        /*$megaMenuItemBlock = $this->getLayout()->createBlock('Magento\Framework\View\Element\Template');*/
        $megaMenuItemBlock = $this->templateFactory->create();
        /** @var $megaMenuItemBlock \Magento\Framework\View\Element\Template */
        $megaMenuItemBlock->setData($megaMenuItemData);
        $megaMenuItemBlock->setTemplate('Magedelight_Megamenu::menu/items/primaryMenu.phtml');
        return trim(preg_replace('/\s\s+/', ' ', $megaMenuItemBlock->toHtml()));
    }

    /**
     * Get Cms Block Config
     *
     * @param \Magedelight\Megamenu\Model\MenuItems|\Magedelight\Megamenu\Api\Data\MenuItemsInterface $item
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function getCmsBlockConfig($item, $key, $value)
    {
        $blockType = ['header', 'bottom', 'left', 'right'];
        if ($value == 'enable') {
            $initValue = 0;
        }
        if ($value == 'block') {
            $initValue = "";
        }
        if ($value == 'title') {
            $initValue = "0";
        }
        $config[$key] = [$value => $initValue];
        if ($item->getCategoryColumns()) {
            $categoryColumns = json_decode($item->getCategoryColumns());
            foreach ($categoryColumns as $categoryColumn) {
                foreach ($blockType as $type) {
                    if ($categoryColumn->type === $type) {
                        $config[$type] = [
                            'enable' => (int) $categoryColumn->enable,
                            'block' => $categoryColumn->value,
                            'title' => $categoryColumn->showtitle
                        ];
                    }
                }
            }
        }
        return $config[$key][$value];
    }

    /**
     * Set Mega Menu
     *
     * @param \Magedelight\Megamenu\Model\MenuItems|\Magedelight\Megamenu\Api\Data\MenuItemsInterface $item
     * @param string $childrenWrapClass
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setMegamenu($item, $childrenWrapClass)
    {
        $html = '';
        $megaMenuItemData = [
            'menu_block' => $this,
            'menu_item' => $item,
            'menu_management' => $this->megamenuManagement,
            'primary_menu' => $this->primaryMenu
        ];
        $megaMenuItemBlock = $this->templateFactory->create();
        /** @var $megaMenuItemBlock \Magento\Framework\View\Element\Template */
        $megaMenuItemBlock->setData($megaMenuItemData);
        if ($item->getItemType() == 'megamenu') {
            $megaMenuItemBlock->setTemplate('Magedelight_Megamenu::menu/items/megaMenuItemBlock.phtml');
            $html .= trim(preg_replace('/\s\s+/', ' ', $megaMenuItemBlock->toHtml()));
        } else {
            if ($this->primaryMenu->getMenuDesignType() == 'horizontal-vertical') {
                $megaMenuItemBlock
                ->setTemplate('Magedelight_Megamenu::menu/items/horVerMenuItemBlock.phtml');
            } else {
                $megaMenuItemBlock->setTemplate('Magedelight_Megamenu::menu/items/menuItemBlock.phtml');
            }
            $html .= trim(preg_replace('/\s\s+/', ' ', $megaMenuItemBlock->toHtml()));
        }
        return $html;
    }

    /**
     * Set All category Left Mega menu
     *
     * @param int $menuId
     * @param \Magedelight\Megamenu\Model\MenuItems|\Magedelight\Megamenu\Api\Data\MenuItemsInterface $item
     * @param string $childrenWrapClass
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setAllCategoryLeftMegamenu($menuId, $item, $childrenWrapClass)
    {
        $html = '';
        $megaMenuItemData = [
            'menu_block' => $this,
            'menu_item' => $item,
            'menu_management' => $this->megamenuManagement,
            'menu_id' => $menuId
        ];

        /** @var $megaMenuItemBlock \Magento\Framework\View\Element\Template */
        $megaMenuItemBlock = $this->templateFactory->create();
        $megaMenuItemBlock->setData($megaMenuItemData);
        $megaMenuItemBlock
        ->setTemplate('Magedelight_Megamenu::menu/items/allCategoryMenuItemBlockFirstLevel.phtml');
        $html .= trim(preg_replace('/\s\s+/', ' ', $megaMenuItemBlock->toHtml()));
        return $html;
    }
    /**
     * Set All Category Right Mega Menu
     *
     * @param \Magedelight\Megamenu\Model\MenuItems|\Magedelight\Megamenu\Api\Data\MenuItemsInterface $item
     * @param string $childrenWrapClass
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setAllCategoryRightMegamenu($item, $childrenWrapClass)
    {
        $html = '';
        $megaMenuItemData = [
            'menu_block' => $this,
            'menu_item' => $item,
            'menu_management' => $this->megamenuManagement
        ];
        /** @var $megaMenuItemBlock \Magento\Framework\View\Element\Template */
        $megaMenuItemBlock = $this->templateFactory->create();
        $megaMenuItemBlock->setData($megaMenuItemData);
        if ($item->getItemType() == 'megamenu') {
            $megaMenuItemBlock
            ->setTemplate('Magedelight_Megamenu::menu/items/allCategoryMegaMenuItemBlock.phtml');
            $html .= trim(preg_replace('/\s\s+/', ' ', $megaMenuItemBlock->toHtml()));
        } else {
            $megaMenuItemBlock
            ->setTemplate('Magedelight_Megamenu::menu/items/allCategoryMenuItemBlock.phtml');
            $html .= trim(preg_replace('/\s\s+/', ' ', $megaMenuItemBlock->toHtml()));
        }
        return $html;
    }

    /**
     * Get Menu Class
     *
     * @return string
     */
    public function getMenuClass()
    {
        $stickySupported = ['horizontal', 'horizontal-vertical'];
        $class = "menu ";
        $class .= $this->primaryMenu->getMenuDesignType() . ' ';
        $class .= $this->primaryMenu->getMenuAlignment() ?? '';
        if (in_array($this->primaryMenu->getMenuDesignType(), $stickySupported)
            && $this->primaryMenu->getIsSticky() == '1') {
            $class .= ' stickymenu ';
        }
        return $class;
    }

    /**
     * Get Active Class
     *
     * @param \Magedelight\Megamenu\Model\MenuItems|\Magedelight\Megamenu\Api\Data\MenuItemsInterface $menuItem
     * @return string
     */
    public function getActiveClass($menuItem)
    {
        if ($menuItem->getItemType() == 'category') {
            if ($menuItem->getObjectId() == $this->getCurrentCat()) {
                return ' active';
            }
        } elseif ($menuItem->getItemType() == 'pages') {
            if ($menuItem->getObjectId() == $this->getCurentPage()) {
                return ' active';
            }
        }
        return '';
    }

    /**
     * Get Child Column For Menu type
     *
     * @param \Magedelight\Megamenu\Model\MenuItems $menuItems
     * @param string $key
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getChildColumnForMenuType($menuItems, $key)
    {
        $megaMenuItemData = [
            'menu_block' => $this,
            'menu_items' => $menuItems,
            'items_key' => $key,
            'menu_management' => $this->megamenuManagement
        ];
        /** @var $megaMenuItemBlock \Magento\Framework\View\Element\Template */
        $megaMenuItemBlock = $this->templateFactory->create();
        $megaMenuItemBlock->setData($megaMenuItemData);
        $megaMenuItemBlock->setTemplate('Magedelight_Megamenu::menu/items/megaMenuItemBlock/typeMenu.phtml');
        return trim(preg_replace('/\s\s+/', ' ', $megaMenuItemBlock->toHtml()));
    }

    /**
     * Get Child Column For Menu Type block
     *
     * @param \Magedelight\Megamenu\Model\MenuItems $menuItems
     * @param string $key
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getChildColumnForMenuTypeBlock($menuItems, $key)
    {
        $megaMenuItemData = [
            'menu_block' => $this,
            'menu_items' => $menuItems,
            'items_key' => $key,
            'menu_management' => $this->megamenuManagement
        ];
        /** @var $megaMenuItemBlock \Magento\Framework\View\Element\Template */
        $megaMenuItemBlock = $this->templateFactory->create();
        $megaMenuItemBlock->setData($megaMenuItemData);
        $megaMenuItemBlock
        ->setTemplate('Magedelight_Megamenu::menu/items/megaMenuItemBlock/typeBlock.phtml');
        return trim(preg_replace('/\s\s+/', ' ', $megaMenuItemBlock->toHtml()));
    }

    /**
     * Get Child Column for Menu Type Category
     *
     * @param \Magedelight\Megamenu\Model\MenuItems $menuItems
     * @param string $key
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getChildColumnForMenuTypeCategory($menuItems, $key)
    {
        $category = $this->megamenuManagement->getCategoryById($menuItems[$key]->value);
        if ($category) {
            $megaMenuItemData = [
                'menu_block' => $this,
                'menu_items' => $menuItems,
                'items_key' => $key,
                'menu_management' => $this->megamenuManagement,
                'category' => $category,
                'sub_category' => $this->megamenuManagement
                ->getChildrenCategoriesById($category->getId())
            ];
            /** @var $megaMenuItemBlock \Magento\Framework\View\Element\Template */
            $megaMenuItemBlock = $this->templateFactory->create();
            $megaMenuItemBlock->setData($megaMenuItemData);
            $megaMenuItemBlock
            ->setTemplate('Magedelight_Megamenu::menu/items/megaMenuItemBlock/typeCategory.phtml');
            return trim(preg_replace('/\s\s+/', ' ', $megaMenuItemBlock->toHtml()));
        }
    }

    /**
     * Get Child Column For Sub Category
     *
     * @param \Magedelight\Megamenu\Model\MenuItems $menuItems
     * @param int $key
     * @param \Magento\Catalog\Model\Category $category
     * @param \Magento\Catalog\Model\Category $subCats
     * @param boolean $skipTitle
     * @param int $level
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getChildColumnForSubCategory(
        $menuItems,
        $key,
        $category,
        $subCats,
        $skipTitle = false,
        $level = 1
    ) {
        $childHtml = '';
        if (!$skipTitle) {
            $categoryArray = $this->prepareCategoryItemsForMenuColumn($subCats, $menuItems[$key]);
        } else {
            $categoryArray = $subCats;
        }
        /** @var $category \Magento\Catalog\Model\Category */
        if ($menuItems[$key]->showtitle == '1' && !$skipTitle) {
            $childHtml .= '<h2>
                            <a href="'.$category->getUrl().'" >' . __($category->getName()) . '</a>
                            <span class="catblockdropdown">icn</span>
                           </h2>';
        }

        $childHtml .= '<ul class="child-column-megamenu-block child-level-' . $level . '">';
        foreach ($categoryArray as $cat) {
            $verticalclass = $cat['id'] == $this->getCurrentCat() ? 'active' : '';
            $liClass = count($cat['childrens']) > 0 ? 'cat-has-child' : 'cat-no-child';
            $childHtml .= '<li class="' . $liClass . ' ' . $verticalclass . '">';
            $childHtml .= '<a href="' . $cat['url'] . '">' . __($cat['label']) . '</a>';
            if (!empty($cat['childrens'])) {
                $childHtml .= $this->getChildColumnForSubCategory(
                    $menuItems,
                    $key,
                    $category,
                    $cat['childrens'],
                    true,
                    $level + 1
                );
            }
            $childHtml .= '</li>';
        }
        $childHtml .= '</ul>';
        return $childHtml;
    }

    /**
     * Prepare Category Items For Menu Column
     *
     * @param array $subcats
     * @param \Magedelight\Megamenu\Api\Data\MegamenuInterface $item
     * @param int $level
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareCategoryItemsForMenuColumn($subcats, $item, $level = 1)
    {
        $leftArray = [];
        foreach ($subcats as $subcat) {
            $maxLevel = $item->categoryLevel ? (int) $item->categoryLevel : 2;
            if ($maxLevel < $level) {
                break;
            }
            $childrenCats = $this->megamenuManagement->getChildrenCategoriesById($subcat->getId());
            $group = [
                'id' => $subcat->getId(),
                'label' => $subcat->getName(),
                'url' => $subcat->getUrl(),
                'position' => $subcat->getPosition(),
                'childrens' => $this->prepareCategoryItemsForMenuColumn($childrenCats, $item, $level + 1)
            ];
            $leftArray[] = $group;
        }
        return $this->sortByOrder($leftArray, $item);
    }

    /**
     * Sort by Order
     *
     * @param array $categoryArray
     * @param \Magedelight\Megamenu\Api\Data\MegamenuInterface $item
     * @return mixed
     */
    public function sortByOrder($categoryArray, $item)
    {
        usort($categoryArray, function ($x, $y) {
            return strcasecmp($x['position'], $y['position']);
        });
        if ($item->catSortBy && $item->catSortOrder) {
            if ($item->catSortBy == 'name' && $item->catSortOrder == 'asc') {
                usort($categoryArray, function ($x, $y) {
                    return strcasecmp($x['label'], $y['label']);
                });
            }
            if ($item->catSortBy == 'name' && $item->catSortOrder == 'desc') {
                usort($categoryArray, function ($x, $y) {
                    return strcasecmp($y['label'], $x['label']);
                });
            }
            if ($item->catSortBy == 'position' && $item->catSortOrder == 'desc') {
                usort($categoryArray, function ($x, $y) {
                    return strcasecmp($y['position'], $x['position']);
                });
            }
        }
        return $categoryArray;
    }

    /**
     * Set Child Category Column
     *
     * @param array $subcats
     * @param \Magedelight\Megamenu\Api\Data\MenuItemsInterface $item
     * @param int $columnCount
     * @param boolean $childs
     * @param int $level
     * @return string
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setChildCategoryColumn(
        $subcats,
        $item,
        $columnCount = 0,
        $childs = false,
        $level = 1
    ) {
        if (!$childs) {
            $categoryArray = $this->prepareCategoryItems($subcats, $item);
        } else {
            $categoryArray = $subcats;
        }
        if (!$categoryArray && !$item->getProductDisplay()) {
            return '';
        }
        $html = '';
        $ulClass = '';
        $countChild = '';

        if ($columnCount !== 0) {
            $ulClass .= 'column' . $columnCount . ' child-level-1';
        } else {
            $ulClass .= 'child-level-' . $level;
        }

        $openInNewTabText = '';
        if ($item->getOpenInNewTab()) {
            $openInNewTabText = 'target="_blank"';
        }

        if (!$categoryArray && $item->getProductDisplay()) {
            $html .= $this->getSingleCategoryProducts($html, $item, $ulClass, $openInNewTabText, $level);
            return $html;
        }

        $html .= '<ul class="' . $ulClass . '">';
        foreach ($categoryArray as $cat) {
            $verticalclass = $cat['id'] == $this->getCurrentCat() ? 'active' : '';
            $uniqueClass = 'category-item nav-' . $item->getItemId() . '-' . $cat['id'];
            if ($item->getCategoryDisplay()) {
                $countChild = $this->getCategoryCount($cat['id']);
            }
            if ($item->getProductDisplay()) {
                $countChild = $this->getProductCount($cat['id']);
            }
            $liClass = $uniqueClass . ' ' . $verticalclass;
            $html .= '<li class=' . $liClass . '">';
            $html .= '<a href="' . $cat['url'] . '" '
            . $openInNewTabText . '>' . $this->getCategoryIconImageHtml($cat['id']) . __($cat['label'])
            . $countChild . $this->getCategoryMenuLabelHtml($cat['id']) . '</a>';
            if ($item->getProductDisplay()) {
                $html .= $this->getCategoryProducts($cat, $item, $level + 1);
            } else {
                if ((int) $item->getCategoryVerticalMenu() !== (int) 1) {
                    $html .= $this->setChildCategoryColumn($cat['childrens'], $item, 0, true, $level+1);
                } else {
                    $html .= $this->setVerticalChildCategoryColumn($cat['childrens'],$level+1);
                }
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Get Single Category Products
     *
     * @param string $html
     * @param \Magedelight\Megamenu\Api\Data\MegamenuInterface $item
     * @param string $ulClass
     * @param string $openInNewTabText
     * @param int $level
     * @return $sting
     */
    private function getSingleCategoryProducts($html, $item, $ulClass, $openInNewTabText, $level)
    {
        $html .= '<ul class="' . $ulClass . '">';
        $_category = $this->megamenuManagement->getCategoryById($item->getObjectId());
        $verticalclass = $_category->getId() == $this->getCurrentCat() ? 'active' : '';
        $uniqueClass = 'category-item nav-' . $item->getItemId() . '-' . $_category->getId();
        if ($item->getCategoryDisplay()) {
            $countChild = $this->getCategoryCount($_category->getId());
        }
        if ($item->getProductDisplay()) {
            $countChild = $this->getProductCount($_category->getId());
        }
        $liClass = $uniqueClass . ' ' . $verticalclass;
        $html .= '<li class=' . $liClass . '">';
        $html .= '<a href="' . $_category->getUrl() . '" ' . $openInNewTabText . '>
                        ' . __($_category->getName()) .
                        $countChild . $this->getCategoryMenuLabelHtml($_category->getId()) . '
                        </a>';
        if ($item->getProductDisplay()) {
            $catArray = [
                'id' => $_category->getId(),
                'url' => $_category->getUrl()
            ];
            $html .= $this->getCategoryProducts($catArray, $item, $level + 1);
        }
        $html .= '</li>';
        $html .= '</ul>';

        return $html;
    }
    /**
     * Set All Category Child Category Column
     *
     * @param array $subcats
     * @param \Magedelight\Megamenu\Api\Data\MegamenuInterface $item
     * @param int $menuId
     * @param int $columnCount
     * @param bool $childs
     * @param int $level
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setAllCatChildCategoryColumn(
        $subcats,
        $item,
        $menuId,
        $columnCount = 0,
        $childs = false,
        $level = 1
    ) {
        if (!$childs) {
            $categoryArray = $this->prepareCategoryItems($subcats, $item);
        } else {
            $categoryArray = $subcats;
        }

        if (!$categoryArray) {
            return '';
        }
        $html = '';
        $ulClass = '';
        $isLevel2 = false;
        $count = 0;
        $hidden = '';
        $noOfSubCategoryToShow = $this->getNoOfSubCategoryToShow($menuId);
        if ($columnCount !== 0) {
            $ulClass .= 'column' . $columnCount . ' child-level-1';
        } else {
            $ulClass .= 'child-level-' . $level;
            if ($level == 2) {
                $isLevel2 = true;
            }
        }
        $html .= '<ul class="' . $ulClass . '">';
        foreach ($categoryArray as $cat) {
            $catIconImgHtml = $this->getCategoryIconImageHtml($cat['id']);
            $countChild = $this->getChildCategoryCount($cat);
            $verticalclass = $cat['id'] == $this->getCurrentCat() ? 'active' : '';
            $uniqueClass = 'category-item nav-' . $item->getItemId() . '-' . $cat['id'];
            $liClass = $uniqueClass . ' ' . $verticalclass;
            if ($isLevel2 && $count == $noOfSubCategoryToShow) {
                $hidden = 'style="display: none"';
            }
            $html .= '<li class="' . $liClass . '" ' . $hidden . '>';
            $html .= '<a href="' . $cat['url'] . '">' .
            $catIconImgHtml . __($cat['label']) . '<span class="category-count">' .
             $countChild . '</span>' . $this->getCategoryMenuLabelHtml($cat['id']) . '</a>';
            $html .=
            $this->setAllCatChildCategoryColumn(
                $cat['childrens'],
                $item,
                $menuId,
                0,
                true,
                $level + 1
            );
            $html .= '</li>';
            $count++;
        }
        if ($isLevel2 && count($categoryArray) > $noOfSubCategoryToShow) {
            $html .= '<li class="show-less" ' .
            $hidden . '><a href="#">Show Less</a></li><li class="show-more"><a href="#">Show More</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Prepare Category Items
     *
     * @param array $subcats
     * @param \Magedelight\Megamenu\Api\Data\MegamenuInterface $item
     * @param int $level
     * @return array
     */
    public function prepareCategoryItems($subcats, $item, $level = 1)
    {
        $leftArray = [];
        $sortArray = "";
        /** @var $subcats \Magento\Catalog\Model\ResourceModel\Category\Collection */
        if ($item->getVerticalCatSortby() && $item->getVerticalCatSortorder()) {
            $sortArray = [
                'sort_by' => $item->getVerticalCatSortby(),
                'sort_order' => $item->getVerticalCatSortorder()
            ];
        }
        foreach ($subcats as $subcat) {
            if (in_array($subcat->getId(), $this->getExcludeCategoryItemId($item))) {
                continue;
            }
            $maxLevel = $item->getVerticalCatLevel() ? (int) $item->getVerticalCatLevel() : 2;
            if ($maxLevel < $level) {
                break;
            }
            $_category = $this->megamenuManagement->getCategoryById($subcat->getId());
            $childrenCats = $this->megamenuManagement->getChildrenCategoriesById($subcat->getId(), $sortArray);
            $group = [
                'id' => $subcat->getId(),
                'label' => $_category->getMdMenuTitle() ? $_category->getMdMenuTitle() : $_category->getName(),
                'url' => $_category->getUrl(),
                'childrens' => $this->prepareCategoryItems($childrenCats, $item, $level + 1)
            ];
            $leftArray[] = $group;
        }
        return $leftArray;
    }

    /**
     * Get Exclude Category Item Id
     *
     * @param \Magedelight\Megamenu\Api\Data\MegamenuInterface $item
     * @return array
     */
    public function getExcludeCategoryItemId($item)
    {
        $categories = [];
        $excludeCategory = $item->getVerticalCatExclude();
        if ($excludeCategory) {
            $categories = explode(',', $excludeCategory);
        }
        return $categories;
    }

    /**
     * Set Vertical CategoryItem
     *
     * @param \Magedelight\Megamenu\Api\Data\MegamenuInterface $item
     * @param array $subcats
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setVerticalCategoryItem($item, $subcats)
    {
        if (count($subcats) == 0 && $item->getProductDisplay()) {
            $childHtml = '<div class="col-menu-9 vertical-menu-content">';
            $html = '<div class="col-menu-3 vertical-menu-left" style="background:#' .
            $item->getCategoryVerticalMenuBg() . ';">';
            $html .= '<ul class="vertical-menu-left-nav">';
            $level = 1;
            $_category = $this->megamenuManagement->getCategoryById($item->getObjectId());
            $verticalclass = 'active';
            $addDropdownClass = !empty($childrenCats) ? " dropdown" : "";
            $uniqueClass = 'menu-vertical-items nav-' . $item->getItemId();
            $liClass = $uniqueClass . ' ' . $verticalclass . ' ' . $addDropdownClass;
            $datToggle = 'subcat-tab-' . $_category->getId();
            $html .= '<li class="' . $liClass . '" data-toggle="' . $datToggle . '">';
            $html .= '<a href="' . $_category->getUrl() . '">' . __($_category->getName()) . '</a>';
            $html .= '</li>';
            if ($item->getProductDisplay()) {
                $childHtml .= '<div id="' . $datToggle . '" class="vertical-subcate-content">';
                $catArray = [
                    'id' => $_category->getId(),
                    'url' => $_category->getUrl()
                ];
                $childHtml .= $this->getCategoryProducts($catArray, $item, $level + 1);
                $childHtml .= '</div>';
            }
            $html .= '</ul>';
            $html .= '</div>';
            $childHtml .= '</div>';
            return $html . $childHtml;
        }

        $leftArray = $this->prepareCategoryItems($subcats, $item);
        $childHtml = '<div class="col-menu-9 vertical-menu-content">';
        $html = '<div class="col-menu-3 vertical-menu-left" style="background:#' .
        $item->getCategoryVerticalMenuBg() . ';">';
        $html .= '<ul class="vertical-menu-left-nav">';
        $level = 1;
        foreach ($leftArray as $key => $subcat) {
            $verticalclass = $subcat['id'] == $this->getCurrentCat() ? 'active' : '';
            $addDropdownClass = !empty($childrenCats) ? " dropdown" : "";
            $uniqueClass = 'menu-vertical-items nav-' . $item->getItemId();
            $liClass = $uniqueClass . ' ' . $verticalclass . ' ' . $addDropdownClass;
            $datToggle = 'subcat-tab-' . $subcat['id'];
            $html .= '<li class="' . $liClass . '" data-toggle="' . $datToggle . '">';
            $html .= '<a href="' . $subcat['url'] . '">' . __($subcat['label']) . '</a>';
            $html .= '</li>';
            if ($item->getProductDisplay()) {
                $childHtml .= '<div id="' . $datToggle . '" class="vertical-subcate-content">';
                $childHtml .= $this->getCategoryProducts($subcat, $item, $level + 1);
                $childHtml .= '</div>';
            } else {
                $childHtml .= $this->setVerticalRightParentItem($subcat);
            }
        }
        $html .= '</ul>';
        $html .= '</div>';
        $childHtml .= '</div>';
        return $html . $childHtml;
    }

    /**
     * Set Vertical Right Parent Item
     *
     * @param mixed $childrens
     * @return string
     */
    public function setVerticalRightParentItem($childrens)
    {
        $html = '';
        $columnCountForVerticalMenu = count($childrens['childrens']) >= 3 ? 3 : count($childrens['childrens']);
        $html .= '<div id="subcat-tab-' . $childrens['id'] . '" class="vertical-subcate-content">';
        $html .= '<ul class="menu-vertical-child child-level-3 column' . $columnCountForVerticalMenu . '">';
        foreach ($childrens['childrens'] as $child) {
            $verticalclass = $child['id'] == $this->getCurrentCat() ? 'active' : '';
            $html .= '<li class="' . $verticalclass . '">';
            $html .= '<h4 class="level-3-cat">';
            $html .= '<a href="' . $child['url'] . '">' . $child['label'] . '</a>';
            $html .= '</h4>';
            $html .= $this->setVerticalRightChildItem($child);
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Set Vertical Right ChildItem
     *
     * @param mixed $childrens
     * @param int $level
     * @return string
     */
    public function setVerticalRightChildItem($childrens, $level = 4)
    {
        $html = '';
        if (empty($childrens['childrens'])) {
            return '';
        }
        $html .= '<ul class="menu-vertical-child-item child-level-' . $level . '">';
        foreach ($childrens['childrens'] as $child) {
            $verticalclass = $child['id'] == $this->getCurrentCat() ? 'active' : '';
            $html .= '<li class="' . $verticalclass . '">';
            $html .= '<a href="' . $child['url'] . '">' . $child['label'] . '</a>';
            if (!empty($child['childrens'])) {
                $html .= $this->setVerticalRightChildItem($child, $level + 1);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Get Block Object Html
     *
     * @param int $id
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockObjectHtml($id)
    {
        $blockObject = $this->getLayout()->createBlock(\Magento\Cms\Block\Block::class);
        $blockObject->setBlockId($id);
        return $blockObject->toHtml();
    }

   /**
    * Create Cms BlockHtml
    *
    * @param int $id
    * @param string $title
    * @param string $class
    * @return string
    */
    public function createCmsBlockHtml($id, $title, $class)
    {
        $html = '';
        $headerblock = $this->megamenuManagement->loadCmsBlock($id);
        $html .= '<li class="' . $class . '">';
        if ($title === '1') {
            $html .= '<h2>' . $headerblock->getTitle() . '</h2>';
        }
        $html .= '<ul><li>' . $this->getBlockObjectHtml($id) . '</li>';
        $html .= '</ul></li>';
        return $html;
    }

    /**
     * Get Menu Style Html
     *
     * @return string
     */
    public function menuStyleHtml()
    {
        if (!($this->primaryMenu->getMenuStyle()==null)) {
            if (!empty(trim($this->primaryMenu->getMenuStyle()))) {
                return '<style>' . $this->primaryMenu->getMenuStyle() . '</style>';
            }
        }
        return '';
    }

    /**
     * Get Animation Time
     *
     * @return mixed
     */
    public function animationTime()
    {
        return $this->helper->getConfig('magedelight/general/animation_time');
    }

    /**
     * GetConfigBurgerStatus
     *
     * @return bool
     */
    public function getConfigBurgerStatus()
    {
        if ($this->helper->isEnabled() && $this->helper->isHumbergerMenu()) {
            return true;
        }

        return false;
    }

    /**
     * Get Menu Item Classes
     *
     * @param Node $item
     * @return array
     */
    protected function _getMenuItemClasses(Node $item)
    {
        $classes = parent::_getMenuItemClasses($item);

        /* Burger menu for desktop */
        if ($this->getConfigBurgerStatus()) {
            if ($item->getLevel() == 1) {
                if (!empty($this->mdColumnCount) && $this->mdColumnCount != 0) {
                    $classes[] = 'col-' . $this->mdColumnCount;
                }
            }
        }
        return $classes;
    }

    /**
     * Add Sub Menu
     *
     * @param \Magento\Framework\Data\Tree\Node $child
     * @param string $childLevel
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        /* Burger menu for desktop */
        if (!$this->getConfigBurgerStatus()) {
            return parent::_addSubMenu($child, $childLevel, $childrenWrapClass, $limit);
        }

        $html = '';

        if ($childLevel == 0) {
            $catIdArray = explode('-', $child->getId());
            $this->categoryData = $this->megamenuManagement->getCategoryById(end($catIdArray));
            if ($this->categoryData) {
                $getLabel = $this->categoryData->getData('md_label');
                $this->getDescription = $this->categoryData->getData('md_category_editor');
                $labelShape = $this->categoryData->getData('md_label_shape');
                $color = $this->categoryData->getData('md_label_text_color');
                $backgroundColor = $this->categoryData->getData('md_label_background_color');
                $this->mdColumnCount = $this->categoryData->getData('md_column_count');
                if (isset($getLabel) && $getLabel != '') {
                    $labelClasses = "md-label-text ".$labelShape;
                    $html .= '<span class="'.$labelClasses.'" style="color:' .
                    $color . '!important;background-color:' .
                        $backgroundColor . '!important; ">' . __($getLabel) . '</span>';
                }
            }
        }

        if (!$child->hasChildren()) {
            return $html;
        }

        $colStops = [];
        if ($childLevel == 0 && $limit) {
            $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }

        if ($childLevel == 0) {
            $html .= '<ul class="level' . $childLevel . ' ' . $childrenWrapClass
            . '"><li class="md-submenu-container"><ul class="md-categories">';
            $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
            $html .= '</ul><ul class="md-categories-image"><li>'
            . $this->output->categoryAttribute(
                $this->categoryData,
                $this->getDescription,
                'md_category_editor'
            ) . '</li></ul></li></ul>';
        } else {
            $html .= '<ul class="level' . $childLevel . ' ' . $childrenWrapClass . '">';
            $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
            $html .= '</ul>';
        }

        return $html;
    }

    /**
     * Get Primary Menu Obj
     *
     * @return \Magedelight\Megamenu\Api\Data\ConfigInterface
     */
    public function getPrimaryMenuObj()
    {
        if ($this->helper->isEnabled()) {
            $_customerSession = $this->customerSession->create();
            if ($_customerSession->isLoggedIn()) {
                /** @var  \Magedelight\Megamenu\Api\MegamenuInterface|DataObject */
                $this->primaryMenu = $this->megamenuManagement->getMenuData(
                    $_customerSession->getCustomerId()
                )->getMenu();
            } else {
                 /** @var  \Magedelight\Megamenu\Api\MegamenuInterface|DataObject */
                $this->primaryMenu = $this->megamenuManagement->getMenuData()->getMenu();
            }
        }
        return $this->primaryMenu;
    }

    /**
     * Get Horizontal Menu Html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHorizontalMenuHtml($outermostClass, $childrenWrapClass, $limit)
    {
        $this->primaryMenu = $this->getPrimaryMenuObj();
        if ($this->helper->isEnabled() && $this->primaryMenu->getIsActive()) {
            $menuItems = $this->megamenuManagement->loadMenuItems(0, 'ASC');
            if ($this->primaryMenu->getMenuType() == Menu::MEGA_MENU) {
                $html = '';
                foreach ($menuItems as $item) {
                    $childrenWrapClass = "level0 nav-1 first parent main-parent";
                    $html .= $this->setMegamenu($item, $childrenWrapClass);
                }
            } else {
                $parent = 'root';
                $level = 0;
                $html = $this->setPrimaryMenu($menuItems, $level, $parent, $outermostClass);
            }
        } else {
            $html = $this->_getHtml($this->getMenu(), $childrenWrapClass, $limit);
        }
        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $html = $transportObject->getHtml();
        return $html;
    }

    /**
     * Is Category Inactive
     *
     * @param \Magedelight\Megamenu\Api\Data $item
     * @return boolean
     */
    public function isCategoryInactive($item)
    {
        try {
            if ($item->getItemType() == 'category') {
                $category = $this->categoryRepository->get($item->getObjectId());
                if (!$category->getIsActive()) {
                    return true;
                }
            }
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return false;
    }

    /**
     * Is All Category Mega Menu Selected
     *
     * @param int $menuId
     * @return boolean
     */
    public function isAllCategoryMegaMenuSelected($menuId)
    {
        if ($this->helper->isEnabled()) {
            $allCategoryMenu = $this->getAllCategoryMenuObj($menuId);
            if ($allCategoryMenu->getIsActive() &&
                $allCategoryMenu->getMenuType() == Menu::MEGA_MENU
                && $allCategoryMenu->getMenuDesignType() == self::ALL_CATEGORY_MENU
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get No Of SubCategory To Show
     *
     * @param int $menuId
     * @return int
     */
    public function getNoOfSubCategoryToShow($menuId)
    {
        $allCategoryMenu = $this->getAllCategoryMenuObj($menuId);
        if ($this->isAllCategoryMegaMenuSelected($menuId)) {
            return $allCategoryMenu->getNoOfSubCategoryToShow();
        }
        return 3;
    }

    /**
     * Get All Category Menu Obj
     *
     * @param int $menuId
     * @return \Magedelight\Megamenu\Model\Menu
     */
    public function getAllCategoryMenuObj($menuId)
    {
        if (!$this->helper->isEnabled()) {
            return null;
        }
        if (isset($this->allCategoryMenuData[$menuId])) {
            return $this->allCategoryMenuData[$menuId];
        }
        $this->allCategoryMenuData[$menuId] = $this->megamenuManagement->loadMenuById($menuId);

        return $this->allCategoryMenuData[$menuId];
    }

    /**
     * Get All Category Menu title
     *
     * @param int $menuId
     * @return string
     */
    public function getAllCategoryMenuTitle($menuId)
    {
        $allCategoryMenuObj = $this->getAllCategoryMenuObj($menuId);
        return $allCategoryMenuObj->getVerticalMenuTitle();
    }

    /**
     * Get All Category Navigation Class
     *
     * @param int $menuId
     * @return string
     */
    public function getAllCategoryNavigationClass($menuId)
    {
        $verticalNavigationClasses = '';
        if ($this->isAllCategoryMegaMenuSelected($menuId)) {
            $verticalNavigationClasses = 'all-category-megamenu-navigation';
        } else {
            $verticalNavigationClasses =  'vertical-navigation';
        }
        return $verticalNavigationClasses;
    }

    /**
     * Get Show Vertical MenuOn
     *
     * @param int $menuId
     * @return string
     */
    public function getShowVerticalMenuOn($menuId)
    {
        $allCategoryMenuObj = $this->getAllCategoryMenuObj($menuId);
        return $allCategoryMenuObj->getShowVerticalMenuOn();
    }

    /**
     * Get Category Icon Image Html
     *
     * @param int $categoryId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCategoryIconImageHtml($categoryId)
    {
        try {
            $category = $this->categoryRepository->get($categoryId);
            $image = $this->getCategoryIconImage();
            $imgUrl = $image ? $image->getUrl($category) : $this->image->getUrl($category, 'category_icon_image');
            if ($imgUrl) {
                return '<span class="category-icon-image"><img src="'
                    . $this->_escaper->escapeUrl($imgUrl)
                    . '" alt="'
                    . $this->_escaper->escapeHtmlAttr($category->getName())
                    . '" title="'
                    . $this->_escaper->escapeHtmlAttr($category->getName())
                    . '" /></span>';
            }
            return '';
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get Child Category Count
     *
     * @param array $cat
     * @return string
     * @throws NoSuchEntityException
     */
    public function getChildCategoryCount($cat)
    {
        $categoryId = $cat['id'];
        $category = $this->categoryRepository->get($categoryId);
        $childCatCount = '';
        if ($countChild = $category->getChildrenCount()) {
            $childCatCount = ' (' . $countChild . ')';
        }
        return $childCatCount;
    }

    /**
     * Get All Category Menu Items
     *
     * @return \Magedelight\Megamenu\Model\ResourceModel\Menu\Collection
     * @throws NoSuchEntityException
     */
    public function getAllCategoryMenuItems()
    {
        $allCategoryMenuItemCollection = $this->megamenuManagement->loadAllMegaMenus();
        $allCategoryMenuItemCollection = $allCategoryMenuItemCollection
        ->addFieldToFilter('menu_design_type', self::ALL_CATEGORY_MENU);
        return $allCategoryMenuItemCollection;
    }

    /**
     * Is All Category Menu Created
     *
     * @return boolean
     */
    public function isAllCategoryMenuCreated()
    {
        if ($this->getAllCategoryMenuItems() && count($this->getAllCategoryMenuItems()) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get Category Menu Label Html
     *
     * @param int $categoryId
     * @return string
     */
    public function getCategoryMenuLabelHtml($categoryId)
    {
        $html = '';
        $this->categoryData = $this->megamenuManagement->getCategoryById($categoryId);
        if ($this->categoryData) {
            $getLabel = $this->categoryData->getData('md_label');
            $this->getDescription = $this->categoryData->getData('md_category_editor');
            $color = $this->categoryData->getData('md_label_text_color');
            $backgroundColor = $this->categoryData->getData('md_label_background_color');
            $labelShape = $this->categoryData->getData('md_label_shape') ?
            $this->categoryData->getData('md_label_shape') : '';
            if (isset($getLabel) && $getLabel != '') {
                $html .= '<span class="md-label-text ' . $labelShape . '" style="color:' .
                $color . '!important;background-color:' .
                    $backgroundColor . '!important; ">' . __($getLabel) . '</span>';
            }
        }
        return $html;
    }

    /**
     * Is All Category Menga menu
     *
     * @param int $menuId
     * @return boolean
     */
    public function isAllCategoryMegamenu($menuId)
    {
        $menu = $this->megamenuManagement->loadMenuById($menuId);
        if ($menu->getMenuType() == Menu::MEGA_MENU) {
            return true;
        }
        return false;
    }

    /**
     * Is Primary Menu Selected
     *
     * @return int|null
     */
    public function isPrimaryMenuSelected()
    {
        $primaryMenu = $this->helper->isPrimaryMenuSelected();
        if ($primaryMenu === self::PRIMARY_NONE) {
            return null;
        }

        $menu = $this->megamenuManagement->loadMenuById($primaryMenu);
        if (!$menu->getIsActive()) {
            return null;
        }

        return $primaryMenu;
    }

    /**
     * Get Display Position
     *
     * @param int $menuId
     * @return \Magedelight\Megamenu\Model\Menu
     */
    public function getDisplayPosition($menuId)
    {
        $allCategoryMenuObj = $this->getAllCategoryMenuObj($menuId);
        return $allCategoryMenuObj->getDisplayPosition();
    }

    /**
     * Get Display Overlay
     *
     * @param int $menuId
     * @return void
     */
    public function getDisplayOverlay($menuId)
    {
        $allCategoryMenuObj = $this->getAllCategoryMenuObj($menuId);
        return $allCategoryMenuObj->getDisplayOverlay();
    }

    /**
     * Get Primary Menu Display Overlay
     *
     * @return boolean
     */
    public function getPrimaryMenuDisplayOverlay()
    {
        if ($this->primaryMenu->getDisplayOverlay()) {
            return true;
        }
        return false;
    }

    /**
     * Get Category Products
     *
     * @param array $cat
     * @param \Magedelight\Megamenu\Model\MenuItems $item
     * @param int $level
     * @return void
     */
    public function getCategoryProducts($cat, $item, $level)
    {
        $html = '';
        $categoryId = $cat['id'];
        $categoryUrl = $cat['url'];
        $sortBy = $item->getVerticalCatSortby();
        $sortOrder = $item->getVerticalCatSortorder();
        $ulClass = 'child-level-' . $level;
        $showMoreStatus = $this->primaryMenu->getShowViewMore();
        $noOfSubCategoryToShow = $this->primaryMenu->getNoOfSubCategoryToShow();
        $excludeProductIds = explode(',', $item->getVerticalCatExclude() ?? '');
        $category = $this->categoryFactory->create()->load($categoryId);
        $categoryProducts = $category->getProductCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('md_menu_label')
            ->addAttributeToSelect('md_menu_label_shape')
            ->addAttributeToSelect('md_label_text_color')
            ->addAttributeToSelect('md_label_background_color')
            ->setOrder($sortBy, $sortOrder)
            ->addAttributeToFilter('entity_id', ['nin' => $excludeProductIds])
            ->setPageSize($noOfSubCategoryToShow);
        if ($categoryProducts->getSize() < 1) {
            return '';
        }
        $html .= '<ul class="' . $ulClass . '">';
        foreach ($categoryProducts as $product) {
            $html .= '<li class="product-item"><a href="' . $product->getProductUrl()
            . '" title="Explore ' . $product->getName() . '">';
            $html .= __($product->getName());
            if ($product->getMdMenuLabel()) {
                $inlineStyle = 'style="color: ' . $product->getMdLabelTextColor()
                . '; background-color:' . $product->getMdLabelBackgroundColor() . '"';
                $html .= '<span class="md-label-text ' . $product->getMdMenuLabelShape()
                . '" ' . $inlineStyle . '>' . $product->getMdMenuLabel() . '</span>';
            }
            $html .= '</a></li>';
        }
        /*if(count($categoryProducts) > $noOfSubCategoryToShow){*/
        if ($showMoreStatus) {
            $html .= '<li class="view_more"><a href="' . $categoryUrl . '">View More</a></li>';
        }
        /*}*/
        $html .= '</ul>';
        return $html;
    }

    /**
     * Get Amazon Menus
     *
     * @return \Magedelight\Megamenu\Model\ResourceModel\Menu\Collection
     */
    public function getAmazonMenus()
    {
        $megaMenuCollection = $this->megamenuManagement->loadAllMegaMenus();
        $megaMenuCollection = $megaMenuCollection->addFieldToFilter(
            'menu_design_type',
            self::AMAZON_MENU
        );
        return $megaMenuCollection;
    }

    /**
     * Is Amazon Menu Created
     *
     * @return boolean
     */
    public function isAmazonMenuCreated()
    {
        if ($this->getAmazonMenus() && count($this->getAmazonMenus()) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Set Vertical Child Category Column
     *
     * @param array $subcats
     * @param integer $level
     * @return string
     */
    private function setVerticalChildCategoryColumn(
        $subcats,
        $level = 1
    ) {
        $html = '';
        $ulClass = 'child-level-' . $level;
        $html .= '<div class="md-hv-right">';
        $html .= '<ul class="' . $ulClass . '">';
        foreach ($subcats as $subcat) {
            $html .= '<li class="product-item"><a href="' .
            $subcat['url'] . '" title="Explore ' .
            $subcat['label'] . '">';
            $html .= __($subcat['label']);
            $html .= $this->getCategoryMenuLabelHtml($subcat['id']);
            $html .= '</a></li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Get Category Count
     *
     * @param int $catId
     * @return string
     */
    public function getCategoryCount($catId)
    {
        $showCatCount = $this->primaryMenu->getShowCategoryCount();
        $countChild = '';
        if ($showCatCount) {
            $categoryLoad = $this->megamenuManagement->getCategoryById($catId);
            $countChild = '<span class="category-count"> (' . $categoryLoad->getChildrenCount() . ')</span>';
        }
        return $countChild;
    }

    /**
     * Get Product Couunt
     *
     * @param int $catId
     * @return string
     */
    public function getProductCount($catId)
    {
        $showCatCount = $this->primaryMenu->getShowCategoryCount();
        $countChild = '';
        if ($showCatCount) {
            $categoryLoad = $this->megamenuManagement->getCategoryById($catId);
            $countChild = '<span class="category-count"> (' .
            $categoryLoad->getProductCollection()->count() . ')</span>';
        }
        return $countChild;
    }
}
