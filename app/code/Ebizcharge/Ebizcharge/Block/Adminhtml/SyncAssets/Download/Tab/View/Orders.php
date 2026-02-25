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
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Text\ListText;

/**
 * SyncAssets Download Orders Tab
 *
 * Class Products
 */
class Orders extends ListText implements TabInterface
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
        return __('Download Orders');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Download Orders');
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
     * @inheritdoc
     */
    public function canShowTab()
    {
        return $this->authorization->isAllowed('Ebizcharge_Ebizcharge::Download_Orders');
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
        return $this->_syncAssetsFactory->create()
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
        return $this->_dataHelper->getUrl(Tabs::ORDERS_VIEW_URL);
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
     * Get Orders Download URL
     *
     * @return string
     */
    public function getOrdersDownloadsFromEbizchargeUrl()
    {
        return $this->_dataHelper->getUrl(Tabs::SYNC_ASSETS_FROM_EBIZCHARGE_URL);
    }

    /**
     * Get Orders Check URL
     *
     * @return string
     */
    public function getOrdersCheckAtEbizchargeUrl()
    {
        return $this->_dataHelper->getUrl(Tabs::CHECK_ASSETS_AT_EBIZCHARGE_URL);
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
