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

use Ebizcharge\Ebizcharge\Api\Data\SyncAssetsInterface;
use Ebizcharge\Ebizcharge\Block\Adminhtml\SyncAssets\Tabs;
use Ebizcharge\Ebizcharge\Helper\Data as DataHelper;
use Ebizcharge\Ebizcharge\Model\Config as ConfigResource;
use Ebizcharge\Ebizcharge\Model\SyncAssets;
use Ebizcharge\Ebizcharge\Model\SyncAssetsFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\Group;

/**
 * Block to upload items to ebizcharge gateway
 *
 * Class Items
 */
class Items extends Template
{
    /**
     * Path to template file
     *
     * @var string
     */
    protected $_template = 'Ebizcharge_Ebizcharge::syncAssets/upload/tabs/upload_items.phtml';

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
    public const TAB_TITLE = 'Upload Products to EBizCharge Gateway';

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
     * @param DataHelper $dataHelper
     * @param ConfigResource $configResource
     * @param SyncAssetsFactory $syncAssetsFactory
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        DataHelper $dataHelper,
        ConfigResource $configResource,
        SyncAssetsFactory $syncAssetsFactory,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->configResource = $configResource;
        $this->syncAssetsFactory = $syncAssetsFactory;
    }

    /**
     * Get Last Uploaded
     *
     * @return string
     */
    public function getLastUploaded()
    {
        $syncProductData = $this->getUploadProductSyncUpdate();
        return $syncProductData[SyncAssets::END_TIME] ?? self::LAST_ASSETS_SYNCED;
    }

    /**
     * Get Upload Product Sync Update
     *
     * @return array|mixed|null
     */
    public function getUploadProductSyncUpdate()
    {
        return $this->syncAssetsFactory->create()
            ->loadByProcessCode(SyncAssets::SYNC_ASSETS_CRON_CODE_UPLOAD_ITEMS)
            ->getData();
    }

    /**
     * Get Last Uploaded Products
     *
     * @return int|mixed
     */
    public function getLastUploadedProducts()
    {
        $syncProductData = $this->getUploadProductSyncUpdate();
        $lastDownloaded = $syncProductData['total_downloaded_records'] ?? 0;

        return $lastDownloaded ?: 0;
    }

    /**
     * Get Products At Ebizcharge
     *
     * @return int|mixed
     */
    public function getProductsAtEbizcharge()
    {
        $syncProductData = $this->getUploadProductSyncUpdate();
        $remoteProducts = $syncProductData[SyncAssetsInterface::REMOTE_TOTAL_RECORDS] ?? 0;
        return $remoteProducts ??  0;
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
     * Get Products Check At Ebizcharge Url
     *
     * Get Product Check URL
     *
     * @return string
     */
    public function getProductsCheckAtEbizchargeUrl()
    {
        return $this->dataHelper->getUrl(Tabs::CHECK_ASSETS_AT_EBIZCHARGE_URL);
    }

    /**
     * Get Product Upload URL
     *
     * @return string
     */
    public function getProductsUploadsFromEbizchargeUrl()
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
     *  Get Total Remote Uploaded
     *
     * @return int|mixed
     */
    public function getTotalRemoteUplaoded()
    {
        $syncProductsData = $this->getUploadProductSyncUpdate();
        $remoteProducts = $syncProductsData[SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS] ?? 0;
        return $remoteProducts ?: 0;
    }

    /**
     * Get View Uploaded Products URL
     *
     * @return string
     */
    public function getViewUploadProductsUrl()
    {
        return $this->dataHelper->getUrl(Tabs::PRODUCTS_VIEW_URL);
    }

    /**
     * Is Product Sync Selected
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isProductSyncActive()
    {
        /** @var $storeId */
        $storeId = $this->dataHelper->getStore()->getId();

        /** @var $isEbizchargeActive */
        $isEbizchargeActive = $this->configResource->isEbizchargeActive($storeId);
        /** @var $isEconnectUploadActive */
        $isEconnectUploadActive = $this->configResource->isEconnectUploadEnabled($storeId);
        /** @var $isDownloadOrdersActive */
        $isDownloadOrdersActive = $this->configResource->isUploadItemsEnabled($storeId);

        return $isEbizchargeActive && $isEconnectUploadActive && $isDownloadOrdersActive;
    }

    /**
     * Is Econnect Active
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isEconnectActive()
    {
        /** @var $storeId */
        $storeId = $this->dataHelper->getStore()->getId();

        return $this->configResource->isEconnectDownlaodEnabled($storeId);
    }

    /**
     * Check downloading status , in progress, completed etc
     *
     * @return int
     */
    public function syncStatus()
    {
        $uploadedOrders = $this->getUploadProductSyncUpdate();
        return isset($uploadedOrders['status']) ? $uploadedOrders['status'] : '';
    }

    /**
     * Title for the tab content
     *
     * @return Phrase
     */
    public function getTitle()
    {
        // phpcs:ignore
        return __(self::TAB_TITLE);
    }
}
