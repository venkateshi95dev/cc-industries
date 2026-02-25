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

namespace Ebizcharge\Ebizcharge\Setup\Patch\Data;

use Ebizcharge\Ebizcharge\Api\Data\SoapApiModelInterface;
use Ebizcharge\Ebizcharge\Api\Data\SyncAssetsInterface;
use Ebizcharge\Ebizcharge\Model\SyncAssets;
use Ebizcharge\Ebizcharge\Model\SyncAssetsFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Add Data Setup Patch class
 *
 * Class AddData
 */
class UpgradeSchemaPatch implements DataPatchInterface
{

    /**
     * Sync Assets Data Factory
     *
     * @var SyncAssetsFactory
     */
    protected SyncAssetsFactory $syncAssetsFactory;

    /**
     * @var ResourceInterface
     */
    protected ResourceInterface $moduleResource;

    /**
     * @var State
     */
    protected State $appState;

    /**
     * @param ResourceInterface $moduleResource
     * @param SyncAssetsFactory $syncAssetsFactory
     * @param State $appState
     */
    public function __construct(
        ResourceInterface $moduleResource,
        SyncAssetsFactory $syncAssetsFactory,
        State             $appState
    ) {
        /** @var $syncAssetsFactory */
        $this->syncAssetsFactory = $syncAssetsFactory;
        /** @var $moduleResource */
        $this->moduleResource = $moduleResource;
        /** @var $appState */
        $this->appState = $appState;
    }

    /**
     * Apply method
     *
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        $currentDateTime = null;
        $ebizSetupVersion = $this->moduleResource->getDataVersion("Ebizcharge_Ebizcharge");
       // var_dump($ebizSetupVersion);exit;
        /**
         * Checking if the EBizCharge version already installed and check version if exists
         */
        if (!$ebizSetupVersion ||
            version_compare(
                $ebizSetupVersion,
                SoapApiModelInterface::EBIZCHARGE_SETUP_VERSION,
                '>'
            )) {
            $this->appState->emulateAreaCode(
                Area::AREA_GLOBAL,
                [
                    $this, "addDefaultSyncAssetsRows"
                ]
            );
        }
    }

    /**
     * Add Default Sync Assets Rows
     *
     * @return void
     * @throws \Exception
     */
    public function addDefaultSyncAssetsRows(): void
    {
        /**
         * Default Data Rows
         */
        /** @var  $defaultDataRows */
        $defaultDataRows = [
            [
                SyncAssetsInterface::PROCESS_CODE => SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS,
                SyncAssetsInterface::PROCESS_NAME => SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS,
                SyncAssetsInterface::PROCESS_GROUP => SyncAssetsInterface::SYNC_ASSETS_CRON_DOWNLOAD_GROUP,
                SyncAssetsInterface::ASSET_TYPE => SyncAssetsInterface::SYNC_ASSETS_TYPE_DOWNLOAD,
                SyncAssetsInterface::START_TIME => "",
                SyncAssetsInterface::END_TIME => "",
                SyncAssetsInterface::PROCESS_CURRENT_STATUS => 0,
                SyncAssetsInterface::TOTAL_TIME => 0,
                SyncAssetsInterface::REMOTE_TOTAL_RECORDS => 0,
                SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => 0,
                SyncAssetsInterface::SYNC_REMARKS => __("Customers download syncing is pending"),
                SyncAssetsInterface::LAST_SYNC_COUNTER => 0,
                SyncAssetsInterface::LAST_SYNC_TOTAL_RECORDS => 0,
                SyncAssetsInterface::STATUS => 0,
            ],
            [
                SyncAssetsInterface::PROCESS_CODE => SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS,
                SyncAssetsInterface::PROCESS_NAME => SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS,
                SyncAssetsInterface::PROCESS_GROUP => SyncAssetsInterface::SYNC_ASSETS_CRON_DOWNLOAD_GROUP,
                SyncAssetsInterface::ASSET_TYPE => SyncAssetsInterface::SYNC_ASSETS_TYPE_DOWNLOAD,
                SyncAssetsInterface::START_TIME => "",
                SyncAssetsInterface::END_TIME => "",
                SyncAssetsInterface::PROCESS_CURRENT_STATUS => 0,
                SyncAssetsInterface::TOTAL_TIME => 0,
                SyncAssetsInterface::REMOTE_TOTAL_RECORDS => 0,
                SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => 0,
                SyncAssetsInterface::SYNC_REMARKS => __("Items download syncing is pending"),
                SyncAssetsInterface::LAST_SYNC_COUNTER => 0,
                SyncAssetsInterface::LAST_SYNC_TOTAL_RECORDS => 0,
                SyncAssetsInterface::STATUS => 0,
            ],
            [
                SyncAssetsInterface::PROCESS_CODE => SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS,
                SyncAssetsInterface::PROCESS_NAME => SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS,
                SyncAssetsInterface::PROCESS_GROUP => SyncAssetsInterface::SYNC_ASSETS_CRON_DOWNLOAD_GROUP,
                SyncAssetsInterface::ASSET_TYPE => SyncAssetsInterface::SYNC_ASSETS_TYPE_DOWNLOAD,
                SyncAssetsInterface::START_TIME => "",
                SyncAssetsInterface::END_TIME => "",
                SyncAssetsInterface::PROCESS_CURRENT_STATUS => 0,
                SyncAssetsInterface::TOTAL_TIME => 0,
                SyncAssetsInterface::REMOTE_TOTAL_RECORDS => 0,
                SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => 0,
                SyncAssetsInterface::SYNC_REMARKS => __("Orders download syncing is pending"),
                SyncAssetsInterface::LAST_SYNC_COUNTER => 0,
                SyncAssetsInterface::LAST_SYNC_TOTAL_RECORDS => 0,
                SyncAssetsInterface::STATUS => 0,
            ],
            [
                SyncAssetsInterface::PROCESS_CODE => SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_CUSTOMERS,
                SyncAssetsInterface::PROCESS_NAME => SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_CUSTOMERS,
                SyncAssetsInterface::PROCESS_GROUP => SyncAssetsInterface::SYNC_ASSETS_CRON_UPLOAD_GROUP,
                SyncAssetsInterface::ASSET_TYPE => SyncAssetsInterface::SYNC_ASSETS_TYPE_UPLOAD,
                SyncAssetsInterface::START_TIME => "",
                SyncAssetsInterface::END_TIME => "",
                SyncAssetsInterface::PROCESS_CURRENT_STATUS => 0,
                SyncAssetsInterface::TOTAL_TIME => 0,
                SyncAssetsInterface::REMOTE_TOTAL_RECORDS => 0,
                SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => 0,
                SyncAssetsInterface::SYNC_REMARKS => __("Customer upload syncing is pending"),
                SyncAssetsInterface::LAST_SYNC_COUNTER => 0,
                SyncAssetsInterface::LAST_SYNC_TOTAL_RECORDS => 0,
                SyncAssetsInterface::STATUS => 0,
            ],
            [
                SyncAssetsInterface::PROCESS_CODE => SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_ITEMS,
                SyncAssetsInterface::PROCESS_NAME => SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_ITEMS,
                SyncAssetsInterface::PROCESS_GROUP => SyncAssetsInterface::SYNC_ASSETS_CRON_UPLOAD_GROUP,
                SyncAssetsInterface::ASSET_TYPE => SyncAssetsInterface::SYNC_ASSETS_TYPE_UPLOAD,
                SyncAssetsInterface::START_TIME => "",
                SyncAssetsInterface::END_TIME => "",
                SyncAssetsInterface::PROCESS_CURRENT_STATUS => 0,
                SyncAssetsInterface::TOTAL_TIME => 0,
                SyncAssetsInterface::REMOTE_TOTAL_RECORDS => 0,
                SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => 0,
                SyncAssetsInterface::SYNC_REMARKS => __("Items upload syncing is pending"),
                SyncAssetsInterface::LAST_SYNC_COUNTER => 0,
                SyncAssetsInterface::LAST_SYNC_TOTAL_RECORDS => 0,
                SyncAssetsInterface::STATUS => 0,
            ],
            [
                SyncAssetsInterface::PROCESS_CODE => SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_ORDERS,
                SyncAssetsInterface::PROCESS_NAME => SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_ORDERS,
                SyncAssetsInterface::PROCESS_GROUP => SyncAssetsInterface::SYNC_ASSETS_CRON_UPLOAD_GROUP,
                SyncAssetsInterface::ASSET_TYPE => SyncAssetsInterface::SYNC_ASSETS_TYPE_UPLOAD,
                SyncAssetsInterface::START_TIME => "",
                SyncAssetsInterface::END_TIME => "",
                SyncAssetsInterface::PROCESS_CURRENT_STATUS => 0,
                SyncAssetsInterface::TOTAL_TIME => 0,
                SyncAssetsInterface::REMOTE_TOTAL_RECORDS => 0,
                SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => 0,
                SyncAssetsInterface::SYNC_REMARKS => __("Orders upload syncing is pending"),
                SyncAssetsInterface::LAST_SYNC_COUNTER => 0,
                SyncAssetsInterface::LAST_SYNC_TOTAL_RECORDS => 0,
                SyncAssetsInterface::STATUS => 0,
            ]
        ];

        /**
         * Looping through the rows and add data to the
         */
        /** @var  $dataRow */
        foreach ($defaultDataRows as $dataRow) {
            /** adding Data Row to database */
            $this->syncAssetsFactory->create()->setData($dataRow)->save();
        }
    }

    /**
     * Get Dependencies
     *
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get Aliases
     *
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
