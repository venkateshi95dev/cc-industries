<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Controller\Adminhtml\Sampleimport;

use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\SearchResultIteratorFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magedelight\Megamenu\Model\MenuItemsFactory;
use Magento\Cms\Model\BlockFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use function SpomkyLabs\Pki\ASN1\Type\string;

class Export extends \Magento\Backend\App\Action
{
    /**
     * @var WriteInterface
     */
    protected $directory;

    /**
     * Static Block Value
     *
     * @var string
     */
    protected $staticBlocks;

    /**
     * available static Block store
     *
     * @var array
     */
    protected $staticBlocksStore = [];

    /**
     * assign menu html
     *
     * @var string
     */
    protected $menus;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var SearchResultIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var MenuItemsFactory
     */
    protected $menuItemsFactory;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var string
     */
    private $menuItems;

    /**
     * Export constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param SearchResultIteratorFactory $iteratorFactory
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param MenuItemsFactory $menuItemsFactory
     * @param BlockFactory $blockFactory
     * @param EncryptorInterface $encryptor
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Context $context,
        Filter $filter,
        SearchResultIteratorFactory $iteratorFactory,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        MenuItemsFactory $menuItemsFactory,
        BlockFactory $blockFactory,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->filter = $filter;
        $this->iteratorFactory = $iteratorFactory;
        $this->fileFactory = $fileFactory;
        $this->menuItemsFactory = $menuItemsFactory;
        $this->blockFactory = $blockFactory;
        $this->encryptor = $encryptor;
    }

    /**
     * Imports country list from csv file
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        return $this->fileFactory->create('export.xml', $this->getXmlFile(), 'var');
    }

    /**
     * Get XMl File array
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getXmlFile()
    {
        $name = $this->encryptor->hash(microtime());
        $component = $this->filter->getComponent();
        $file = 'export/' . $component->getName() . $name . '.xml';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();

        $component->getContext()->getDataProvider()->setLimit(0, 0);

        /** @var SearchResultInterface $searchResult */
        $searchResult = $component->getContext()->getDataProvider()->getSearchResult();

        /** @var DocumentInterface[] $searchResultItems */
        $searchResultItems = $searchResult->getItems();

        $searchResultIterator = $this->iteratorFactory->create(['items' => $searchResultItems]);

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->write('<root>');
        $this->menus = '<menus>';
        $this->menuItems = '<menu_items>';
        $this->staticBlocks = '<blocks>';

        foreach ($searchResultIterator as $single) {
            $this->createMenus($single);
        }

        $this->menus .= '</menus>';
        $this->menuItems .= '</menu_items>';
        $this->staticBlocks .= '</blocks>';

        $stream->write($this->menus);
        $stream->write($this->menuItems);
        $stream->write($this->staticBlocks);
        $stream->write('</root>');
        $stream->unlock();
        $stream->close();
        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }

    /**
     * Create Menu
     *
     * @param \Magedelight\Megamenu\Model\Menu $menu
     * @return void
     */
    public function createMenus($menu)
    {
        if (!empty($menu->getData())) {
            $this->menus .= '<item>';
            $this->menus .= '<menu_id>' . $menu->getMenuId() . '</menu_id>';
            $this->menus .= '<menu_name>' . $menu->getMenuName() . '</menu_name>';
            $this->menus .= '<menu_design_type>' . $menu->getMenuDesignType() . '</menu_design_type>';
            $this->menus .= '<menu_style><![CDATA[' . $menu->getMenuStyle() . ']]></menu_style>';
            $this->menus .= '<is_active>' . $menu->getIsActive() . '</is_active>';
            $this->menus .= '<menu_type>' . $menu->getMenuType() . '</menu_type>';
            $this->menus .= '<customer_groups>' . $menu->getCustomerGroups() . '</customer_groups>';
            $this->menus .= '<is_sticky>' . $menu->getIsSticky() . '</is_sticky>';
            $this->menus .= '<menu_alignment>' . $menu->getMenuAlignment() . '</menu_alignment>';
            $this->menus .= '<show_verticalmenu_with_megamenu>' .
            $menu->getShowVerticalmenuWithMegamenu() . '</show_verticalmenu_with_megamenu>';
            $this->menus .= '<show_vertical_menu_on>' .
            $menu->getShowVerticalMenuOn() . '</show_vertical_menu_on>';
            $this->menus .= '<display_position>' . $menu->getDisplayPosition() . '</display_position>';
            $this->menus .= '<display_overlay>' . $menu->getDisplayOverlay() . '</display_overlay>';
            $this->menus .= '<main_menu_hover>'. $menu->getMainMenuHover() .'</main_menu_hover>';
            $this->menus .= '<sub_menu_hover>'. $menu->getSubMenuHover() .'</sub_menu_hover>';
            $this->menus .= '<vertical_menu_title>' .
            $menu->getVerticalMenuTitle() . '</vertical_menu_title>';
            $this->menus .= '<show_category_icon_with_menu>' .
            $menu->getShowCategoryIconWithMenu() . '</show_category_icon_with_menu>';
            $this->menus .= '<show_category_count>' .
            $menu->getShowCategoryCount() . '</show_category_count>';
            $this->menus .= '<show_view_more>' . $menu->getShowViewMore() . '</show_view_more>';
            $this->menus .= '<no_of_sub_category_to_show>' .
            $menu->getNoOfSubCategoryToShow() . '</no_of_sub_category_to_show>';
            $this->menus .= '<store_code>' . $menu->getStoreCode() . '</store_code>';
            $this->menus .= '</item>';
            $this->createMenuItems($menu);
        }
    }

    /**
     * Create Menu Item
     *
     * @param \Magedelight\Megamenu\Model\Menu $menu
     * @return void
     */
    public function createMenuItems($menu)
    {
        $menuItems = $this->menuItemsFactory->create()->getCollection()
            ->addFieldToFilter('menu_id', $menu->getMenuId())
            ->addFieldToFilter('item_parent_id', 0)
            ->setOrder('sort_order', 'ASC');
        foreach ($menuItems as $singlrMenuItem) {

            if (!empty($singlrMenuItem->getData())) {
                $this->menuItems .= '<item>';
                $this->menuItems .= '<item_name>' . htmlentities((string)$singlrMenuItem->getItemName(), ENT_QUOTES, 'UTF-8') . '</item_name>';
                $this->menuItems .= '<item_type>' . htmlentities((string)$singlrMenuItem->getItemType(), ENT_QUOTES, 'UTF-8') . '</item_type>';
                $this->menuItems .= '<sort_order>' . $singlrMenuItem->getSortOrder() . '</sort_order>';
                $this->menuItems .= '<item_parent_id>' .
                    $singlrMenuItem->getItemParentId() . '</item_parent_id>';
                $this->menuItems .= '<menu_id>' . $singlrMenuItem->getMenuId() . '</menu_id>';
                $this->menuItems .= '<object_id>' . $singlrMenuItem->getObjectId() . '</object_id>';
                $this->menuItems .= '<item_link>' . htmlentities((string)$singlrMenuItem->getItemLink(), ENT_QUOTES, 'UTF-8') . '</item_link>';
                $this->menuItems .= '<item_columns>' . $singlrMenuItem->getItemColumns() . '</item_columns>';
                $this->menuItems .= '<item_font_icon><![CDATA[' .
                    $singlrMenuItem->getItemFontIcon() . ']]></item_font_icon>';
                $this->menuItems .= '<item_class>' . htmlentities((string)$singlrMenuItem->getItemClass(), ENT_QUOTES, 'UTF-8') . '</item_class>';
                $this->menuItems .= '<animation_option>' .
                    $singlrMenuItem->getAnimationOption() . '</animation_option>';
                $this->menuItems .= '<category_display>' .
                    $singlrMenuItem->getCategoryDisplay() . '</category_display>';
                $this->menuItems .= '<category_columns>' .
                    $singlrMenuItem->getCategoryColumns() . '</category_columns>';
                $this->menuItems .= '<category_vertical_menu>' .
                    $singlrMenuItem->getCategoryVerticalMenu() . '</category_vertical_menu>';
                $this->menuItems .= '<category_vertical_menu_bg>' .
                    $singlrMenuItem->getCategoryVerticalMenuBg() . '</category_vertical_menu_bg>';
                $this->menuItems .= '<vertical_cat_exclude>' .
                    $singlrMenuItem->getVerticalCatExclude() . '</vertical_cat_exclude>';
                $this->menuItems .= '<vertical_cat_sortby>' .
                    $singlrMenuItem->getVerticalCatSortby() . '</vertical_cat_sortby>';
                $this->menuItems .= '<vertical_cat_sortorder>' .
                    $singlrMenuItem->getVerticalCatSortorder() . '</vertical_cat_sortorder>';
                $this->menuItems .= '<vertical_cat_level>' .
                    $singlrMenuItem->getVerticalCatLevel() . '</vertical_cat_level>';
                $this->menuItems .= '<product_display>' .
                    $singlrMenuItem->getProductDisplay() . '</product_display>';
                $this->menuItems .= '<open_in_new_tab>' .
                    $singlrMenuItem->getOpenInNewTab() . '</open_in_new_tab>';
                $this->menuItems .= '<menu_icon>' . $singlrMenuItem->getMenuIcon() . '</menu_icon>';
                $this->menuItems .= '</item>';
                if (!empty($singlrMenuItem->getItemColumns())) {
                    $this->generateStaticBlock($singlrMenuItem);

                }
            }
        }
    }

    /**
     * Generate Static Block
     *
     * @param \Magedelight\Megamenu\Model\MenuItems $singlrMenuItem
     * @return void
     */
    private function generateStaticBlock($singlrMenuItem)
    {
        $columns = json_decode($singlrMenuItem->getItemColumns());
        $totalColumn = count($columns);
        for ($i = 0; $i < $totalColumn; $i++) {
            if (isset($columns[$i]->item_rows)) {
                $rowItems = $columns[$i]->item_rows;
                $rowItemsCount = count($rowItems);
                ;
                for ($j = 0; $j < $rowItemsCount; $j++) {
                    $type = $rowItems[$j]->type;
                    if ($type == 'block') {
                        $subBlockId = $rowItems[$j]->value;
                        $this->createStaticBlocks($subBlockId);
                    }
                }
            } else {
                $type = $columns[$i]->type;
                if ($type == 'block') {
                    $subBlockId = $columns[$i]->value;
                    $this->createStaticBlocks($subBlockId);
                }
            }
        }
    }

    /**
     * Create Static Blocks
     *
     * @param string $identifier
     * @return void
     */
    public function createStaticBlocks($identifier)
    {
        if (!in_array($identifier, $this->staticBlocksStore)) {
            $block = $this->blockFactory->create()->load($identifier);
            $this->staticBlocks .= '<item>';
            $this->staticBlocks .= '<title>' . $block->getTitle() . '</title>';
            $this->staticBlocks .= '<identifier>' . $block->getIdentifier() . '</identifier>';
            $this->staticBlocks .= '<content><![CDATA[' . $block->getContent() . ']]></content>';
            $this->staticBlocks .= '<is_active>' . $block->getIsActive() . '</is_active>';
            $this->staticBlocks .= '</item>';
            $this->staticBlocksStore[] = $identifier;
        }
    }
}
