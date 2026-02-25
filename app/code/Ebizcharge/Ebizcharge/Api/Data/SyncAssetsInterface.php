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

namespace Ebizcharge\Ebizcharge\Api\Data;

/**
 * Interface SyncAssetsInterface
 *
 * Sync Assets Data Interface
 */
interface SyncAssetsInterface
{
    /**
     * Cache Tag
     *
     * @const CACHE_TAG
     */
    public const CACHE_TAG = 'ebizcharge_sync_assets_cron';

    /**
     * Cron Code Download Customers
     *
     * @const SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS
     */
    public const SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS = 'sync_assets_cron_download_customers';

    /**
     * Cron Code Download items
     *
     * @const SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS
     */
    public const SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS = 'sync_assets_cron_download_items';

    /**
     * Cron Code Download Orders
     *
     * @const SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS
     */
    public const SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS = 'sync_assets_cron_download_orders';

    /**
     * Cron Code Upload Customers
     *
     * @const SYNC_ASSETS_CRON_CODE_UPLOAD_CUSTOMERS
     */
    public const SYNC_ASSETS_CRON_CODE_UPLOAD_CUSTOMERS = 'sync_assets_cron_upload_customers';

    /**
     * Cron Code Upload items
     *
     * @const SYNC_ASSETS_CRON_CODE_UPLOAD_ITEMS
     */
    public const SYNC_ASSETS_CRON_CODE_UPLOAD_ITEMS = 'sync_assets_cron_upload_items';

    /**
     * Cron Code Upload Orders
     *
     * @const SYNC_ASSETS_CRON_CODE_UPLOAD_ORDERS
     */
    public const SYNC_ASSETS_CRON_CODE_UPLOAD_ORDERS = 'sync_assets_cron_upload_orders';

    /**
     * Sync Assets Type Downlaod
     *
     * @const SYNC_ASSETS_TYPE_DOWNLOAD
     */
    public const SYNC_ASSETS_TYPE_DOWNLOAD = 'assets_download';

    /**
     * Sync Assets Type Upload
     *
     * @const SYNC_ASSETS_TYPE_UPLOAD
     */
    public const SYNC_ASSETS_TYPE_UPLOAD = 'assets_upload';

    /**
     * Assets Sync Cron Download Group
     *
     * @const SYNC_ASSETS_CRON_DOWNLOAD_GROUP
     */
    public const SYNC_ASSETS_CRON_DOWNLOAD_GROUP = 'assets_download_group';

    /**
     * Sync Assets Cron Upload Group
     *
     * @const SYNC_ASSETS_CRON_UPLOAD_GROUP
     */
    public const SYNC_ASSETS_CRON_UPLOAD_GROUP = 'assets_upload_group';

    /**
     * Cron Current Status Started
     *
     * @const SYNC_ASSETS_CURRENT_STATUS_STARTED
     */
    public const SYNC_ASSETS_CURRENT_STATUS_STARTED = 'started';

    /**
     * Cron Current Status Pending
     *
     * @const SYNC_ASSETS_CURRENT_STATUS_PENDING
     */
    public const SYNC_ASSETS_CURRENT_STATUS_PENDING = 'pending';

    /**
     * Cron Current Status Running
     *
     * @const SYNC_ASSETS_CURRENT_STATUS_RUNNING
     */
    public const SYNC_ASSETS_CURRENT_STATUS_RUNNING = 'running';

    /**
     * Cron Current Status Failed
     *
     * @const SYNC_ASSETS_CURRENT_STATUS_FAILED
     */
    public const SYNC_ASSETS_CURRENT_STATUS_FAILED = 'failed';

    /**
     * Cron Current Status Partially failed
     *
     * @const SYNC_ASSETS_CURRENT_STATUS_PARTIALLY_FAILED
     */
    public const SYNC_ASSETS_CURRENT_STATUS_PARTIALLY_FAILED = 'partially_failed';

    /**
     * Cron current status Completed
     *
     * @const SYNC_ASSETS_CURRENT_STATUS_COMPLETED
     */
    public const SYNC_ASSETS_CURRENT_STATUS_COMPLETED = 'completed';

    /**
     * Cron Current Status Partially Completed
     *
     * @const SYNC_ASSETS_CURRENT_STATUS_PARTIALLY_COMPLETED
     */
    public const SYNC_ASSETS_CURRENT_STATUS_PARTIALLY_COMPLETED = 'partially_completed';

    /**
     * Cron current Status Rejected
     *
     * @const SYNC_ASSETS_CURRENT_STATUS_REJECTED
     */
    public const SYNC_ASSETS_CURRENT_STATUS_REJECTED = 'rejected';

    /**
     * Assets Status Completed
     *
     * @const ASSETS_STATUS_COMPLETED
     */
    public const ASSETS_STATUS_COMPLETED = 1;

    /**
     * Assets Status Failed
     *
     * @const ASSETS_STATUS_FAILED
     */
    public const ASSETS_STATUS_FAILED = 0;

    /**
     * Assets Status in Progress
     *
     * @const ASSETS_STATUS_IN_PROGRESS
     */
    public const ASSETS_STATUS_IN_PROGRESS = 2;

    /**
     * Entity Id
     *
     * @const ENTITY_ID
     */
    public const ENTITY_ID = 'entity_id';

    /**
     * Process Code
     *
     * @const PROCESS_CODE
     */
    public const PROCESS_CODE = 'process_code';

    /**
     * Process Name
     *
     * @const PROCESS_NAME
     */
    public const PROCESS_NAME = 'process_name';

    /**
     * Process Group
     *
     * @const PROCESS_GROUP
     */
    public const PROCESS_GROUP = 'process_group';

    /**
     * @const ASSET_TYPE
     */
    public const ASSET_TYPE = 'asset_type';

    /**
     * Start Time
     *
     * @const START_TIME
     */
    public const START_TIME = 'start_time';

    /**
     * End Time
     *
     * @const END_TIME
     */
    public const END_TIME = 'end_time';

    /**
     * @const PROCESS_CURRENT_STATUS
     */
    public const PROCESS_CURRENT_STATUS = 'process_current_status';

    /**
     * Total Time
     *
     * @const TOTAL_TIME
     */
    public const TOTAL_TIME = 'total_time';

    /**
     * @const REMOTE_TOTAL_RECORDS
     */
    public const REMOTE_TOTAL_RECORDS = 'remote_total_records';

    /**
     * Total Downlaod Records
     *
     * @const TOTAL_DOWNLOAD_RECORDS
     */
    public const TOTAL_DOWNLOAD_RECORDS = 'total_downloaded_records';

    /**
     * Sync Remarks
     *
     * @const SYNC_REMARKS
     */
    public const SYNC_REMARKS = 'sync_remarks';

    /**
     * Status
     *
     * @const STATUS
     */
    public const STATUS = 'status';

    /**
     * Last Sync Counter
     *
     * @const LAST_SYNC_COUNTER
     */
    public const LAST_SYNC_COUNTER = 'last_sync_counter';

    /**
     * Created At
     *
     * @const  CREATED_AT
     */
    public const CREATED_AT = 'created_at';

    /**
     * Last Sync Total Records
     *
     * @const LAST_SYNC_TOTAL_RECORDS
     */
    public const LAST_SYNC_TOTAL_RECORDS = 'last_sync_total_records';

    /**
     * Failed Total Records
     *
     * @const FAILED_COUNTER
     */
    public const FAILED_COUNTER = 'failed_counter';

    /**
     * Total Upload Customers
     *
     * @const TOTAL_UPLOAD_CUSTOMERS
     */
    public const TOTAL_UPLOAD_CUSTOMERS = 0;

    /**
     * Total Upload Items
     *
     * @const TOTAL_UPLOAD_ITEMS
     */
    public const TOTAL_UPLOAD_ITEMS = 0;

    /**
     * Total Upload Orders
     *
     * @const TOTAL_UPLOAD_ORDERS
     */
    public const TOTAL_UPLOAD_ORDERS = 0;

    /**
     * Set Entity Id
     *
     * @param mixed $entityId
     * @return SyncAssetsInterface
     */
    public function setEntityId($entityId): SyncAssetsInterface;

    /**
     * Get Entity id
     *
     * @return int
     */
    public function getEntityId(): int;

    /**
     * Get Process Code
     *
     * @return string
     */
    public function getProcessCode(): string;

    /**
     * Get Process Name
     *
     * @return string
     */
    public function getProcessName(): string;

    /**
     * Get Process Group
     *
     * @return string
     */
    public function getProcessGroup(): string;

    /**
     * Get Asset Type
     *
     * @return string
     */
    public function getAssetType(): string;

    /**
     * Get Start Time
     *
     * @return string
     */
    public function getStartTime(): string;

    /**
     * Get End Time
     *
     * @return string
     */
    public function getEndTime(): string;

    /**
     * Get Process Current Status
     *
     * @return string
     */
    public function getProcessCurrentStatus(): string;

    /**
     * Get Total Time
     *
     * @return string
     */
    public function getTotalTime(): string;

    /**
     * Get Remote Total Records
     *
     * @return int
     */
    public function getRemoteTotalRecords(): int;

    /**
     * Get Total Downlaoded Records
     *
     * @return int
     */
    public function getTotalDownloadedRecords(): int;

    /**
     * Get Sync Remarks
     *
     * @return string
     */
    public function getSyncRemarks(): string;

    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Get failed counter
     *
     * @return int|null
     */
    public function getFailedCounter(): ?int;

    /**
     * Get Last Sync Counter
     *
     * @return int
     */
    public function getLastSyncCounter(): int;

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Get Last Sync Total Records
     *
     * @return string
     */
    public function getLastSyncTotalRecords();

    /**
     * Set Process Code
     *
     * @param string $processCode
     * @return SyncAssetsInterface
     */
    public function setProcessCode(string $processCode): SyncAssetsInterface;

    /**
     * Set Process Name
     *
     * @param string $processName
     * @return SyncAssetsInterface
     */
    public function setProcessName(string $processName): SyncAssetsInterface;

    /**
     * Set Process Group
     *
     * @param string $processGroup
     * @return SyncAssetsInterface
     */
    public function setProcessGroup(string $processGroup): SyncAssetsInterface;

    /**
     * Set Assets Types
     *
     * @param string $assetType
     * @return SyncAssetsInterface
     */
    public function setAssetType(string $assetType): SyncAssetsInterface;

    /**
     * Set Start Time
     *
     * @param string $startTime
     * @return SyncAssetsInterface
     */
    public function setStartTime(string $startTime): SyncAssetsInterface;

    /**
     * Set End Time
     *
     * @param string $endTime
     * @return SyncAssetsInterface
     */
    public function setEndTime(string $endTime): SyncAssetsInterface;

    /**
     * Set Process Current Status
     *
     * @param string $processCurrentStatus
     * @return SyncAssetsInterface
     */
    public function setProcessCurrentStatus(string $processCurrentStatus): SyncAssetsInterface;

    /**
     * Set Total Time
     *
     * @param string $totalTime
     * @return SyncAssetsInterface
     */
    public function setTotalTime(string $totalTime): SyncAssetsInterface;

    /**
     * Set Remote Total Records
     *
     * @param int $remoteTotalRecords
     * @return SyncAssetsInterface
     */
    public function setRemoteTotalRecords(int $remoteTotalRecords): SyncAssetsInterface;

    /**
     * Set Total Downloaded Records
     *
     * @param int $totalDownloadedRecords
     * @return SyncAssetsInterface
     */
    public function setTotalDownloadedRecords(int $totalDownloadedRecords): SyncAssetsInterface;

    /**
     * Set Sync Remarks
     *
     * @param string $syncRemarks
     * @return SyncAssetsInterface
     */
    public function setSyncRemarks(string $syncRemarks): SyncAssetsInterface;

    /**
     * Set Status
     *
     * @param int $status
     * @return SyncAssetsInterface
     */
    public function setStatus(int $status): SyncAssetsInterface;

    /**
     * Set failed counter
     *
     * @param int $counter
     * @return SyncAssetsInterface
     */
    public function setFailedCounter(int $counter): SyncAssetsInterface;

    /**
     * Set Last Sync Counter
     *
     * @param int $lastSyncCounter
     * @return SyncAssetsInterface
     */
    public function setLastSyncCounter(int $lastSyncCounter): SyncAssetsInterface;

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return SyncAssetsInterface
     */
    public function setCreatedAt(string $createdAt): SyncAssetsInterface;

    /**
     * Set Last Sync total Records
     *
     * @param string $lastSyncTotalRecords
     * @return SyncAssetsInterface
     */
    public function setLastSyncTotalRecords(string $lastSyncTotalRecords): SyncAssetsInterface;
}
