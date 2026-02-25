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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\SyncAssets\Upload\Tab;

use Ebizcharge\Ebizcharge\Block\Adminhtml\SyncAssets\Tabs;
use Ebizcharge\Ebizcharge\Helper\Data as DataHelper;
use Ebizcharge\Ebizcharge\Model\SyncAssets;
use Ebizcharge\Ebizcharge\Model\SyncAssetsFactory;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;

/**
 * Block to upload orders to ebizcharge gateway
 *
 * Class Orders
 */
class Orders extends Template
{
    /**
     * Path to template file
     *
     * @var string
     */
    protected $_template = 'Ebizcharge_Ebizcharge::syncAssets/upload/tabs/upload_orders.phtml';

    /**
     * Last time uploaded
     *
     * @const LAST_ASSETS_SYNCED
     */
    public const LAST_ASSETS_SYNCED = 'Never';

    /**
     * Title for the content
     *
     * @const TITLE
     */
    public const TAB_TITLE = 'Upload Orders to EBizCharge Gateway';

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var SyncAssetsFactory
     */
    private $syncAssetsFactory;

    /**
     * @param DataHelper $dataHelper
     * @param SyncAssetsFactory $syncAssetsFactory
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        DataHelper $dataHelper,
        SyncAssetsFactory $syncAssetsFactory,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->syncAssetsFactory = $syncAssetsFactory;
    }

    /**
     * Check downloading status , in progress, completed etc
     *
     * @return int
     */
    public function syncStatus()
    {
        $uploadOrders = $this->getUploadOrdersSyncUpdate();
        return isset($uploadOrders['status']) ? $uploadOrders : '';
    }

    /**
     * Get Total Remote Uploaded
     *
     * @return int|mixed
     */
    public function getTotalRemoteUplaoded()
    {
        $syncOrdersData = $this->getUploadOrdersSyncUpdate();
        $syncOrdersData = $syncOrdersData[SyncAssets::TOTAL_DOWNLOAD_RECORDS] ?? 0;
        return $syncOrdersData ?: 0;
    }

    /**
     * Get Orders At Ebizcharge
     *
     * @return int|mixed
     */
    public function getOrdersAtEbizcharge()
    {
        $syncOrdersData = $this->getUploadOrdersSyncUpdate();
        $remoteOrders = $syncOrdersData[SyncAssets::REMOTE_TOTAL_RECORDS] ?? 0;
        return $remoteOrders ??  0;
    }

    /**
     * Get Upload Customer Sync Update
     *
     * @return array|mixed|null
     */
    public function getUploadOrdersSyncUpdate()
    {
        return $this->syncAssetsFactory->create()
            ->loadByProcessCode(SyncAssets::SYNC_ASSETS_CRON_CODE_UPLOAD_ORDERS)
            ->getData();
    }

    /**
     * Get Last uploaded Orders Date
     *
     * @return mixed|string
     */
    public function getlastUploadedOrdersDate()
    {
        $uploadOrdersSyncAssets = $this->getUploadOrdersSyncUpdate();
        return $uploadOrdersSyncAssets['end_time'] ?? self::LAST_ASSETS_SYNCED;
    }

    /**
     * Get Last uploaded Orders Date
     *
     * @return mixed|string
     */
    public function getlastUploadedOrdersStatus()
    {
        $uploadOrdersSyncAssets = $this->getUploadOrdersSyncUpdate();
        return $uploadOrdersSyncAssets['status'] ?? '';
    }

    /**
     * Get Last Upload Orders
     *
     * @return int|mixed
     */
    public function getLastUploadOrders()
    {
        $syncOrdersData = $this->getUploadOrdersSyncUpdate();
        $remoteOrders = $syncOrdersData['remote_total_records'] ?? 0;
        return $remoteOrders ?: 0;
    }

    /**
     * Get View Upload Products URL
     *
     * @return string
     */
    public function getViewUploadOrdersUrl()
    {
        return $this->dataHelper->getUrl(Tabs::ORDERS_VIEW_URL);
    }

    /**
     * Get Progress Bar URL
     *
     * @return string
     */
    public function getProgressBarUrl()
    {
        return $this->dataHelper->getUrl(Tabs::ASSETS_PROGRESS_BAR_URL);
    }

    /**
     * Get Orders Upload URL
     *
     * @return string
     */
    public function getOrdersUploadsFromEbizchargeUrl()
    {
        return $this->dataHelper->getUrl(Tabs::UPLOAD_ASSETS_TO_EBIZCHARGE_URL);
    }

    /**
     * Get Orders Check URL
     *
     * @return string
     */
    public function getOrdersCheckAtEbizchargeUrl()
    {
        return $this->dataHelper->getUrl(Tabs::CHECK_ASSETS_AT_EBIZCHARGE_URL);
    }

    /**
     * Title for the tab content
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        // phpcs:ignore
        return __(self::TAB_TITLE);
    }
}
