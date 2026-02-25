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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Downloads;

use Ebizcharge\Ebizcharge\Api\Data\SyncAssetsInterface;
use Ebizcharge\Ebizcharge\Api\SyncAssetsRepositoryInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ShellCommand;
use Ebizcharge\Ebizcharge\Model\SyncAssets;
use Ebizcharge\Ebizcharge\Model\SyncAssetsFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Download Latest Resources action class
 *
 * Class DownloadLatestResources
 */
class DownloadLatestResources extends Action
{
    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var ShellCommand
     */
    protected ShellCommand $_shellCommand;

    /**
     * @var SyncAssetsFactory
     */
    protected SyncAssetsFactory $_syncAssetsFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $_jsonFactory;

    /**
     * @var SyncAssetsRepositoryInterface
     */
    private SyncAssetsRepositoryInterface $syncAssetsRepository;

    /**
     * DownloadLatestResources constructor.
     *
     * @param Context $context
     * @param EbizchargeLogger $ebizchargeLogger
     * @param ShellCommand $shellCommand
     * @param JsonFactory $jsonFactory
     * @param SyncAssetsRepositoryInterface $syncAssetsRepository
     */
    public function __construct(
        Context $context,
        EbizchargeLogger $ebizchargeLogger,
        ShellCommand $shellCommand,
        JsonFactory $jsonFactory,
        SyncAssetsRepositoryInterface $syncAssetsRepository
    ) {
        parent::__construct($context);
        $this->_shellCommand = $shellCommand;
        $this->_ebizchargeLogger = $ebizchargeLogger;
        $this->_jsonFactory = $jsonFactory;
        $this->syncAssetsRepository = $syncAssetsRepository;
    }

    /**
     * Execute Methods
     *
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $response = [
            'message' => __("Error occurred during running process "),
            'error' => true,
            'success' => false
        ];
        try {

            $downloadResourceShellCommand = null;
            $processCode = null;

            /** @var  $downloadAction */
            $downloadAction = $this->getRequest()->getParam('download_request');
            $this->_ebizchargeLogger->addInfo(__("Current download process action is ".$downloadAction));

            $response = [];

            /** Download Customers Command  */
            if ($downloadAction === 'download_customers') {
                $downloadResourceShellCommand = ShellCommand::SHELL_COMMAND_PROCESS_DOWNLOAD_CUSTOMERS;
                $this->_ebizchargeLogger->addInfo(__("Shell command (".$downloadResourceShellCommand.
                    ") started to download customers. "));
                $processCode = SyncAssets::SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS;
            }

            /** Download Orders Command  */
            if ($downloadAction === 'download_orders') {
                $downloadResourceShellCommand = ShellCommand::SHELL_COMMAND_PROCESS_DOWNLOAD_ORDERS;
                $this->_ebizchargeLogger->addInfo(__("Shell process command (".$downloadResourceShellCommand.
                    ") started to download orders. "));
                $processCode = SyncAssets::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS;
            }

            /** Download Products Command  */
            if ($downloadAction === 'download_products') {
                $downloadResourceShellCommand = ShellCommand::SHELL_COMMAND_PROCESS_DOWNLOAD_PRODUCTS;
                $this->_ebizchargeLogger->addInfo(__("Shell process command (".$downloadResourceShellCommand.
                    ") started to download products. "));
                $processCode = SyncAssets::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS;
            }

            /** @var  $processStarted */
            $processStarted = $this->_shellCommand->run($downloadResourceShellCommand);

            /** process ran successfully */
            if ($processStarted) {
                // phpcs:ignore
                // $this->setProcessInProgress($processCode);

                $response = [
                    'message' => __("Success process started successfully"),
                    'error' => false,
                    'success' => true,
                ];
            }

        } catch (\Exception $exception) {

            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during running process Error:" . $exception->getMessage()
            ));
            $response = [
                'message' => __("Error occurred during running process Error: " . $exception->getMessage()),
                'error' => true,
                'success' => false
            ];

            $syncMessage = "Exception occurred during checking the customers " . $exception->getMessage();
            $this->_ebizchargeLogger->addCritical(__($syncMessage));
            $params = [
                'status' => SyncAssetsInterface::ASSETS_STATUS_FAILED,
                'remote_total_records' => 0,
                'sync_remarks' => $syncMessage,
                'last_sync_counter' => 0,
                'total_downloaded_records' => 0
            ];
            /** saving sync customers values */
            $this->_syncAssetsFactory->create()->saveSyncCronValues($params);
        }

        /** @var $jsonFactory */
        $jsonFactory = $this->_jsonFactory->create();

        /** creating JSON Factory */
        return $jsonFactory->setData(json_encode($response));
    }

    /**
     * Set Process In Progress
     *
     * @param mixed $processCode
     */
    private function setProcessInProgress($processCode)
    {
        /**
         * @var $syncRecord SyncAssetsInterface
         */
        $syncRecord = $this->syncAssetsRepository->getById($processCode, SyncAssetsInterface::PROCESS_CODE);
        $syncRecord->setStatus(SyncAssets::ASSETS_STATUS_IN_PROGRESS);
        $syncRecord->setProcessCurrentStatus((string) SyncAssets::ASSETS_STATUS_IN_PROGRESS);
        $this->syncAssetsRepository->save($syncRecord);
    }
}
