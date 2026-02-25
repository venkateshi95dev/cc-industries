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
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Ebizcharge\Ebizcharge\Model\SyncAssets;
use Ebizcharge\Ebizcharge\Model\SyncAssetsFactory;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Text\ListText;

/**
 * SyncAssets Download Products Tab
 *
 * Class Products
 */
class Products extends ListText implements TabInterface
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
    protected ConfigFactory $_configFactory;

    /**
     * @var SyncAssetsFactory
     */
    protected SyncAssetsFactory $_syncAssetsFactory;

    /**
     * @param Context $context
     * @param DataHelper $dataHelper
     * @param ConfigFactory $configFactory
     * @param SyncAssetsFactory $syncAssetsFactory
     * @param array $data
     * @param AuthorizationInterface|null $authorization
     */
    public function __construct(
        Context                 $context,
        DataHelper              $dataHelper,
        ConfigFactory          $configFactory,
        SyncAssetsFactory       $syncAssetsFactory,
        array                   $data = [],
        ?AuthorizationInterface $authorization = null
    ) {
        $this->authorization = $authorization ?? ObjectManager::getInstance()->get(AuthorizationInterface::class);
        parent::__construct($context, $data);
        /** @var _dataHelper */
        $this->_dataHelper = $dataHelper;
        /** @var  _configFactory */
        $this->_configFactory = $configFactory;
        /** @var _syncAssetsFactory */
        $this->_syncAssetsFactory = $syncAssetsFactory;
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Download Products');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Download Products');
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
        return $this->_syncAssetsFactory->create()
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
     * Can Show Tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
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
        return $this->_dataHelper->getUrl(Tabs::CHECK_ASSETS_AT_EBIZCHARGE_URL);
    }

    /**
     * Get Product Downloads From Ebizcharge Url
     *
     * @return string
     */
    public function getProductsDownloadsFromEbizchargeUrl()
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
     * Get View Downloaded Products URL
     *
     * @return string
     */
    public function getViewDownloadProductsUrl()
    {
        return $this->_dataHelper->getUrl(Tabs::PRODUCTS_VIEW_URL);
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
        $storeId = $this->_dataHelper->getStore()->getId();

        /** @var $isEbizchargeActive */
        $isEbizchargeActive = $this->_configFactory->create()->isEbizchargeActive($storeId);
        /** @var $isEconnectActive */
        $isEconnectActive = $this->_configFactory->create()->isEconnectDownlaodEnabled($storeId);
        /** @var $isDownloadProductsActive */
        $isDownloadProductsActive = $this->_configFactory->create()->isDownlaodItemsEnabled($storeId);

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
        $storeId = $this->_dataHelper->getStore()->getId();

        return $this->_configFactory->create()->isEconnectDownlaodEnabled($storeId);
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
