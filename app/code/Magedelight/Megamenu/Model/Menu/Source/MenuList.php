<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Model\Menu\Source;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magedelight\Megamenu\Model\Menu;

class MenuList implements OptionSourceInterface
{
    /**
     * Exclude Menu
     *
     * @var array
     */
    private $excludeArray = [
        'all-category',
        'amazon-menu',
        'drilldown'
    ];

    /**
     * @var Menu
     */
    protected $megamenuMenu;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param Menu $megamenuMenu
     * @param Http $request
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Menu $megamenuMenu,
        Http $request,
        StoreManagerInterface $storeManager
    ) {
        $this->megamenuMenu = $megamenuMenu;
        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * Get Stores
     *
     * @return array
     */
    public function getStores()
    {
        $store = $this->request->getParam('store');
        $website = $this->request->getParam('website');

        $allStores = [];
        if (isset($store) && ! empty($store)) {
            $allStores[] = $store;
        } elseif (isset($website) && ! empty($website)) {
            $website = $this->storeManager->getWebsite($website);
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $allStores[] = $store->getStoreId();
                }
            }
        } else {
            $allStores[] = 0;
        }
        $allStores[] = 0;

        return $allStores;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $storeId = $this->getStores();
        $menus = $this->megamenuMenu->getCollection();
        $menus->addFieldToFilter('is_active', ['eq' => 1]);
        if ($this->request->getParam('website')) {
            $menus->getSelect()->join(
                ['u' => $menus->getTable('megamenu_menus_store')],
                'u.menu_id = main_table.menu_id',
                ['c' =>new \Zend_Db_Expr("group_concat(u.store_id)")]
            );
            $qry = '';
            foreach ($storeId as $_storeId) {
                if ($_storeId != 0) {
                    $temp[] =  "find_in_set(".$_storeId.", c)";
                    $qry = implode(" AND ", $temp);
                }

                if ($_storeId == 0) {
                    $qry .= " OR find_in_set(".$_storeId.", c)";
                }
            }
            $menus->getSelect()->group('main_table.menu_id')->having(new \Zend_Db_Expr($qry));

        } else {
            $menus->getSelect()->join(
                ['u' => $menus->getTable('megamenu_menus_store')],
                'u.menu_id = main_table.menu_id',
                []
            );
            $menus->addFieldToFilter(
                'u.store_id',
                [
                        ['in' => $storeId],
                    ]
            );
            $menus->getSelect()->group('main_table.menu_id');
        }

        $sourceArray = [];
        $count = 0;
        if (isset($menus) && ! empty($menus)) {
            foreach ($menus as $menu) {
                if (!in_array($menu->getMenuDesignType(), $this->excludeArray)) {
                    $sourceArray[$count]['value'] = $menu->getMenuId();
                    $sourceArray[$count]['label'] = $menu->getMenuName();
                    $count++;
                }
            }
        }
        array_unshift($sourceArray, ['value' => 0, 'label' => __('None')]);
        return $sourceArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            0 => __('No'),
            1 => __('Yes')
        ];
    }
}
