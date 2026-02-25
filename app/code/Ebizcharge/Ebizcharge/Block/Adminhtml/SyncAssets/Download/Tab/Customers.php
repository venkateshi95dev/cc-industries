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
use Ebizcharge\Ebizcharge\Model\Config as ConfigResource;
use Ebizcharge\Ebizcharge\Model\SyncAssets;
use Ebizcharge\Ebizcharge\Model\SyncAssetsFactory;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;


/**
 * Sync Download Customers Assets
 *
 * Class Customers
 */
class Customers extends Widget implements TabInterface
{
    /**
     * Path to template file
     *
     * @var string
     */
    protected $_template = 'Ebizcharge_Ebizcharge::syncAssets/download/tabs/download_customers.phtml';

    /**
     * @var DataHelper
     */
    protected DataHelper $_dataHelper;

    /**
     * @var ConfigResource
     */
    protected ConfigResource $_configResource;

    /**
     * @var SyncAssetsFactory
     */
    protected SyncAssetsFactory $_syncAssetsFactory;

    /**
     * Customers constructor.
     *
     * @param TemplateContext $context
     * @param DataHelper $dataHelper
     * @param ConfigResource $configResource
     * @param SyncAssetsFactory $syncAssetsFactory
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        DataHelper $dataHelper,
        ConfigResource $configResource,
        SyncAssetsFactory $syncAssetsFactory,
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null,
        array $data = []
    ) {
        /** parent Constructor */
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);

        /** @var _dataHelper */
        $this->_dataHelper = $dataHelper;
        /** @var _configResource */
        $this->_configResource = $configResource;
        /** @var _syncAssetsFactory */
        $this->_syncAssetsFactory = $syncAssetsFactory;
    }

    /**
     * Get Last Downloaded Date
     *
     * @return string
     */
    public function getLastDownloadedDate()
    {
        $syncCustomerData = $this->getDownloadCustomerSyncUpdate();
        return $syncCustomerData['end_time'] ?? 'Never';
    }

    /**
     * Get Download Customer Sync Update
     *
     * @return array|mixed|null
     */
    public function getDownloadCustomerSyncUpdate()
    {
        return $this->_syncAssetsFactory->create()
            ->loadByProcessCode(SyncAssets::SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS)
            ->getData();
    }

    /**
     * Get Last Downloaded Customers
     *
     * @return int|mixed
     */
    public function getLastDownloadedCustomers()
    {
        $syncCustomerData = $this->getDownloadCustomerSyncUpdate();
        $lastDownloaded = $syncCustomerData['total_downloaded_records'] ?? 0;
        return $lastDownloaded ?: 0;
    }

    /**
     * Get Last Downloaded Customers
     *
     * @return int|mixed
     */
    public function getFailedCustomersCount()
    {
        $syncCustomerData = $this->getDownloadCustomerSyncUpdate();
        return $syncCustomerData['failed_counter'] ?? 0;
    }

    /**
     * Check downloading status , in progress, completed etc
     *
     * @return int
     */
    public function syncStatus()
    {
        $downloadCustomers = $this->getDownloadCustomerSyncUpdate();
        return $downloadCustomers['status'] ?? '';
    }

    /**
     * Get Customers At Ebizcharge
     *
     * @return int|mixed
     */
    public function getCustomersAtEbizcharge()
    {
        $syncCustomerData = $this->getDownloadCustomerSyncUpdate();
        $remoteCustomers = $syncCustomerData['remote_total_records'] ?? 0;
        return $remoteCustomers ?: 0;
    }

    /**
     * @param $group
     * @return mixed
     */
    public function getStoreCollection($group =null)
    {
        return $group->getStores();
    }

    /**
     * Tab settings
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Download Customers From EBizCharge Gateway');
    }

    /**
     * Get Tab Title
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Download Customers From EBizCharge Gateway');
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
     * Get Customer Check URL
     *
     * @return string
     */
    public function getCustomersCheckAtEbizchargeUrl()
    {
        return $this->_dataHelper->getUrl(Tabs::CHECK_ASSETS_AT_EBIZCHARGE_URL);
    }

    /**
     * Get Customer Downloads From Ebizcharge Url
     *
     * @return string
     */
    public function getCustomersDownloadsFromEbizchargeUrl()
    {
        return $this->_dataHelper->getUrl(Tabs::SYNC_ASSETS_FROM_EBIZCHARGE_URL);
    }

    /**
     * Get Progress Bar URL
     *
     * @return string
     */
    public function getProgressBarUrl()
    {
        return $this->_dataHelper->getUrl(Tabs::ASSETS_PROGRESS_BAR_URL);
    }

    /**
     * Get View Downloaded Customers URL
     *
     * @return string
     */
    public function getViewDownloadCustomersUrl()
    {
        return $this->_dataHelper->getUrl(Tabs::CUSTOMERS_VIEW_URL);
    }

    /**
     * Is Customer Sync Selected
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isCustomerSyncActive()
    {
        $storeId = $this->_dataHelper->getStore()->getId();

        $isEbizchargeActive = $this->_configResource->isEbizchargeActive($storeId);
        $isEconnectActive = $this->_configResource->isEconnectDownlaodEnabled($storeId);
        $isDownloadCustomersActive = $this->_configResource->isDownlaodCustomersEnabled($storeId);

        return $isEbizchargeActive && $isEconnectActive && $isDownloadCustomersActive;
    }

    /**
     * Is Econnect Active
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isEconnectActive()
    {
        $storeId = $this->_dataHelper->getStore()->getId();
        return $this->_configResource->isEconnectDownlaodEnabled($storeId);
    }

    /**
     * Get logs view url for CSV file
     *
     * @return string
     */
    public function getLogsViewUrl()
    {
        return $this->getUrl(Tabs::LOGS_VIEW_URL);
    }
}
