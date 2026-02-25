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
use Ebizcharge\Ebizcharge\Model\Config as ConfigResource;
use Ebizcharge\Ebizcharge\Model\SyncAssets;
use Ebizcharge\Ebizcharge\Model\SyncAssetsFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;

/**
 * Sync Upload Customers Assets
 *
 * Class Customers
 */
class Customers extends Template
{
    /**
     * Path to template file
     *
     * @var string
     */
    protected $_template = 'Ebizcharge_Ebizcharge::syncAssets/upload/tabs/upload_customers.phtml';

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
    public const TAB_TITLE = 'Upload Customers to EBizCharge Gateway';

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var ConfigResource
     */
    private $configResource;

    /**
     * @var SyncAssetsFactory
     */
    private $syncAssetsFactory;

    /**
     * @param Template\Context $context
     * @param DataHelper $dataHelper
     * @param ConfigResource $configResource
     * @param SyncAssetsFactory $syncAssetsFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        DataHelper $dataHelper,
        ConfigResource $configResource,
        SyncAssetsFactory $syncAssetsFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        /** @var dataHelper */
        $this->dataHelper = $dataHelper;
        /** @var configResource */
        $this->configResource = $configResource;
        /** @var syncAssetsFactory */
        $this->syncAssetsFactory = $syncAssetsFactory;
    }

    /**
     * Get Last Uploaded
     *
     * @return string
     */
    public function getLastUploaded()
    {
        $syncCustomerData = $this->getUploadCustomerSyncUpdate();
        return $syncCustomerData['end_time'] ?? self::LAST_ASSETS_SYNCED;
    }

    /**
     * Get Upload Customer Sync Update
     *
     * @return array|mixed|null
     */
    public function getUploadCustomerSyncUpdate()
    {
        return $this->syncAssetsFactory->create()
            ->loadByProcessCode(SyncAssets::SYNC_ASSETS_CRON_CODE_UPLOAD_CUSTOMERS)
            ->getData();
    }

    /**
     * Get Last Downloaded Customers
     *
     * @return int|mixed
     */
    public function getLastUploadedCustomers()
    {
        $syncCustomerData = $this->getUploadCustomerSyncUpdate();
        $lastUploaded = $syncCustomerData['total_downloaded_records'] ?? 0;
        return $lastUploaded ?: 0;
    }

    /**
     * Check downloading status , in progress, completed etc
     *
     * @return int
     */
    public function syncStatus()
    {
        $uploadCustomers = $this->getUploadCustomerSyncUpdate();
        return $uploadCustomers['status'] ?? '';
    }

    /**
     * Get Total Remote Uploaded
     *
     * @return int|mixed
     */
    public function getTotalRemoteUplaoded()
    {
        $syncCustomerData = $this->getUploadCustomerSyncUpdate();
        $remoteCustomers = $syncCustomerData[SyncAssets::TOTAL_DOWNLOAD_RECORDS] ?? 0;
        return $remoteCustomers ?: 0;
    }

    /**
     * Get Customers At Ebizcharge
     *
     * @return int|mixed
     */
    public function getCustomersAtEbizcharge()
    {
        $syncCustomerData = $this->getUploadCustomerSyncUpdate();
        $remoteCustomers = $syncCustomerData[SyncAssets::REMOTE_TOTAL_RECORDS] ?? 0;
        return $remoteCustomers ?: 0;
    }

    /**
     * Get Customer Check URL
     *
     * @return string
     */
    public function getCustomersCheckAtEbizchargeUrl()
    {
        return $this->dataHelper->getUrl(Tabs::CHECK_ASSETS_AT_EBIZCHARGE_URL);
    }

    /**
     * Get Customer Upload URL
     *
     * @return string
     */
    public function getCustomersUploadsFromEbizchargeUrl()
    {
        return $this->dataHelper->getUrl(Tabs::UPLOAD_ASSETS_TO_EBIZCHARGE_URL);
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
     * Get View Uploaded Customers URL
     *
     * @return string
     */
    public function getViewUploadedCustomersUrl()
    {
        return $this->dataHelper->getUrl(Tabs::CUSTOMERS_VIEW_URL);
    }

    /**
     * Is Customer Sync Selected
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isCustomerSyncActive()
    {
        $storeId = $this->dataHelper->getStore()->getId();

        $isEbizchargeActive = $this->configResource->isEbizchargeActive($storeId);
        $isEconnectActive = $this->configResource->isEconnectUploadEnabled($storeId);
        $isUploadCustomersActive = $this->configResource->isUploadCustomersEnabled($storeId);

        return $isEbizchargeActive && $isEconnectActive && $isUploadCustomersActive;
    }

    /**
     * Is Econnect Active
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isEconnectActive()
    {
        $storeId = $this->dataHelper->getStore()->getId();
        return $this->configResource->isEconnectDownlaodEnabled($storeId);
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
