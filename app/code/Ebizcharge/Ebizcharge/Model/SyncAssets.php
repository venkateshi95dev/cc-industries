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

namespace Ebizcharge\Ebizcharge\Model;

use DateInterval;
use DateTime;
use Ebizcharge\Ebizcharge\Api\Data\SoapApiModelInterface;
use Ebizcharge\Ebizcharge\Api\Data\SyncAssetsInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory as EbizConfigFactory;
use Ebizcharge\Ebizcharge\Model\CustomerFactory as EbizCustomerFactory;
use Ebizcharge\Ebizcharge\Model\OrderFactory as EbizOrderFactory;
use Ebizcharge\Ebizcharge\Model\ProductFactory as EbizProductFactory;
use Exception;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use SoapFault;

/**
 * Sync Assets Model Class
 *
 * Class SyncAssets
 */
class SyncAssets extends AbstractModel implements IdentityInterface, SyncAssetsInterface
{
    /**
     * Cache Tag var
     *
     * @var string
     */
    protected $_cacheTag = 'ebizcharge_sync_assets_cron';

    /**
     * Event Prefix of table
     *
     * @var string
     */
    protected $_eventPrefix = 'ebizcharge_sync_assets_cron';

    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $_timeZone;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargLogger;

    /**
     * @var ShellCommand
     */
    protected ShellCommand $_shellCommand;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManager;


    protected EbizOrderFactory $_orderFactory;

    /**
     * @var RegionFactory
     */
    protected RegionFactory $_regionFactory;

    /**
     * @var EbizProductFactory
     */
    protected EbizProductFactory $_productFactory;


    protected EbizCustomerFactory $_customerFactory;


    protected EbizConfigFactory $configFactory;

    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $sessionManagerInterface;


    public function __construct(
        Context                 $context,
        Registry                $registry,
        TimezoneInterface       $timezone,
        EbizConfigFactory       $configFactory,
        EbizchargeLogger        $ebizchargeLogger,
        ShellCommand            $shellCommand,
        StoreManagerInterface   $storeManager,
        EbizProductFactory      $productFactory,
        EbizOrderFactory        $orderFactory,
        EbizCustomerFactory     $customerFactory,
        RegionFactory           $regionFactory,
        SessionManagerInterface $sessionManagerInterface,
        AbstractResource        $resource = null,
        AbstractDb              $resourceCollection = null,
        array                   $data = []
    )
    {
        /** parent construct */
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        /** @var _timeZone */
        $this->_timeZone = $timezone;
        /** @var  configFactory */
        $this->configFactory = $configFactory;
        /** @var  _ebizchargLogger */
        $this->_ebizchargLogger = $ebizchargeLogger;
        /** @var _shellCommand */
        $this->_shellCommand = $shellCommand;
        /** @var _storeManager */
        $this->_storeManager = $storeManager;
        /** @var _orderFactory */
        $this->_orderFactory = $orderFactory;
        /** @var _regionFactory */
        $this->_regionFactory = $regionFactory;
        /** @var _productFactory */
        $this->_productFactory = $productFactory;
        /** @var  _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var  sessionManagerInterface */
        $this->sessionManagerInterface = $sessionManagerInterface;
    }

    /**
     * Check Latest Customers At Ebizcharge
     *
     * @return array|int[]
     * @throws Exception
     */
    public function checkLatestCustomersAtEbizcharge()
    {
        $syncCheckedParams = [];
        $syncMessage = __("Checking Customers from EBizCharge Payment Hub.");

        try {
            /** @var $ebizchargeCustomers */
            $ebizchargeCustomers = $this->getLatestEbizchargeCustomers();
            $totalRemoteCustomers = count($ebizchargeCustomers);
            if ($totalRemoteCustomers > 0) {
                $syncMessage = __("Total " . $totalRemoteCustomers .
                    " remote customers found at EBizCharge Payment Gateway");
            }
            /** @var  $params */
            $params = [
                'status' => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                'remote_total_records' => $totalRemoteCustomers,
                'sync_remarks' => $syncMessage,
                'last_sync_counter' => 0,
                'total_downloaded_records' => 0
            ];
            /** saving sync customers values */
            $this->saveSyncCronValues($params);
            $syncMessage = __("Success total " . $totalRemoteCustomers .
                "  fetch customers from EBizCharge Payment Gateway");

        } catch (Exception $exception) {
            $syncMessage = "Exception occurred during checking the customers " . $exception->getMessage();
            $this->_ebizchargLogger->addCritical(__($syncMessage));
            $params = [
                'status' => SyncAssetsInterface::ASSETS_STATUS_FAILED,
                'remote_total_records' => 0,
                'sync_remarks' => $syncMessage,
                'last_sync_counter' => 0,
                'total_downloaded_records' => 0
            ];
            /** saving sync customers values */
            $this->saveSyncCronValues($params);
        }

        /** @var $syncCheckedParams */
        $syncCheckedParams = $this->loadByProcessCode(
            SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS
        )->getData();
        $syncCheckedParams['msg'] = $syncMessage;

        return $syncCheckedParams;
    }

    /**
     * Get Latest EBizCharge Customers
     *
     * @param int $position
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getLatestEbizchargeCustomers($position = 0): array
    {
        $customersResp = [];

        try {
            /** @var $syncAssetsFactory */
            $syncAssetsFactory = $this->loadByProcessCode(
                SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS
            );

            /** @var $startPosition */
            $startPosition = 0;

            /** @var $params */
            $params = [
                'position' => $startPosition,
                'http_request' => true
            ];

            /** @var $totalCustomersResp */
            $customersResp = $this->_customerFactory->create()->getCustomersAtEbizcharge($params);

        } catch (SoapFault $soapFault) {
            $this->_ebizchargLogger->addCritical(__(
                "Soap Exception occurred during fetching the customers from EBizCharge Gateway " .
                $soapFault->getMessage()
            ));
        }
        return $customersResp;
    }

    /**
     * Load by Process Code
     *
     * @param string $processCode
     * @return SyncAssets
     */
    public function loadByProcessCode(string $processCode): SyncAssets
    {
        $processId = $this->getResource()->loadByProcessCode($processCode);
        return $this->load($processId);
    }

    /**
     * Save Sync Cron  Values
     *
     * @param array $params
     * @param string $cronCode
     * @return bool
     */
    public function saveSyncCronValues(
        array $params = [],
              $cronCode = SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS
    ): bool
    {
        /** if params are more */
        if (count($params) > 0) {
            try {
                /** @var loading customers $customerSync */
                $asetSyncProcess = $this->loadByProcessCode($cronCode);
                $syncParams = $asetSyncProcess->getData();

                if (count($params) > 0) {
                    foreach ($params as $key => $param) {
                        $syncParams[$key] = $param;
                    }
                }
                /** if customer sysnc  */
                $asetSyncProcess->setData($syncParams);

                /** saving sync customers */
                $asetSyncProcess->save();

                /** logging to the logger */
                $this->_ebizchargLogger->addInfo("Sync Assets Data saved to the databases ");
                return true;
            } catch (Exception $exception) {
                $this->_ebizchargLogger->addInfo("Exception occured during saving data Error: " .
                    $exception->getMessage());
                return false;
            }
        } else {
            $this->_ebizchargLogger->addInfo("the params provided are not valid ");
            return false;
        }
    }

    /**
     * Check Total Local Customers
     *
     * @return array|mixed|null
     * @throws Exception
     */
    public function checkTotalLocalCustomers(): mixed
    {
        try {
            $origMemoryLimit = $this->getMemoryLimit();
            $currentMemoryLimit = $this->getMemoryLimitInt();

            if ($currentMemoryLimit <= 756) {
                $this->setMemoryLimit("2048M");
            }
            /** @var $totalLocalCustomers */
            $totalLocalCustomers = $this->_customerFactory->create()->checkTotalLocalCustomers();

            /** @var $ebizchargeCustomers */
            $totalLocalCustomers = count($totalLocalCustomers);

            /** @var  $params */
            $params = [
                'status' => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                'remote_total_records' => $totalLocalCustomers
            ];
            /** saving sync customers values */
            $this->saveSyncCronValues($params, SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_CUSTOMERS);
            $message = __("Success total " . $totalLocalCustomers . " fetch customers from EBizCharge Gateway");

        } catch (Exception $exception) {
            $this->_ebizchargLogger->addCritical(__("Exception occurred during checking the customers " .
                $exception->getMessage()));
            $params = [
                'status' => SyncAssetsInterface::ASSETS_STATUS_FAILED,
                'remote_total_records' => 0,
            ];

            /** saving sync customers values */
            $this->saveSyncCronValues($params, SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_CUSTOMERS);
            $message = __("An exception is occurred during checking customers");
        }
        /** @var $syncCheckedParams */
        $syncCheckedParams = $this->loadByProcessCode(SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_CUSTOMERS)
            ->getData();

        $syncCheckedParams['msg'] = $message;

        return $syncCheckedParams;
    }

    /**
     * Get Memory Limit
     *
     * @return false|string
     */
    public function getMemoryLimit(): false|string
    {
        return ini_get("memory_limit");
    }

    /**
     * Get Memory Limit Int
     *
     * @return array|false|string|string[]
     */
    public function getMemoryLimitInt(): array|false|string
    {
        $memoryLimit = $this->getMemoryLimit();
        $memKey = substr($memoryLimit, strlen($memoryLimit) - 1);

        if (in_array($memKey, ["M", "G"])) {
            $memoryLimit = str_replace($memKey, "", $memoryLimit);
        }
        return $memoryLimit;
    }

    /**
     * Set Memory Limit
     *
     * @param mixed $memoryLimit
     * @return void
     */
    public function setMemoryLimit($memoryLimit = "1024M"): void
    {
        // phpcs:ignore
        ini_set("memory_limit", $memoryLimit);
    }

    /**
     * Region Id
     *
     * @return string
     */
    public function getRegionId()
    {
        return $this->_regionFactory->create()->getRegionId();
    }

    /**
     * Get Region Factory
     *
     * @return Region
     */
    public function getRegionFactory()
    {
        return $this->_regionFactory->create();
    }

    /**
     * Get Download Progress
     *
     * @param string $currentProgress
     * @return array|int
     */
    public function getDownloadProgress(string $currentProgress): int|array
    {
        /** @var $progressData */

        $progressResp = [
            'progress_counter' => 0,
            'total_remote_records' => 0,
            'total_downloaded_records' => 0,
            'sync_remarks' => '',
            'start_time' => '',
            'end_time' => '',
            'process_current_status' => 0,
            'status' => 0,
            'total_time' => 0
        ];
        $progressData = $this->getSyncProgressStats($currentProgress);

        $progressCounter = 0;

        /** Progress Data  */
        if (count($progressData) > 0) {
            $totalRemoteRecords = (double)$progressData['remote_total_records'];
            $totalDownloadedRecords = (double)$progressData['total_downloaded_records'];

            if ($totalRemoteRecords <= 0) {
                return (int)$totalRemoteRecords;
            }
            /** @var $progress */
            $progressCounter = (int)(($totalDownloadedRecords / $totalRemoteRecords) * 100);

            $progressResp = [
                'progress_counter' => $progressCounter,
                'total_remote_records' => $totalRemoteRecords,
                'total_downloaded_records' => $totalDownloadedRecords,
                'sync_remarks' => $progressData['sync_remarks'],
                'start_time' => $progressData['start_time'],
                'end_time' => $progressData['end_time'],
                'process_current_status' => $progressData['process_current_status'],
                'status' => $progressData['status'],
                'total_time' => $progressData['total_time'],
                'failed_counter' => $progressData['failed_counter']
            ];
        }

        return $progressResp;
    }

    /**
     * Get Sync Progress Status
     *
     * @param string $currentProgress
     * @return array|bool|mixed|null
     */
    public function getSyncProgressStats(string $currentProgress): mixed
    {
        if (!$currentProgress) {
            return [];
        }

        $syncProcessProgress = $this->loadByProcessCode($currentProgress);

        if ($syncProcessProgress) {
            return $syncProcessProgress->getData();
        }
        return [];
    }

    /**
     * Get getUploadProgress Progress
     *
     * @param string $currentProgress
     * @return array|int
     */
    public function getUploadProgress(string $currentProgress)
    {
        /** @var $progressData */

        $progressResp = [
            'progress_counter' => 0,
            'total_remote_records' => 0,
            'total_uploaded_records' => 0,
            'sync_remarks' => '',
            'start_time' => '',
            'end_time' => '',
            'process_current_status' => 0,
            'status' => 0,
            'total_time' => 0
        ];
        $progressData = $this->getSyncProgressStats($currentProgress);

        $progressCounter = 0;

        /** Progress Data  */
        if (count($progressData) > 0) {
            $totalRemoteRecords = (double)$progressData['remote_total_records'];
            $totalDownloadedRecords = (double)$progressData['total_downloaded_records'];

            if ($totalRemoteRecords <= 0) {
                return (int)$totalRemoteRecords;
            }
            /** @var showing $progress */
            $progressCounter = (int)(($totalDownloadedRecords / $totalRemoteRecords) * 100);

            $progressResp = [
                'progress_counter' => $progressCounter,
                'total_remote_records' => $totalRemoteRecords,
                'total_uploaded_records' => $totalDownloadedRecords,
                'sync_remarks' => $progressData['sync_remarks'],
                'start_time' => $progressData['start_time'],
                'end_time' => $progressData['end_time'],
                'process_current_status' => $progressData['process_current_status'],
                'status' => $progressData['status'],
                'total_time' => $progressData['total_time']
            ];
        }

        return $progressResp;
    }

    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus(): int
    {
        return (int)$this->getData(SyncAssetsInterface::STATUS);
    }

    /**
     * Get failed counter
     *
     * @return int|null
     */
    public function getFailedCounter(): ?int
    {
        return (int)$this->getData(SyncAssetsInterface::FAILED_COUNTER);
    }

    /**
     * Check Latest Products At EBizCharge
     *
     * @return array|int[]
     * @throws Exception
     */
    public function checkLatestProductsAtEbizcharge(): array
    {
        /** @var  $syncCheckedParams */
        $syncCheckedParams = [
            "error" => true,
            "status" => false,
            "msg" => __("Could not found any products at EBizCharge Hub.")
        ];

        try {
            /** @var $ebizchargeProducts */
            $ebizchargeProducts = $this->getLatestEbizchargeItems();

            $totalRemoteProducts = count($ebizchargeProducts);

            /** @var  $params */
            $params = [
                'status' => SyncAssetsInterface::ASSETS_STATUS_FAILED,
                'remote_total_records' => $totalRemoteProducts,
                'last_sync_counter' => 0,
                'total_downloaded_records' => 0
            ];

            /** saving sync customers values */
            $this->saveSyncCronValues($params, SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS);
            $message = __("Success total " . $totalRemoteProducts . " fetch products from EBizCharge Gateway");

        } catch (Exception $exception) {
            $this->_ebizchargLogger->addCritical(__("Exception occurred during checking the products. " .
                $exception->getMessage()));
            $params = [
                'status' => SyncAssetsInterface::ASSETS_STATUS_FAILED,
                'remote_total_records' => 0,
                'last_sync_counter' => 0,
                'total_downloaded_records' => 0
            ];

            /** saving sync products values */
            $this->saveSyncCronValues($params, SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS);
            $message = __("An exception occurred during checking products. ");
        }

        /** @var $syncCheckedParams */
        $syncCheckedParams = $this->loadByProcessCode(SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS)
            ->getData();
        $syncCheckedParams['msg'] = $message;

        return $syncCheckedParams;
    }

    /**
     * Get EBizCharge product/items that need to import to magento
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getLatestEbizchargeItems(): array
    {
        /** @var  $productsCollection */
        $productsCollection = [];
        $configFactory = $this->configFactory->create();
        $store = $configFactory->getStore();
        $storeId = $store->getId();
        $envPrfix = $configFactory->getEnvoirnmentPrefix($storeId);

        try {
            $syncAssetsFactory = $this->loadByProcessCode(SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS);
            $startPosition = $syncAssetsFactory->getLastSyncTotalRecords() ?: 0;
            $startPosition = 0;

            /** @var $ebizChargeProducts */
            $ebizChargeProducts = $this->_productFactory->create()->getItemsFromEbizcharge(
                '',
                '',
                [],
                "",
                $startPosition,
                SoapApiModelInterface::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT
            );

            if ($ebizChargeProducts && count($ebizChargeProducts) > 0) {
                foreach ($ebizChargeProducts as $ebizProduct) {

                    $ebizProduct = (array)$ebizProduct;
                    $ebizItemID = isset($ebizProduct["ItemId"]) ? $ebizProduct["ItemId"] : "";
                    $ebizItemSku = isset($ebizProduct["SKU"]) ? $ebizProduct["SKU"] : "";
                    $ebizDivisionID = isset($ebizProduct["DivisionId"]) ? $ebizProduct["DivisionId"] : "";

                    if (str_contains($ebizDivisionID, $envPrfix) === false) {
                        continue;
                    }
                    if ($this->isProductExistsAtLocal($ebizProduct)) {
                        continue;
                    }
                    $productsCollection[] = $ebizProduct;
                }
            }

        } catch (SoapFault $soapFault) {
            $this->_ebizchargLogger->addCritical(__(
                "Soap Exception occurred during fetching the items from EBizCharge Gateway.  " .
                $soapFault->getMessage()
            ));
        } catch (NoSuchEntityException $e) {
            $this->_ebizchargLogger->addCritical(__(
                "Soap Exception occurred during fetching the items from EBizCharge Gateway.  " .
                $e->getMessage()
            ));
        } catch (\Magento\Setup\Exception $e) {
            $this->_ebizchargLogger->addCritical(__(
                "Soap Exception occurred during fetching the items from EBizCharge Gateway.  " .
                $e->getMessage()
            ));
        }

        return $productsCollection;
    }

    /**
     * Get Last Sync Total Records
     *
     * @return array|mixed|string|null
     */
    public function getLastSyncTotalRecords(): mixed
    {
        return $this->getData(SyncAssetsInterface::LAST_SYNC_TOTAL_RECORDS);
    }

    /**
     * @param array $ebizProduct
     * @return bool
     * @throws NoSuchEntityException
     * @throws \Magento\Setup\Exception
     */
    public function isProductExistsAtLocal(array $ebizProduct = []): bool
    {
        /** @var  $isProductExists */
        $isProductExists = false;
        /** @var  $storeId */
        $storeId = $this->_storeManager->getStore()->getId();

        if (!is_array($ebizProduct) || count($ebizProduct) === 0) {
            return $isProductExists;
        }
        if (isset($ebizProduct['ItemId'])) {
            /** @var  $productSID */
            $productSID = isset($ebizProduct['ItemId']) ? $ebizProduct['ItemId'] : "";
            $productSku = isset($ebizProduct['SKU']) ? $ebizProduct['SKU'] : "";
            /** @var  $productDivisionId */
            $productDivisionId = $ebizProduct['DivisionId'] ?? "";
            $productId = $this->_productFactory->create()->getResource()->loadBySku($productSku);
            if (!empty($productId)) {
                $product = $this->_productFactory->create()->load($productId);
                $isProductExists = true;
            }
        }
        return $isProductExists;
    }

    /**
     * Check Total Local Products
     *
     * @return array|mixed
     */
    public function checkTotalLocalProducts(): mixed
    {
        try {

            $origMemoryLimit = $this->getMemoryLimit();
            $currentMemoryLimit = $this->getMemoryLimitInt();

            if ($currentMemoryLimit <= 756) {
                $this->setMemoryLimit("2048M");
            }
            /** @var $localProducts */
            $localProducts = $this->_productFactory->create()->getLatestLocalProducts();

            /** @var $totalLocalProducts */
            $totalLocalProducts = $localProducts && count($localProducts) ? count($localProducts) : 0;

            /** @var  $params */
            $params = [
                'status' => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                'remote_total_records' => $totalLocalProducts
            ];
            /** saving sync products values */
            $this->saveSyncCronValues($params, SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_ITEMS);
            $message = __("Success total " . $totalLocalProducts . " products at magento");

        } catch (Exception $exception) {
            $this->_ebizchargLogger->addCritical(__("Exception occurred during checking the products.  " .
                $exception->getMessage()));
            $params = [
                'status' => SyncAssetsInterface::ASSETS_STATUS_FAILED,
                'remote_total_records' => 0,
            ];

            /** saving sync customers values */
            $this->saveSyncCronValues($params, SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_ITEMS);

            $message = __("An exception is occurred during checking products. ");
        }
        /** @var $syncCheckedParams */
        $syncCheckedParams = $this->loadByProcessCode(SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_ITEMS)
            ->getData();
        $syncCheckedParams['msg'] = $message;

        return $syncCheckedParams;
    }

    /**
     * Check Latest Orders At EBizCharge
     *
     * @return array|int
     */
    public function checkLatestOrdersAtEbizcharge(): array|int
    {
        $syncCheckedParams = [];
        try {
            /** @var $ebizchargeOrders */
            $ebizchargeOrders = $this->getLatestEbizchargeOrders();
            $totalRemoteOrders = count($ebizchargeOrders);

            /** @var  $params */
            $params = [
                'status' => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                'remote_total_records' => $totalRemoteOrders,
                'last_sync_counter' => 0,
                'total_downloaded_records' => 0
            ];
            /** saving sync customers values */
            $this->saveSyncCronValues($params, SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS);
            $message = __("Success total " . $totalRemoteOrders . " fetch orders from EBizCharge Gateway");
        } catch (Exception $exception) {
            $this->_ebizchargLogger->addCritical(__("Exception occurred during checking the customers " .
                $exception->getMessage()));
            $params = [
                'status' => SyncAssetsInterface::ASSETS_STATUS_FAILED,
                'remote_total_records' => 0,
                'last_sync_counter' => 0,
                'total_downloaded_records' => 0
            ];
            /** saving sync orders values */
            $this->saveSyncCronValues($params);
            $message = __("An exception is occurred during checking customers");
        }
        /** @var $syncCheckedParams */
        $syncCheckedParams = $this->loadByProcessCode(
            SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS
        )->getData();
        $syncCheckedParams['msg'] = $message;

        return $syncCheckedParams;
    }

    /**
     * Get Latest EBizCharge Orders
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getLatestEbizchargeOrders(): array
    {
        /** @var  $ebizOrdersCollection */
        $ebizOrdersCollection = [];

        try {
            /** Get Orders from EBizCharge */
            $syncFactoryObj = $this->loadByProcessCode(
                SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS
            );
            /** @var $syncOrderParams */
            $syncOrderParams = [
                'position' => $syncFactoryObj->getLastSyncTotalRecords() ?
                    $syncFactoryObj->getLastSyncTotalRecords() : 0
            ];
            $configFactory = $this->configFactory->create();
            $storeId = $configFactory->getStoreId();
            $divisionId = $configFactory->getDivisionID($storeId);
            /** @var  $ebizChargeOrders */
            $ebizChargeOrders = $this->_orderFactory->create()->getOrdersFromEbizcharge($syncOrderParams);

            $counter = 0;
            if (count($ebizChargeOrders) > 0) {
                foreach ($ebizChargeOrders as $ebizChargeOrder) {
                    $ebizOrder = (array)$ebizChargeOrder;

                    $ebizCustomerId = isset($ebizOrder["CustomerId"]) ?
                        trim(str_replace(" ", "", $ebizOrder["CustomerId"])) : "";
                    $ebizOrderInternalId = isset($ebizOrder["SalesOrderInternalId"]) ?
                        trim(str_replace(" ", "", $ebizOrder["SalesOrderInternalId"])) : "";
                    $ebizChargeOrderNumber = isset($ebizOrder["SalesOrderNumber"]) ?
                        trim(str_replace(" ", "", $ebizOrder["SalesOrderNumber"])) : "";
                    $ebizChargeDivisionId = isset($ebizOrder["DivisionId"]) ?
                        trim(str_replace(" ", "", $ebizOrder["DivisionId"])) : "";

                    if ($ebizOrderInternalId === "" || $ebizChargeOrderNumber === "" ||
                        $ebizChargeDivisionId === "" || $ebizCustomerId === "Guest") {
                        continue;
                    }

                    if (strpos($ebizChargeDivisionId, $divisionId) === false) {
                        continue;
                    }

                    if ($ebizCustomerId !== "") {
                        $customer = $this->_customerFactory->create()->loadByEbizCustomerId($ebizCustomerId);
                        /*if (!$customer->getId()) {
                            $customer = $this->_customerFactory->create()->getEbizCustomerById($ebizCustomerId);
                            if (is_object($customer->GetCustomerResult)) {
                                if (isset($customer->GetCustomerResult->Email) &&
                             $customer->GetCustomerResult->Email === "") {
                                    continue;
                                }
                            }
                        }*/
                    }
                    if (!$this->orderExistsLocally($ebizChargeOrder)) {
                        $ebizOrdersCollection[] = $ebizChargeOrder;
                    }
                }
            }

        } catch (SoapFault $soapFault) {
            $this->_ebizchargLogger->addCritical(__(
                "Soap Exception occurred during fetching the Orders from EBizCharge Gateway " .
                $soapFault->getMessage()
            ));
        }

        return $ebizOrdersCollection;
    }

    /**
     * Order Exists Locally
     *
     * @param mixed|null $ebizChargeOrder
     * @return bool
     * @throws NoSuchEntityException
     */
    public function orderExistsLocally(
        $ebizChargeOrder = null
    ): bool
    {

        $isOrderExistsLocally = false;
        $ebizOrder = (array)$ebizChargeOrder;
        $ebizOrderInternalId = isset($ebizOrder["SalesOrderInternalId"]) ?
            trim(str_replace(" ", "", $ebizOrder["SalesOrderInternalId"])) : "";
        $ebizChargeOrderNumber = isset($ebizOrder["SalesOrderNumber"]) ?
            trim(str_replace(" ", "", $ebizOrder["SalesOrderNumber"])) : "";
        $ebizChargeDivisionId = isset($ebizOrder["DivisionId"]) ?
            trim(str_replace(" ", "", $ebizOrder["DivisionId"])) : "";

        /** @var  $storeId */
        $storeId = $this->_storeManager->getStore()->getId();
        // var_dump($ebizChargeOrderNumber, $ebizChargeDivisionId);

        /** @var  $localOrder */
        $localOrder = $this->_orderFactory->create()->loadOrderByEbizId($ebizChargeOrderNumber);

        //  var_dump($localOrder->getIncrementId());

        if ($localOrder->getIncrementId()) {
            $isOrderExistsLocally = true;
        }

        return $isOrderExistsLocally;
    }

    /**
     * Check Total Local Orders
     *
     * @return array|mixed|null
     * @throws Exception
     */
    public function checkTotalLocalOrders()
    {
        try {
            $origMemoryLimit = $this->getMemoryLimit();
            $currentMemoryLimit = $this->getMemoryLimitInt();

            if ($currentMemoryLimit <= 756) {
                $this->setMemoryLimit("2048M");
            }

            /** @var $localOrders */
            $localOrders = $this->_orderFactory->create()->getLatestLocalOrders();
            $totalLocalOrders = count($localOrders);

            /** @var  $params */
            $params = [
                'status' => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                'remote_total_records' => $totalLocalOrders
            ];
            /** saving sync products values */
            $this->saveSyncCronValues($params, SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_ORDERS);
            $message = __("Success total " . $totalLocalOrders . " orders at ebizcharge");

        } catch (Exception $exception) {

            $this->_ebizchargLogger->addCritical(__("Exception occurred during checking the orders " .
                $exception->getMessage()));
            $params = [
                'status' => SyncAssetsInterface::ASSETS_STATUS_FAILED,
                'remote_total_records' => 0
            ];

            /** saving sync customers values */
            $this->saveSyncCronValues($params, SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_ORDERS);
            $message = __("An exception is occurred during checking customers");
        }
        /** @var $syncCheckedParams */
        $syncCheckedParams = $this->loadByProcessCode(SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_ORDERS)
            ->getData();
        $syncCheckedParams['msg'] = $message;

        return $syncCheckedParams;
    }

    /**
     * @param $order
     * @return array|null
     * @throws Exception
     */
    public function importEbizchargeOrdersToMagento($order = null)
    {
        $this->_ebizchargLogger->addInfo(__('Downloading and saving order to Magento....'));
        $orderResp = [];
        /** @var  $incrementId */
        try {
            $orderResp = $this->_orderFactory->create()->saveOrderToMagento($order);
        } catch (NoSuchEntityException $e) {
            $this->_ebizchargLogger->addCritical(__("Error occurred during downloading orders. Error: " .
                $e->getMessage()));
        }
        return $orderResp;
    }

    /**
     * Get Order by EbizInternal Id
     *
     * @param mixed $ebizInternalId
     * @return Order
     */
    public function getOrderByEbizInternalId($ebizInternalId)
    {
        return $this->_orderFactory->create()->loadOrderByEbizInternalId($ebizInternalId);
    }

    /**
     * Load Order By Ebiz Internal Id
     *
     * @param String|null $ebizOrderInternalId
     * @return Order
     */
    public function loadOrderByEbizInternalId(string $ebizOrderInternalId = null)
    {
        return $localOrder = $this->_orderFactory->create()->loadOrderByEbizInternalId($ebizOrderInternalId);
    }

    /**
     * Is Product Exists already
     *
     * @param mixed $ebizProductInternalId
     * @return mixed
     */
    public function isProductExists($ebizProductInternalId)
    {
        return $this->_productFactory->create()->isProductExistsLocal($ebizProductInternalId);
    }

    /**
     * @param array $ebizItem
     * @param $isDownload
     * @return float
     * @throws Exception
     */
    public function importEbizchargeItemToMagento(array $ebizItem = [], $isDownload = false)
    {
        return $this->_productFactory->create()->createEbizchargeProductToMagento(
            $ebizItem,
            "",
            $isDownload
        );
    }

    /**
     * Get Default country
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getDefaultCountry()
    {
        $store = $this->_storeManager->getStore();
        return $this->configFactory->create()->getDefaultCountryCode($store->getId());
    }

    /**
     * Get Date Time Diff
     *
     * @param mixed $startDateTime
     * @param mixed $endDateTime
     * @return DateInterval|false
     * @throws Exception
     */
    public function getDateTimeDiff($startDateTime, $endDateTime)
    {
        $startDate = new DateTime($startDateTime);
        $endDate = new DateTime($endDateTime);
        $dateDiff = $startDate->diff($endDate);

        return $dateDiff;
    }

    /**
     * Set Status
     *
     * @param int $status
     * @return SyncAssetsInterface
     */
    public function setStatus(int $status): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::STATUS, $status);
    }

    /**
     * Set failed counter
     *
     * @param int $counter
     * @return SyncAssetsInterface
     */
    public function setFailedCounter(int $counter): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::FAILED_COUNTER, $counter);
    }

    /**
     * Get Identities
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [SyncAssetsInterface::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get Default Values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }

    /**
     * Get Current Date Time
     *
     * @return false|string
     */
    public function getCurrentDateTime()
    {
        return $this->_timeZone->date()->format('Y-m-d H:i:s');
    }

    /**
     * Get Current Date
     *
     * @return false|string
     */
    public function getCurrentDate()
    {
        return $this->_timeZone->date()->format('Y-m-d');
    }

    /**
     * Get Current Time
     *
     * @return false|string
     */
    public function getCurrentTime()
    {
        return $this->_timeZone->date()->format('H:i:s');
    }

    /**
     * Get Entiry Id
     *
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->getData(SyncAssetsInterface::ENTITY_ID);
    }

    /**
     * Get Process Code
     *
     * @return string
     */
    public function getProcessCode(): string
    {
        return $this->getData(SyncAssetsInterface::PROCESS_CODE);
    }

    /**
     * Get Process Name
     *
     * @return string
     */
    public function getProcessName(): string
    {
        return $this->getData(SyncAssetsInterface::PROCESS_NAME);
    }

    /**
     * Get Process Group
     *
     * @return string
     */
    public function getProcessGroup(): string
    {
        return $this->getData(SyncAssetsInterface::PROCESS_GROUP);
    }

    /**
     * Get Asset Type
     *
     * @return string
     */
    public function getAssetType(): string
    {
        return $this->getData(SyncAssetsInterface::ASSET_TYPE);
    }

    /**
     * Get Start Time
     *
     * @return string
     */
    public function getStartTime(): string
    {
        return $this->getData(SyncAssetsInterface::START_TIME);
    }

    /**
     * Get End Time
     *
     * @return string
     */
    public function getEndTime(): string
    {
        return $this->getData(SyncAssetsInterface::END_TIME);
    }

    /**
     * Get Process Current Status
     *
     * @return string
     */
    public function getProcessCurrentStatus(): string
    {
        return $this->getData(SyncAssetsInterface::PROCESS_CURRENT_STATUS);
    }

    /**
     * Get Total Time
     *
     * @return string
     */
    public function getTotalTime(): string
    {
        return $this->getData(SyncAssetsInterface::TOTAL_TIME);
    }

    /**
     * Get Remote Total Records
     *
     * @return int
     */
    public function getRemoteTotalRecords(): int
    {
        return $this->getData(SyncAssetsInterface::REMOTE_TOTAL_RECORDS);
    }

    /**
     * Get Total Downlaoded Records
     *
     * @return int
     */
    public function getTotalDownloadedRecords(): int
    {
        return $this->getData(SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS);
    }

    /**
     * Get Sync Remarks
     *
     * @return string
     */
    public function getSyncRemarks(): string
    {
        return $this->getData(SyncAssetsInterface::SYNC_REMARKS);
    }

    /**
     * Get Last Sync Counter
     *
     * @return int
     */
    public function getLastSyncCounter(): int
    {
        return $this->getData(SyncAssetsInterface::LAST_SYNC_COUNTER);
    }

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->getData(SyncAssetsInterface::CREATED_AT);
    }

    /**
     * Set Entity Id
     *
     * @param int $entityId
     * @return SyncAssetsInterface
     */
    public function setEntityId($entityId): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::ENTITY_ID, $entityId);
    }

    /**
     * Set Process Code
     *
     * @param string $processCode
     * @return SyncAssetsInterface
     */
    public function setProcessCode(string $processCode): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::PROCESS_CODE, $processCode);
    }

    /**
     * Set Process Name
     *
     * @param string $processName
     * @return SyncAssetsInterface
     */
    public function setProcessName(string $processName): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::PROCESS_NAME, $processName);
    }

    /**
     * Set Process Group
     *
     * @param string $processGroup
     * @return SyncAssetsInterface
     */
    public function setProcessGroup(string $processGroup): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::PROCESS_GROUP, $processGroup);
    }

    /**
     * Set Asset Type
     *
     * @param string $assetType
     * @return SyncAssetsInterface
     */
    public function setAssetType(string $assetType): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::ASSET_TYPE, $assetType);
    }

    /**
     * Set Start Time
     *
     * @param string $startTime
     * @return SyncAssetsInterface
     */
    public function setStartTime(string $startTime): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::START_TIME, $startTime);
    }

    /**
     * Set End Time
     *
     * @param string $endTime
     * @return SyncAssetsInterface
     */
    public function setEndTime(string $endTime): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::END_TIME, $endTime);
    }

    /**
     * Set Process Current Status
     *
     * @param string $processCurrentStatus
     * @return SyncAssetsInterface
     */
    public function setProcessCurrentStatus(string $processCurrentStatus): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::PROCESS_CURRENT_STATUS, $processCurrentStatus);
    }

    /**
     * Set Total Time
     *
     * @param string $totalTime
     * @return SyncAssetsInterface
     */
    public function setTotalTime(string $totalTime): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::TOTAL_TIME, $totalTime);
    }

    /**
     * Set Remote Total Records
     *
     * @param int $remoteTotalRecords
     * @return SyncAssetsInterface
     */
    public function setRemoteTotalRecords(int $remoteTotalRecords): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::REMOTE_TOTAL_RECORDS, $remoteTotalRecords);
    }

    /**
     * Set Total Downlaoded Records
     *
     * @param int $totalDownloadedRecords
     * @return SyncAssetsInterface
     */
    public function setTotalDownloadedRecords(int $totalDownloadedRecords): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS, $totalDownloadedRecords);
    }

    /**
     * Set Sync Remarks
     *
     * @param string $syncRemarks
     * @return SyncAssetsInterface
     */
    public function setSyncRemarks(string $syncRemarks): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::SYNC_REMARKS, $syncRemarks);
    }

    /**
     * Set Last Sync Counter
     *
     * @param int $lastSyncCounter
     * @return SyncAssetsInterface
     */
    public function setLastSyncCounter(int $lastSyncCounter): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::LAST_SYNC_COUNTER, $lastSyncCounter);
    }

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return SyncAssetsInterface
     */
    public function setCreatedAt(string $createdAt): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::CREATED_AT, $createdAt);
    }

    /**
     * Set Last Sync Total Records
     *
     * @param string $lastSyncTotalRecords
     * @return SyncAssetsInterface
     */
    public function setLastSyncTotalRecords(string $lastSyncTotalRecords): SyncAssetsInterface
    {
        return $this->setData(SyncAssetsInterface::LAST_SYNC_TOTAL_RECORDS, $lastSyncTotalRecords);
    }

    /**
     * Function Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\SyncAssets::class);
    }
}
