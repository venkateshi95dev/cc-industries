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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Phrase;

/**
 * Sync Download Items Assets
 *
 * Class Items
 */
class Items extends Widget implements TabInterface
{
    /**
     * Path to template file
     *
     * @var string
     */
    protected $_template = 'Ebizcharge_Ebizcharge::syncAssets/download/tabs/download_items.phtml';

    /**
     * @var DataHelper
     */
    private DataHelper $dataHelper;

    /**
     * @var ConfigResource
     */
    private ConfigResource $configResource;

    /**
     * @var SyncAssetsFactory
     */
    private SyncAssetsFactory $syncAssetsFactory;

    /**
     * Items constructor.
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

        /** @var dataHelper */
        $this->dataHelper = $dataHelper;
        /** @var configResource */
        $this->configResource = $configResource;
        /** @var syncAssetsFactory */
        $this->syncAssetsFactory = $syncAssetsFactory;
    }

    /**
     * Get Last Downloaded date
     *
     * @return string
     */
    public function getLastDownloadedDate()
    {
        $syncProductData = $this->getDownloadProductSyncUpdate();
        return $syncProductData['end_time'] ?? 'Never';
    }

    /**
     * Get Download Product Sync Update
     *
     * @return array|mixed|null
     */
    public function getDownloadProductSyncUpdate()
    {
        return $this->syncAssetsFactory->create()
            ->loadByProcessCode(SyncAssets::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS)
            ->getData();
    }

    /**
     * Get Last Downloaded Products
     *
     * @return int|mixed
     */
    public function getLastDownloadedProducts()
    {
        $syncProductData = $this->getDownloadProductSyncUpdate();
        $lastDownloaded = $syncProductData['total_downloaded_records'] ?? 0;

        return $lastDownloaded ?: 0;
    }

    /**
     * Get Last Downloaded Customers
     *
     * @return int|mixed
     */
    public function getFailedItemsCount()
    {
        $syncCustomerData = $this->getDownloadProductSyncUpdate();
        return $syncCustomerData['failed_counter'] ?? 0;
    }

    /**
     * Get Products At Ebizcharge
     *
     * @return int|mixed
     */
    public function getProductsAtEbizcharge()
    {
        $syncProductData = $this->getDownloadProductSyncUpdate();
        $remoteProducts = $syncProductData['remote_total_records'] ?? 0;
        return $remoteProducts ?: 0;
    }

    /**
     * Tab settings
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Download Products From EBizCharge Gateway');
    }

    /**
     * Get Tab Title
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Download Products From EBizCharge Gateway');
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
     * Get Product Downloads From Ebizcharge Url
     *
     * @return string
     */
    public function getProductsDownloadsFromEbizchargeUrl()
    {
        return $this->dataHelper->getUrl(Tabs::SYNC_ASSETS_FROM_EBIZCHARGE_URL);
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
     * Get View Downloaded Products URL
     *
     * @return string
     */
    public function getViewDownloadProductsUrl()
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
        /** @var $isEconnectActive */
        $isEconnectActive = $this->configResource->isEconnectDownlaodEnabled($storeId);
        /** @var $isDownloadProductsActive */
        $isDownloadProductsActive = $this->configResource->isDownlaodItemsEnabled($storeId);

        return $isEbizchargeActive && $isEconnectActive && $isDownloadProductsActive;
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
     * Check downloading status, in progress, completed etc
     *
     * @return int
     */
    public function syncStatus()
    {
        $downloadProducts = $this->getDownloadProductSyncUpdate();
        return $downloadProducts['status'] ?? '';
    }
}
