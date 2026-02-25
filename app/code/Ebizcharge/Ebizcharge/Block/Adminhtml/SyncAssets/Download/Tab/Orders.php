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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\SyncAssets\Download\Tab;

use Ebizcharge\Ebizcharge\Block\Adminhtml\SyncAssets\Tabs;
use Ebizcharge\Ebizcharge\Helper\Data as DataHelper;
use Ebizcharge\Ebizcharge\Model\SyncAssets;
use Ebizcharge\Ebizcharge\Model\SyncAssetsFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Phrase;

/**
 * Sync Download Orders Assets
 *
 * Class Orders
 */
class Orders extends Widget implements TabInterface
{
    /**
     * Path to template file
     *
     * @var string
     */
    protected $_template = 'Ebizcharge_Ebizcharge::syncAssets/download/tabs/download_orders.phtml';

    /**
     * @var array
     */
    protected $_data;

    /**
     * @var SyncAssetsFactory
     */
    protected SyncAssetsFactory $syncAssetsFactory;

    /**
     * @var DataHelper
     */
    protected DataHelper $dataHelper;

    /**
     * Orders constructor.
     *
     * @param DataHelper $dataHelper
     * @param Context $context
     * @param SyncAssetsFactory $syncAssetsFactory
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     * @param array $data
     */
    public function __construct(
        DataHelper $dataHelper,
        Context $context,
        SyncAssetsFactory $syncAssetsFactory,
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null,
        array $data = []
    ) {
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);

        /** @var _data */
        $this->_data = $data;
        /** @var  syncAssetsFactory */
        $this->syncAssetsFactory = $syncAssetsFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Tab settings
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Download Orders From EBizCharge Gateway');
    }

    /**
     * Get Tab Title
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Download Orders From EBizCharge Gateway');
    }

    /**
     * Can Show Tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Is Hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check downloading status , in progress, completed etc
     *
     * @return int
     */
    public function syncStatus()
    {
        $downloadOrders = $this->getDownloadOrdersSyncUpdate();
        return $downloadOrders['status'] ?? '';
    }

    /**
     * Get Download Customer Sync Update
     *
     * @return array|mixed|null
     */
    public function getDownloadOrdersSyncUpdate()
    {
        return $this->syncAssetsFactory->create()
            ->loadByProcessCode(SyncAssets::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS)
            ->getData();
    }

    /**
     * Get View Downloaded Products URL
     *
     * @return string
     */
    public function getViewDownloadOrdersUrl()
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
     * Get Orders Download URL
     *
     * @return string
     */
    public function getOrdersDownloadsFromEbizchargeUrl()
    {
        return $this->dataHelper->getUrl(Tabs::SYNC_ASSETS_FROM_EBIZCHARGE_URL);
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
     * Get Last Downloaded Orders
     *
     * @return int|mixed
     */
    public function getLastDownloadedOrders()
    {
        $syncOrderData = $this->getDownloadOrdersSyncUpdate();
        $remoteOrders = $syncOrderData['remote_total_records'] ?? 0;
        return $remoteOrders ?: 0;
    }

    /**
     * Get Last Downloaded Orders
     *
     * @return int|mixed
     */
    public function getFailedOrdersCount()
    {
        $syncOrderData = $this->getDownloadOrdersSyncUpdate();
        return $syncOrderData['failed_counter'] ?? 0;
    }

    /**
     * Get Last downloaded date
     *
     * @return string
     */
    public function getLastDownloadedDate()
    {
        $syncProductData = $this->getDownloadOrdersSyncUpdate();
        return $syncProductData['end_time'] ?? 'Never';
    }
}
