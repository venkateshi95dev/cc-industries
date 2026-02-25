<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\SyncAssets;

use Exception;
use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

/**
 * Sync Assets Tabs class
 *
 * Class Tabs
 */
class Tabs extends WidgetTabs
{
    /**
     * Check assets At Ebizcharge URL
     *
     * @const CHECK_ASSETS_AT_EBIZCHARGE_URL
     */
    public const CHECK_ASSETS_AT_EBIZCHARGE_URL = 'ebizcharge_ebizcharge/*/checklatestresources';

    /**
     * Download/upload Assets URL from Ebizcharge Gateway
     *
     * @const SYNC_ASSETS_FROM_EBIZCHARGE_URL
     */
    public const SYNC_ASSETS_FROM_EBIZCHARGE_URL = 'ebizcharge_ebizcharge/*/downloadlatestresources';

    /**
     * Upload Assets URL to Ebizcharge Gateway
     *
     * @const UPLOAD_ASSETS_TO_EBIZCHARGE_URL
     */
    public const UPLOAD_ASSETS_TO_EBIZCHARGE_URL = 'ebizcharge_ebizcharge/*/uploadlatestresources';

    /**
     * Download/upload Assets Progress Bar
     *
     * @const SYNC_ASSETS_PROGRESS_BAR_URL
     */
    public const ASSETS_PROGRESS_BAR_URL = 'ebizcharge_ebizcharge/*/progressbar';

    /**
     * Download Assets View Products URL
     *
     * @const: UPLOAD_ASSETS_VIEW_PRODUCTS_URL
     */
    public const UPLOAD_ASSETS_VIEW_PRODUCTS_URL = 'ebizcharge_ebizcharge/*/items';

    /**
     * Logs view url for CSV file
     * @const LOGS_VIEW_URL
     */
    public const LOGS_VIEW_URL = 'ebizcharge_ebizcharge/synclogs/view';

    /**
     * View Customers Url
     *
     * @const CUSTOMERS_VIEW_URL
     */
    public const CUSTOMERS_VIEW_URL = 'customer/index/index';

    /**
     * Items view url
     *
     * @const PRODUCTS_VIEW_URL
     */
    public const PRODUCTS_VIEW_URL = 'catalog/product/';

    /**
     * Orders view url
     *
     * @const ORDERS_VIEW_URL
     */
    public const ORDERS_VIEW_URL = 'sales/order/';

    /**
     * Main Construct of the layout class
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('upload_resources');
        $this->setDestElementId('sync-resources-content');
        $this->setTitle(__($this->getSyncAction() . ' '.$this->getSyncFromTo().' EBizCharge Hub'));
    }

    /**
     * Get sync action Download|Upload
     *
     * @return string
     */
    public function getSyncAction(): string
    {
        return $this->getData('sync_action');
    }

    /**
     * Get sync action From To
     *
     * @return string
     */
    public function getSyncFromTo(): string
    {
        return $this->getData('sync_from_to');
    }

    /**
     * Get sync action Download|Upload
     *
     * @return string
     */
    private function getFromToLabel(): string
    {
        return $this->getData('sync_from_to');
    }

    /**
     * Prepare layout function for tabs left
     *
     * @return Tabs
     * @throws Exception
     */
    protected function _prepareLayout()
    {
        /** @var $actionType */
        $actionType = $this->getSyncAction();

        /**
         * download customers
         */
        $this->addTab(
            'tab_' . strtolower($actionType) . '_customers',
            [
                'label' => __($actionType . ' Customers'),
                'content' => $this->getLayout()->createBlock(__NAMESPACE__ . "\\$actionType\\Tab\\Customers")->toHtml(),
                'active' => true
            ]
        );

        /**
         * download Products | Items
         */
        $this->addTab(
            'tab_' . strtolower($actionType) . '_items',
            [
                'label' => __(ucwords($actionType) . ' Products'),
                'content' => $this->getLayout()->createBlock(__NAMESPACE__ . "\\$actionType\\Tab\\Items")->toHtml(),
                'class' => 'ajax'
            ]
        );

        /**
         * download customers
         */
        $this->addTab(
            'tab_' . strtolower($actionType) . '_orders',
            [
                'label' => __($actionType . ' Orders'),
                'content' => $this->getLayout()->createBlock(__NAMESPACE__ . "\\$actionType\\Tab\\Orders")->toHtml(),
                'class' => 'ajax'
            ]
        );

        return parent::_prepareLayout();
    }
}
