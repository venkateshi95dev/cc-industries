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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\SyncAssets\Download\Tab\View;

use Ebizcharge\Ebizcharge\Block\Adminhtml\SyncAssets\Tabs;
use Ebizcharge\Ebizcharge\Helper\Data as DataHelper;
use Ebizcharge\Ebizcharge\Model\Config as ConfigResource;
use Ebizcharge\Ebizcharge\Model\SyncAssets;
use Ebizcharge\Ebizcharge\Model\SyncAssetsFactory;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Text\ListText;
use Magento\Store\Model\Group;

/**
 * SyncAssets Download Customers Tab
 *
 * Class Products
 */
class Customers extends ListText implements TabInterface
{
    /**
     * @var AuthorizationInterface
     */
    private mixed $authorization;

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
     * @param Context $context
     * @param DataHelper $dataHelper
     * @param ConfigResource $configResource
     * @param SyncAssetsFactory $syncAssetsFactory
     * @param array $data
     * @param AuthorizationInterface|null $authorization
     */
    public function __construct(
        Context                 $context,
        DataHelper              $dataHelper,
        ConfigResource          $configResource,
        SyncAssetsFactory       $syncAssetsFactory,
        array                   $data = [],
        ?AuthorizationInterface $authorization = null
    ) {
        $this->authorization = $authorization ?? ObjectManager::getInstance()->get(AuthorizationInterface::class);
        parent::__construct($context, $data);
        /** @var _dataHelper */
        $this->_dataHelper = $dataHelper;
        /** @var _configResource */
        $this->_configResource = $configResource;
        /** @var _syncAssetsFactory */
        $this->_syncAssetsFactory = $syncAssetsFactory;
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Download Customers');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Download Customers');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return $this->authorization->isAllowed('Ebizcharge_Ebizcharge::Download_Orders');
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
     * Get Store Collection
     *
     * @param Group $group
     * @return array
     */
    public function getStoreCollection(Group $group)
    {
        return $group->getStores();
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
