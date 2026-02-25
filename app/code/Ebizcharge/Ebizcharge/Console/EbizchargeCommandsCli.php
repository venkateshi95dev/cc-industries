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

namespace Ebizcharge\Ebizcharge\Console;

use Ebizcharge\Ebizcharge\Api\Data\CommandsCliModelInterface;
use Ebizcharge\Ebizcharge\Api\Data\SoapApiModelInterface;
use Ebizcharge\Ebizcharge\Api\Data\SyncAssetsInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config as EbizConfig;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\OrderFactory;
use Ebizcharge\Ebizcharge\Model\PaymentHistory;
use Ebizcharge\Ebizcharge\Model\ProductFactory;
use Ebizcharge\Ebizcharge\Model\ShellCommand;
use Ebizcharge\Ebizcharge\Model\SyncAssetsFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cron;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Crontab\CrontabManagerInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\ProgressBarFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * EBizCharge Commands Cli for Console
 *
 * Class EbizchargeCommandsCli
 */
class EbizchargeCommandsCli extends Command implements CommandsCliModelInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $_scopeConfig;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var TranApi
     */
    protected TranApi $_ebizchargeSOAPApi;

    /**
     * @var State
     */
    protected State $_appState;

    /**
     * @var MetadataPool
     */
    protected MetadataPool $_metadataPool;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManager;

    /**
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $_objectManager;

    /**
     * @var CrontabManagerInterface
     */
    protected CrontabManagerInterface $_crontabManager;

    /**
     * @var Cron
     */
    protected Cron $_cronModel;

    /**
     * @var ShellCommand
     */
    protected ShellCommand $_shellCommand;

    /**
     * @var ProgressBarFactory
     */
    protected ProgressBarFactory $_progressBarFactory;

    /**
     * @var ProgressBar
     */
    protected ProgressBar $_progressBarHelper;

    /**
     * @var SyncAssetsFactory
     */
    protected SyncAssetsFactory $_syncAssetsFactory;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productFactory;

    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;

    /**
     * @var PaymentHistory
     */
    protected PaymentHistory $_paymentHistory;

    /**
     * @var Filesystem
     */
    protected Filesystem $_fileSystem;
    /**
     * @var InputInterface
     */
    protected InputInterface $input;
    /**
     * @var OutputInterface
     */
    protected OutputInterface $output;
    /**
     * @var EbizConfig
     */
    protected EbizConfig $_ebizConfigModel;
    /**
     * @var Csv
     */
    private Csv $csvProcessor;
    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $sessionManagerInterface;

    /**
     * @param EbizchargeLogger $ebizchargeLogger
     * @param TranApi $ebizchargeSOAPApi
     * @param State $appState
     * @param MetadataPool $metadataPool
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectmanager
     * @param CrontabManagerInterface $crontabManager
     * @param ProductFactory $productFactory
     * @param OrderFactory $orderFactory
     * @param Cron $cronModel
     * @param ShellCommand $shellCommand
     * @param PaymentHistory $paymentHistory
     * @param ProgressBarFactory $progressBarFactory
     * @param SyncAssetsFactory $syncAssetsFactory
     * @param Filesystem $filesystem
     * @param SessionManagerInterface $sessionManagerInterface
     * @param Csv $csvProcessor
     * @param EbizConfig $ebizConfigModel
     */
    public function __construct(
        EbizchargeLogger        $ebizchargeLogger,
        TranApi                 $ebizchargeSOAPApi,
        State                   $appState,
        MetadataPool            $metadataPool,
        ScopeConfigInterface    $scopeConfig,
        CustomerFactory         $customerFactory,
        StoreManagerInterface   $storeManager,
        ObjectManagerInterface  $objectmanager,
        CrontabManagerInterface $crontabManager,
        ProductFactory          $productFactory,
        OrderFactory            $orderFactory,
        Cron                    $cronModel,
        ShellCommand            $shellCommand,
        PaymentHistory          $paymentHistory,
        ProgressBarFactory      $progressBarFactory,
        SyncAssetsFactory       $syncAssetsFactory,
        Filesystem              $filesystem,
        SessionManagerInterface $sessionManagerInterface,
        Csv                     $csvProcessor,
        EbizConfig              $ebizConfigModel
    )
    {
        parent::__construct();

        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _appState */
        $this->_appState = $appState;
        /** @var _metadataPool */
        $this->_metadataPool = $metadataPool;
        /** @var _scopeConfig */
        $this->_scopeConfig = $scopeConfig;
        /** @var _ebizchargeSOAPApi */
        $this->_ebizchargeSOAPApi = $ebizchargeSOAPApi;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var  _storeManager */
        $this->_storeManager = $storeManager;
        /** @var  _objectManager */
        $this->_objectManager = $objectmanager;
        /** @var  _crontabManager */
        $this->_crontabManager = $crontabManager;
        /** @var  _cronModel */
        $this->_cronModel = $cronModel;
        /** @var  _shellCommand */
        $this->_shellCommand = $shellCommand;
        /** @var _progressBarHelper */
        //  $this->_progressBarHelper = $progressBarHelper;
        /** @var _progressBarFactory */
        $this->_progressBarFactory = $progressBarFactory;
        /** @var _syncAssetsFactory */
        $this->_syncAssetsFactory = $syncAssetsFactory;
        /** @var _productFactory */
        $this->_productFactory = $productFactory;
        /** @var _orderFactory */
        $this->_orderFactory = $orderFactory;
        /** @var _paymentHistory */
        $this->_paymentHistory = $paymentHistory;
        $this->csvProcessor = $csvProcessor;
        /** @var  _fileSystem */
        $this->_fileSystem = $filesystem;
        /** @var  _ebizConfigModel */
        $this->_ebizConfigModel = $ebizConfigModel;
        /** @var  sessionManagerInterface */
        $this->sessionManagerInterface = $sessionManagerInterface;
    }

    /**
     * Execute method
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     * @phpcs:disable
     */
    final public function execute(InputInterface $input, OutputInterface $output): int
    {
        // phpcs:enable
        $this->input = $input;
        $this->output = $output;
        /** decorating the output */
        $output->setDecorated(true);
        $this->_appState->emulateAreaCode(
            Area::AREA_GLOBAL,
            [
                $this,
                "processCliCommands"
            ]
        );
        return 1;
    }

    /**
     * Process Cli Commands
     *
     * @return int
     * @throws Exception
     */
    public function processCliCommands(): int
    {
        $input = $this->input;
        $output = $this->output;

        /** @var  $isCommandCompletedFlag */
        $isCommandCompletedFlag = 0;

        /** @var  $currentCommand */
        $currentCommand = $input->getOption(CommandsCliModelInterface::INPUT_ARGUMENT_NAME);
        $processCompleted = 1;

        /**
         * switch command
         * @phpcs:disable
         */
        switch ($currentCommand) {
            case CommandsCliModelInterface::COMMAND_CONFIG:
                $isCommandCompletedFlag = $this->getValueOfConfig($currentCommand, $input, $output);
                break;
            case CommandsCliModelInterface::COMMAND_DOWNLOAD_CUSTOMERS:
                $isCommandCompletedFlag = $this->downloadCustomers($currentCommand, $input, $output);
                break;
            case CommandsCliModelInterface::COMMAND_DOWNLOAD_ITEMS:
                $isCommandCompletedFlag = $this->downloadItems($currentCommand, $input, $output);
                break;
            case CommandsCliModelInterface::COMMAND_DOWNLOAD_ORDERS:
                $isCommandCompletedFlag = $this->downloadOrders($currentCommand, $input, $output);
                break;
            case CommandsCliModelInterface::COMMAND_UPLOAD_CUSTOMERS:
                $isCommandCompletedFlag = $this->uploadCustomers($currentCommand, $input, $output);
                break;
            case CommandsCliModelInterface::COMMAND_UPLOAD_ITEMS:
                $isCommandCompletedFlag = $this->uploadItems($currentCommand, $input, $output);
                break;
            case CommandsCliModelInterface::COMMAND_UPLOAD_ORDERS:
                $isCommandCompletedFlag = $this->uploadOrders($currentCommand, $input, $output);
                break;
            case CommandsCliModelInterface::COMMAND_RE_ORDER_VIA_CRON:
                $isCommandCompletedFlag = $this->reOrderViaCli($currentCommand, $input, $output);
                break;
            case CommandsCliModelInterface::COMMAND_LOW_STOCK_NOTIFICATIONSL_VIA_CRON:
                $isCommandCompletedFlag = $this->sendLowStockNotifications($currentCommand, $input, $output);
                break;
            case CommandsCliModelInterface::COMMAND_MANUAL_RUN_RECURRING_ORDERS_CRON:
                $isCommandCompletedFlag = $this->manuallyRunCreateRecurringOrdersCron($currentCommand, $input, $output);
                break;
            case CommandsCliModelInterface::COMMAND_MANUAL_DOWNLOAD_PAYMENTS_CRON:
                $isCommandCompletedFlag = $this->manuallyDownloadPaymentsCron($currentCommand, $input, $output);
                break;
            case CommandsCliModelInterface::COMMAND_MANUAL_SYNC_ITEMS_STOCK_CRON:
                $isCommandCompletedFlag = $this->manuallySyncItemsStockCron($currentCommand, $input, $output);
                break;
            case CommandsCliModelInterface::COMMAND_UPDATE_CUSTOMER_AT_EBIZCHARGE:
                $isCommandCompletedFlag = $this->manuallyUpdateCustomer($currentCommand, $input, $output);
                break;
            default:
                $this->writeLine('<error>Please provide a valid input e.g "' .
                    CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' ' .
                    CommandsCliModelInterface::EBIZCHARGE_COMMAND . ' config"</error>');
                $isCommandCompletedFlag = 1;

        }
        // phpcs:enable
        return (int)$isCommandCompletedFlag;
    }

    /**
     * Output for checking and testing the configuration values
     *
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getValueOfConfig(string $command, InputInterface $input, OutputInterface $output): bool
    {
        $isCommandCompleted = false;
        $customersAtEbizcharge = $this->getCustomersAtEbizcharge();
        $this->writeLine("Getting customers from EBizCharge Gateway API");
        $totalCustomers = count($customersAtEbizcharge);
        $this->writeLine("(" . $totalCustomers . ")");
        return $isCommandCompleted = true;
    }

    /**
     * Get Customers At EBizCharge
     *
     * @throws NoSuchEntityException
     */
    public function getCustomersAtEbizcharge()
    {
        $store = $this->_storeManager->getStore();
        //$cronManager = $this->_crontabManager->getTasks();
        $shellCommand = $this->_shellCommand->run();
        //  $customerFactory = $this->_customerFactory->create()->syncCustomersFromEbizcharge();

        //return $ebizchargeCustomers;
    }

    /**
     * Write Line
     *
     * @param string $message
     * @return void
     */
    public function writeLine(string $message = ""): void
    {
        $this->output->writeln($message);
    }

    /**
     * Download Customers
     *
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function downloadCustomers(string $command, InputInterface $input, OutputInterface $output): bool
    {
        $isCommandCompleted = false;

        /** if Command is download Customers  */
        if ($command === CommandsCliModelInterface::COMMAND_DOWNLOAD_CUSTOMERS) {
            $message = "Shell process started to check total remote customers from EBizCharge Hub.";
            $this->writeLine($message);
            $this->_ebizchargeLogger->addInfo($message);
            $message = "Fetching please wait---------------------------------------------------";
            $this->writeLine($message);
            /** checking customers */
            /** @var $syncAssetsFactory */
            $syncAssetsFactory = $this->_syncAssetsFactory->create()
                ->loadByProcessCode(SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS);
            $startTime = $syncAssetsFactory->getCurrentDateTime();

            /** @var $ebizchargeCustomers */
            $ebizchargeCustomers = $this->_customerFactory->create()->getCustomersAtEbizcharge();
            $totalRemoteCustomers = $ebizchargeCustomers ? count($ebizchargeCustomers) : 0;
            $message = "Total (" . $totalRemoteCustomers . ") customers found to download from EBizCharge Hub.";

            /** checking customers */
            $this->writeLine($message);
            $this->_ebizchargeLogger->addInfo($message);
            /** @var  $params */
            $params = [
                SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                SyncAssetsInterface::REMOTE_TOTAL_RECORDS => $totalRemoteCustomers,
                SyncAssetsInterface::START_TIME => $startTime,
                SyncAssetsInterface::CREATED_AT => $startTime,
                SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => 0,
                SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                SyncAssetsInterface::SYNC_REMARKS => __(
                    "Sync of Customers Started from EBizCharge Gateway to local system"
                ),
                SyncAssetsInterface::TOTAL_TIME => '0d 0h 0m 0s',
                SyncAssetsInterface::FAILED_COUNTER => '0',
            ];
            $syncAssetsFactory->saveSyncCronValues($params);

            if ($totalRemoteCustomers === 0) {
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Sync of total 0 Customers Completed from EBizCharge Hub to Local DB"
                    )
                ];
                $syncAssetsFactory->saveSyncCronValues(
                    $params,
                    SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS
                );
                $this->_ebizchargeLogger->addInfo(__(
                    "Total customers found from EBizCharge Hub (" . $totalRemoteCustomers .
                    ") so process got exit."
                ));
                $message = 'New (' . $totalRemoteCustomers . ') customers downloaded and shell process completed.';
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo(__($message));
                $isCommandCompleted = true;
            }

            /** if found customers at EBizCharge */
            if ($ebizchargeCustomers && count($ebizchargeCustomers) > 0) {
                $message = "Downloading (" . $totalRemoteCustomers . ") customers to the local DB from EBizCharge Hub.";
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo(__($message));

                /** saving customers to local Magento Database */
                $this->renderDownloadCustomersIntegrationProgressBar($output, $ebizchargeCustomers);
                $message = "Total (" . $totalRemoteCustomers .
                    ") customers has been downloaded and has been saved to local DB.";
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo(__($message));

                /** saving sync customers cron process */
                $endTime = $syncAssetsFactory->getCurrentDateTime();
                $dateDiff = $syncAssetsFactory->getDateTimeDiff($startTime, $endTime);

                /** @var  $params */
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::END_TIME => $endTime,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::TOTAL_TIME => $dateDiff->h . 'h ' . $dateDiff->i . 'm ' . $dateDiff->s . 's',
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Syncing of total (" . $totalRemoteCustomers .
                        ") Customers downloaded from EBizCharge Hub to Local DB."
                    )
                ];
                $syncAssetsFactory->saveSyncCronValues(
                    $params,
                    SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS
                );
                $message = 'Process downloading customers has been completed and total (' .
                    $totalRemoteCustomers . ') customers downloaded.';
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo(__($message));
                $isCommandCompleted = true;
            }
        } else {
            // phpcs:ignore
            $message = "Your provided Cli-Command is not correct. So please provide a valid Cli-Command to download customers: e.g php bin/magento ebizcharge:command:cli --command download-customers";
            $this->writeLine($message);
            $this->_ebizchargeLogger->addCritical(__($message));
            $isCommandCompleted = true;
        }
        return $isCommandCompleted;
    }

    /**
     * Render Download Customers Integration Progress Bar
     *
     * @param OutputInterface $output
     * @param array $customers
     * @return void
     * @throws FileSystemException
     */
    public function renderDownloadCustomersIntegrationProgressBar(OutputInterface $output, array $customers = [])
    {
        /** @var ProgressBar $progress */
        $progressBar = $this->_progressBarFactory->create(
            [
                'output' => $output,
                'max' => count($customers),
            ]
        );
        $progressBar->setFormat(
            '%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%'
        );
        $progressBar->start();
        $downloadCounter = 0;
        $failedCounter = 0;

        $syncAssetsFactory = $this->_syncAssetsFactory->create();

        if (count($customers) > 0) {
            foreach ($customers as $ebizchargeCustomer) {
                try {
                    /** @var  $ebizchargeCustomer */
                    $ebizchargeCustomer = (array)$ebizchargeCustomer;

                    /** Saving EBizCharge Customer to the internal customer */
                    $downloadCounter++;
                    /** @var $customerEbizInternalId */
                    $customerEbizInternalId = $ebizchargeCustomer["CustomerInternalId"] ?? "";
                    $customerFactory = $this->_customerFactory->create();
                    $isDownloaded = true;

                    /** @var $customerModel */
                    $customerRes = $customerFactory->saveEbizchargeCustomerToLocal($ebizchargeCustomer, $isDownloaded);

                    if (!$customerRes) {
                        $failedCounter++;
                    }
                    $customerMessage[] = [
                        date('Y-m-d H:i:s'),
                        "Saved customer with customerID =" . $customerEbizInternalId
                    ];

                    // phpcs:ignore
                    if (is_file(CommandsCliModelInterface::DOWNLOAD_CUSTOMER_ERROR_LOG_FILE)) {
                        $this->csvProcessor->appendData(CommandsCliModelInterface::DOWNLOAD_CUSTOMER_ERROR_LOG_FILE, $customerMessage);
                    } else {
                        $this->csvProcessor->saveData(CommandsCliModelInterface::DOWNLOAD_CUSTOMER_ERROR_LOG_FILE, $customerMessage);
                    }

                    $this->_ebizchargeLogger->addInfo(
                        "Saved and downloaded the customer with customer_id=" . $customerEbizInternalId
                    );

                } catch (Exception $e) {
                    $failedCounter++;
                    $errorMessage[] = [date('Y-m-d H:i:s'), $e->getMessage()];
                    $this->csvProcessor->appendData(CommandsCliModelInterface::DOWNLOAD_CUSTOMER_ERROR_LOG_FILE, $errorMessage);

                    $this->_ebizchargeLogger->addInfo(
                        'Exception: occurred during downloading Customer  Error: ' . $e->getMessage()
                    );

                    $message = "Error occurred during saving customer. Exception: " . $e->getMessage();
                    $this->writeLine($message);
                    $this->_ebizchargeLogger->addCritical(__($message));
                }

                /** @var  $params */
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                    SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => $downloadCounter,
                    SyncAssetsInterface::LAST_SYNC_TOTAL_RECORDS => $downloadCounter,
                    SyncAssetsInterface::LAST_SYNC_COUNTER => $downloadCounter,
                    SyncAssetsInterface::FAILED_COUNTER => $failedCounter,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Syncing Customers from EBizCharge to Local System is in progress"
                    )
                ];
                $syncAssetsFactory->saveSyncCronValues(
                    $params,
                    SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_CUSTOMERS
                );
                $progressBar->advance();
            }
        } else {
            $message = "No customer downloaded to local DB from EBizCharge Hub.";
            $this->writeLine($message);
            $this->_ebizchargeLogger->addCritical(__($message));
            // phpcs:ignore
            // print_r("No customers found to save to Magento.");
        }

        $progressBar->finish();
        $output->write(PHP_EOL);
    }

    /**
     * Download Items
     *
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function downloadItems(string $command, InputInterface $input, OutputInterface $output): bool
    {
        $isCommandCompleted = false;

        if ($command === CommandsCliModelInterface::COMMAND_DOWNLOAD_ITEMS) {
            $message = "Shell process started to import latest items from EBizCharge Hub.";
            $this->writeLine($message);
            $this->_ebizchargeLogger->addInfo(__($message));
            $message = "Fetching please wait---------------------------------------------------";
            $this->writeLine($message);

            /** @var $syncAssetsFactory */
            $syncAssetsFactory = $this->_syncAssetsFactory->create();
            $startTime = $syncAssetsFactory->getCurrentDateTime();
            $ebizchargeItems = $this->_syncAssetsFactory->create()->getLatestEbizchargeItems();
            $totalRemoteItems = count($ebizchargeItems);

            if ($totalRemoteItems === 0) {
                /** @var  $params */
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::FAILED_COUNTER => 0,
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Sync of items/products from EBizCharge to Local Database has been completed"
                    )
                ];
                $syncAssetsFactory->saveSyncCronValues(
                    $params,
                    SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS
                );
                $message = "Total new (" . $totalRemoteItems . ") items found at EBizCharge Hub. So process got exit.";
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo(__($message));

                return false;
            }

            /** saving values */
            $syncAssetsFactory->saveSyncCronValues(
                [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                    SyncAssetsInterface::REMOTE_TOTAL_RECORDS => $totalRemoteItems,
                    SyncAssetsInterface::START_TIME => $startTime,
                    SyncAssetsInterface::CREATED_AT => $startTime,
                    SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => 0,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                    SyncAssetsInterface::FAILED_COUNTER => 0,
                    SyncAssetsInterface::TOTAL_TIME => '0d 0h 0m 0s',
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Syncing of Items has been started from EBizCharge to Local System"
                    )
                ],
                SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS
            );

            /** if found items at EBizCharge */
            if ($totalRemoteItems > 0) {
                $message = "Process started to download new (" . $totalRemoteItems . ") items from EBizCharge Hub.";
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo(__($message));

                /** rendering Download Items Integration Progress Bar */
                $this->renderDownloadItemsIntegrationProgressBar($output, $ebizchargeItems);

                $message = "Shell download process has completed and total new (" . $totalRemoteItems .
                    ") items saved to local DB.";
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo(__($message));

                /** saving sync customers cron process */
                $endTime = $syncAssetsFactory->getCurrentDateTime();
                $dateDiff = $syncAssetsFactory->getDateTimeDiff($startTime, $endTime);

                /** @var  $params */
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::END_TIME => $endTime,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::TOTAL_TIME => $dateDiff->h . 'h ' . $dateDiff->i . 'm ' . $dateDiff->s . 's',
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Sync of items/products from EBizCharge to Local Database has been completed"
                    )
                ];
                $syncAssetsFactory->saveSyncCronValues(
                    $params,
                    SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS
                );
            }
            $isCommandCompleted = true;

        } else {
            // phpcs:ignore
            $message = "Your provided  Cli-Command is not correct, please provide valid Cli-Command for downloading Items | Products: e.g php bin/magento " . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . " " . CommandsCliModelInterface::EBIZCHARGE_COMMAND . " " . CommandsCliModelInterface::COMMAND_DOWNLOAD_CUSTOMERS;
            $this->writeLine($message);
            $this->_ebizchargeLogger->addCritical(__($message));
            $isCommandCompleted = false;

        }
        return $isCommandCompleted;
    }

    /**
     * Render Customers Progress Bar
     *
     * @param OutputInterface $output
     * @param mixed $items
     * @throws FileSystemException
     */
    public function renderDownloadItemsIntegrationProgressBar(OutputInterface $output, $items)
    {
        /** @var ProgressBar $progress */
        $progressBar = $this->_progressBarFactory->create(
            [
                'output' => $output,
                'max' => count($items),
            ]
        );
        $progressBar->setFormat(
            '%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%'
        );
        $progressBar->start();
        $downloadCounter = 0;
        $failedCounter = 0;

        /** @var $syncAssetsFactory */

        foreach ($items as $item) {
            try {
                $syncAssetsFactory = $this->_syncAssetsFactory->create();
                $productItem = (array)$item;

                /** @var  $ebizchargeInternalId */
                $ebizchargeInternalId = isset($productItem['ItemInternalId']) ? $productItem['ItemInternalId'] : '';
                $itemName = isset($productItem['Name']) ? $productItem['Name'] : '';
                $itemSku = isset($productItem['SKU']) ? $productItem['SKU'] : '';

                if ($itemSku === "") {
                    $itemSku = str_replace(" ", "-", $itemName);
                    $productItem['SKU'] = $itemSku;
                }

                /** if product exists */
                if ($itemName === '') {
                    $failedCounter++;
                    $syncAssetsFactory->saveSyncCronValues(
                        [SyncAssetsInterface::FAILED_COUNTER => $failedCounter],
                        SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS
                    );
                    $this->_ebizchargeLogger->addInfo(__("Product Name: " . $itemName .
                        " is empty so we cannot download this item."));
                    $progressBar->advance();
                    continue;
                }
                $downloadCounter++;

                $isDownload = true;
                /** @var $customerModel */
                $product = $syncAssetsFactory->importEbizchargeItemToMagento($productItem, $isDownload);

                if (!$product) {
                    $failedCounter++;
                }
                $this->_ebizchargeLogger->addInfo("Product Sku: " . $itemSku .
                    " has been imported to Local Database successfully");
            } catch (Exception $e) {
                $failedCounter++;
                $errorMessage[] = [date('Y-m-d H:i:s'), $e->getMessage()];
                $this->csvProcessor->appendData(static::DOWNLOAD_ITEMS_ERROR_LOG_FILE, $errorMessage);

                $message = 'Exception occurred during downloading item from EBizCharge Hub. Error: ' .
                    $e->getMessage();
                // phpcs:ignore
                // $this->writeLine($message);
                $this->_ebizchargeLogger->addCritical(__($message));
                $progressBar->advance();
                continue;
            }

            /** Saving EBizCharge Item to the internal item */
            $syncAssetsFactory->saveSyncCronValues(
                [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                    SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => $downloadCounter,
                    SyncAssetsInterface::LAST_SYNC_TOTAL_RECORDS => $downloadCounter,
                    SyncAssetsInterface::LAST_SYNC_COUNTER => $downloadCounter,
                    SyncAssetsInterface::FAILED_COUNTER => $failedCounter,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                    SyncAssetsInterface::SYNC_REMARKS => __("Sync of items from EBizCharge is in progress")
                ],
                SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ITEMS
            );

            $progressBar->advance();
        }

        $progressBar->finish();
        $output->write(PHP_EOL);
    }

    /**
     * Download Orders
     *
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws NoSuchEntityException
     */
    public function downloadOrders(string $command, InputInterface $input, OutputInterface $output): bool
    {
        /** @var  $isCommandCompleted */
        $isCommandCompleted = false;

        if ($command === CommandsCliModelInterface::COMMAND_DOWNLOAD_ORDERS) {
            $message = "Shell process started to import latest orders from EBizCharge Hub.";
            $this->writeLine($message);
            $this->_ebizchargeLogger->addInfo(__($message));
            $message = "Fetching please wait---------------------------------------------------";
            // phpcs:ignore
            $this->writeLine($message);

            /** @var $syncAssetsFactory */
            $syncAssetsFactory = $this->_syncAssetsFactory->create()
                ->loadByProcessCode(SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS);
            $startTime = $syncAssetsFactory->getCurrentDateTime();

            /** @var $ebizchargeOrders */
            $ebizchargeOrders = $this->_syncAssetsFactory->create()->getLatestEbizchargeOrders();

            /** @var $totalRemoteOrders */
            $totalRemoteOrders = count($ebizchargeOrders);

            if ($totalRemoteOrders === 0) {
                /** @var  $params */
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::FAILED_COUNTER => 0,
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Sync of orders are in progress from EBizCharge Gateway"
                    )
                ];
                $syncAssetsFactory->saveSyncCronValues(
                    $params,
                    SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS
                );
                $message = "Total new (" . $totalRemoteOrders .
                    ") orders found at EBizCharge Hub. So process got exit.";
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo(__($message));
                return false;
            }

            /** Sync Assets Factory */
            $syncAssetsFactory->saveSyncCronValues(
                [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                    SyncAssetsInterface::REMOTE_TOTAL_RECORDS => $totalRemoteOrders,
                    SyncAssetsInterface::START_TIME => $startTime,
                    SyncAssetsInterface::CREATED_AT => $startTime,
                    SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => 0,
                    SyncAssetsInterface::FAILED_COUNTER => 0,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                    SyncAssetsInterface::TOTAL_TIME => '0d 0h 0m 0s',
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Sync of orders shell process started from EBizCharge Hub."
                    )
                ],
                SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS
            );

            $this->writeLine("Total (" . $totalRemoteOrders . ") Orders found at EBizCharge Payment Gateway");

            /** if found items at EBizCharge */
            if ($totalRemoteOrders > 0) {
                $this->writeLine("Downloading the Orders to the Local System");

                /** Render Orders Integration Progress Bar */
                $this->renderDownloadOrdersIntegrationProgressBar($output, $ebizchargeOrders);

                $this->writeLine("Total (" . $totalRemoteOrders .
                    ") Orders downloaded and has been saved to the Local Database");

                /** saving sync customers cron process */
                $endTime = $syncAssetsFactory->getCurrentDateTime();
                $dateDiff = $syncAssetsFactory->getDateTimeDiff($startTime, $endTime);

                /** @var  $params */
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::END_TIME => $endTime,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::TOTAL_TIME => $dateDiff->h . 'h ' . $dateDiff->i . 'm ' . $dateDiff->s . 's',
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Sync of orders are in progress from EBizCharge Gateway Hub."
                    )
                ];
                $syncAssetsFactory->saveSyncCronValues(
                    $params,
                    SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS
                );
            }
            $isCommandCompleted = true;
        } else {
            // phpcs:disable
            $this->_ebizchargeLogger->addCritical(__("Sorry the provided command is not correct please provide valid command " . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . " " . CommandsCliModelInterface::EBIZCHARGE_COMMAND . " " . CommandsCliModelInterface::COMMAND_DOWNLOAD_ORDERS));
            $this->writeLine("Provided command is not correct please provide valid command: e.g " . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . " " . CommandsCliModelInterface::EBIZCHARGE_COMMAND . " " . CommandsCliModelInterface::COMMAND_DOWNLOAD_ORDERS);
            // phpcs:disable
        }

        return $isCommandCompleted;
    }

    /**
     * Render Orders IntegrationProgressBar
     *
     * @param OutputInterface $output
     * @param mixed $ebizchargeOrders
     * @throws Exception
     */
    public function renderDownloadOrdersIntegrationProgressBar(OutputInterface $output, $ebizchargeOrders)
    {
        /** @var ProgressBar $progress */
        $progressBar = $this->_progressBarFactory->create(
            [
                'output' => $output,
                'max' => count($ebizchargeOrders),
            ]
        );
        $progressBar->setFormat(
            '%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%'
        );
        $progressBar->start();
        $downloadCounter = 0;
        $failedCounter = 0;

        /** Looping through the EBizCharge Orders */
        /** @var  $order */
        foreach ($ebizchargeOrders as $order) {

            $order = $this->_orderFactory->create()
                ->getEbizChargeOrder(['salesOrderInternalId' => $order->SalesOrderInternalId]);

            /** @var $syncAssetsFactory */
            $syncAssetsFactory = $this->_syncAssetsFactory->create();

            /** if order exists at EBizCharge */
            if (!$order) {
                $failedCounter++;
                $syncAssetsFactory->saveSyncCronValues(
                    [SyncAssetsInterface::FAILED_COUNTER => $failedCounter],
                    SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS
                );
                $this->_ebizchargeLogger->addError(__('Sorry order does not exist at EBizCharge Gateway Hub.'));
                // phpcs:ignore
                print_r("Sorry this order doest not exist at EBizCharge Gateway Hub.");
                $progressBar->advance();
                continue;
            }

            try {
                $ebizOrderInternalId = isset($order->SalesOrderInternalId) ? $order->SalesOrderInternalId : "";
                if (!$ebizOrderInternalId) {
                    $progressBar->advance();
                    continue;
                }

                /** @var $OrderModel  */
                $orderResponse = $syncAssetsFactory->importEbizchargeOrdersToMagento($order);


                if ($orderResponse['error'] === true) {
                    $failedCounter++;
                }
                /** Saving EBizCharge Item to the internal item */
                $downloadCounter++;

                if ($orderResponse['error'] === false) {
                    $incrementId = $orderResponse['response']['increment_id'];
                    $this->_ebizchargeLogger->addInfo(__("Order Number: " . $incrementId .
                        " already exists for EBizCharge Internal Id: " . $ebizOrderInternalId));
                }
            } catch (Exception $e) {
                $failedCounter++;
                $this->createCSVFile(CommandsCliModelInterface::DOWNLOAD_ORDERS_ERROR_LOG_FILE);
                // $this->csvProcessor->appendData(
                //static::DOWNLOAD_ORDERS_ERROR_LOG_FILE, [date('Y-m-d H:i:s'), $e->getMessage()]);

                $this->_ebizchargeLogger->addInfo('Exception: occurred during downloading order ' .
                    $ebizOrderInternalId . ' Error: ' . $e->getMessage());
                // var_dump($e->getTrace());exit;
                // phpcs:ignore
                //   print_r("\r\n " . $ebizOrderInternalId . " Order could not be saved  Exception: " . $e->getMessage() . "\n");
            }

            /** @var  $params */
            $params = [
                SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => $downloadCounter,
                SyncAssetsInterface::LAST_SYNC_TOTAL_RECORDS => $downloadCounter,
                SyncAssetsInterface::LAST_SYNC_COUNTER => $downloadCounter,
                SyncAssetsInterface::FAILED_COUNTER => $failedCounter,
                SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                SyncAssetsInterface::SYNC_REMARKS => __(
                    "Sync of Orders from EBizCharge to Local System is in progress"
                )
            ];

            $syncAssetsFactory->saveSyncCronValues(
                $params,
                SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_DOWNLOAD_ORDERS
            );

            $this->_ebizchargeLogger->addInfo('EBizCharge Orders download to Local Database successfully');
            $progressBar->advance();
        }
        $progressBar->finish();

        $output->write(PHP_EOL);
    }

    /**
     * Create CSV File
     *
     * @param string $filePath
     */
    public function createCSVFile(string $filePath = "")
    {
        try {
            /** @var  $logDirectory */
            $logDirectory = $this->_fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            // phpcs:ignore
            if (!is_file($filePath)) {
                $stream = $logDirectory->openFile($filePath, 'w+');
            }

        } catch (FileSystemException $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Error occurred during creating file. " . $exception->getMessage()
            ));
        }
    }

    /**
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws Exception
     */
    public function uploadCustomers(string $command, InputInterface $input, OutputInterface $output): bool
    {
        $isCommandCompleted = false;

        /** Command upload Customers */
        if ($command === CommandsCliModelInterface::COMMAND_UPLOAD_CUSTOMERS) {
            $message = "Shell process started to check customers to export to EBizCharge Hub.";
            $this->writeLine($message);
            $message = "Calculating customers please wait -----------";
            $this->writeLine($message);

            /** @var $syncAssetsFactory */
            $syncAssetsFactory = $this->_syncAssetsFactory->create();
            $startTime = $syncAssetsFactory->getCurrentDateTime();
            $cronCode = SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_CUSTOMERS;

            /** @var  $params */
            $params = [
                SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_FAILED,
                SyncAssetsInterface::REMOTE_TOTAL_RECORDS => 0,
                SyncAssetsInterface::START_TIME => $startTime,
                SyncAssetsInterface::CREATED_AT => $startTime,
                SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => 0,
                SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_FAILED,
                SyncAssetsInterface::TOTAL_TIME => '0d 0h 0m 0s',
            ];
            $syncAssetsFactory->saveSyncCronValues($params, $cronCode);

            /** @var $ebizchargeCustomers */
            $localCustomers = $this->_customerFactory->create()->checkTotalLocalCustomers();
            $totalLocalCustomers = count($localCustomers);

            if ($totalLocalCustomers === 0) {
                /** saving sync customers cron process */
                /** @var  $params */
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::REMOTE_TOTAL_RECORDS => $totalLocalCustomers,
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Sync of upload Customers has started to EBizCharge Gateway Hub."
                    )
                ];

                /** Sync Assets Factory */
                $syncAssetsFactory->saveSyncCronValues($params, $cronCode);
                $message = "Process completed as total (" . $totalLocalCustomers . ") Customers found to export to EBizCharge Hub. ";
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo($message);
                return $isCommandCompleted;
            }

            /** saving sync customers cron process */
            /** @var  $params */
            $params = [
                SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                SyncAssetsInterface::REMOTE_TOTAL_RECORDS => $totalLocalCustomers,
                SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                SyncAssetsInterface::SYNC_REMARKS => __(
                    "Sync of upload Customers has started to EBizCharge Gateway Hub."
                )
            ];
            /** Sync Assets Factory */
            $syncAssetsFactory->saveSyncCronValues($params, $cronCode);

            $this->writeLine("Total (" . $totalLocalCustomers . ") Customers found with Local Database ");

            /** if found customers at EBizCharge */
            if ($localCustomers && count($localCustomers) > 0) {
                $this->writeLine("Uploading customers to the EBizCharge Payment Gateway");
                $this->renderCustomersUploadIntegrationProgressBar($output, $localCustomers);
                $this->writeLine("Total (" . $totalLocalCustomers .
                    ") customers has been uploaded to EBizCharge Gateway Hub.");

                /** saving sync customers cron process */
                $endTime = $syncAssetsFactory->getCurrentDateTime();
                $dateDiff = $syncAssetsFactory->getDateTimeDiff($startTime, $endTime);

                /** @var  $params */
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::END_TIME => $endTime,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::TOTAL_TIME => $dateDiff->h . 'h ' . $dateDiff->i . 'm ' . $dateDiff->s . 's',
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Sync of Customers to the EBizCharge Gateway has been Completed."
                    )
                ];
                /** saving values to cron tables */
                $syncAssetsFactory->saveSyncCronValues($params, $cronCode);
            }
            $isCommandCompleted = true;

        } else {
            // phpcs:ignore
            $this->writeLine("Your provided Cli-command is not correct, please provide valid Cli-command for Upload customers: e.g php bin/magento " . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . " " . CommandsCliModelInterface::EBIZCHARGE_COMMAND . " " . CommandsCliModelInterface::COMMAND_UPLOAD_CUSTOMERS);
        }
        return $isCommandCompleted;
    }

    /**
     * Render Upload Customers Integration Progress Bar
     *
     * @param OutputInterface $output
     * @param mixed $customers
     * @throws Exception
     */
    public function renderCustomersUploadIntegrationProgressBar(OutputInterface $output, $customers)
    {
        /** @var ProgressBar $progress */
        $progressBar = $this->_progressBarFactory->create(
            [
                'output' => $output,
                'max' => count($customers),
            ]
        );
        $progressBar->setFormat(
            '%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%'
        );
        $progressBar->start();
        $downloadCounter = 0;
        $startPosition = 0;

        /** @var $cronCode */
        $cronCode = SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_CUSTOMERS;
        $ebizchargeCustomers = $customers;

        /** @var $syncAssetsFactory */
        foreach ($ebizchargeCustomers as $ebizchargeCustomer) {
            $customerId = $ebizchargeCustomer->getEntityId();
            $ebizchargeCustomer = $this->_customerFactory->create()->load($customerId);

            /** Saving Ebizcharge Customer to the internal customer */
            $downloadCounter++;
            $syncAssetsFactory = $this->_syncAssetsFactory->create();

            /** @var  $params */
            $params = [
                SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => $downloadCounter,
                SyncAssetsInterface::LAST_SYNC_TOTAL_RECORDS => $downloadCounter,
                SyncAssetsInterface::LAST_SYNC_COUNTER => $downloadCounter,
                SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                SyncAssetsInterface::SYNC_REMARKS => __(
                    "Syncing Customers from Local Database to EBizCharge is in progress"
                )
            ];
            $syncAssetsFactory->saveSyncCronValues($params, $cronCode);

            /** @var $customerModel */
            /** saving the customer to EBizCharge  */
            $this->_customerFactory->create()->saveLocalCustomerToEbizcharge($ebizchargeCustomer);

            $this->_ebizchargeLogger->addInfo("Customer with Customer_id=" .
                $ebizchargeCustomer->getEcCustInternalid() . " has been uploaded to EBizCharge Gateway successfully");
            $progressBar->advance();
        }
        $progressBar->finish();

        $output->write(PHP_EOL);
    }

    /**
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws Exception
     */
    public function uploadItems(string $command, InputInterface $input, OutputInterface $output): bool
    {
        $isCommandCompleted = false;

        /** Command upload Customers */
        if ($command === CommandsCliModelInterface::COMMAND_UPLOAD_ITEMS) {
            $message = "Shell process started to check missing items to upload to the EBizCharge Hub.";
            $this->writeLine($message);
            $this->_ebizchargeLogger->addInfo(__($message));
            $message = "Fetching please wait-----------------------";
            $this->writeLine($message);

            /** @var $syncAssetsFactory */
            $syncAssetsFactory = $this->_syncAssetsFactory->create();
            $startTime = $syncAssetsFactory->getCurrentDateTime();
            $cronCode = SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_ITEMS;

            /** @var  $params */
            $params = [
                SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_FAILED,
                SyncAssetsInterface::REMOTE_TOTAL_RECORDS => 0,
                SyncAssetsInterface::START_TIME => $startTime,
                SyncAssetsInterface::CREATED_AT => $startTime,
                SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => 0,
                SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_FAILED,
                SyncAssetsInterface::TOTAL_TIME => '0d 0h 0m 0s',
            ];
            $syncAssetsFactory->saveSyncCronValues($params, $cronCode);

            /** @var $localProducts */
            $localProducts = $this->_productFactory->create()->getLatestLocalProducts();

            /** @var $totalLocalProducts */
            $totalLocalProducts = count($localProducts);

            if (count($localProducts) === 0) {
                /** saving sync customers cron process */
                /** @var  $params */
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::REMOTE_TOTAL_RECORDS => $totalLocalProducts,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Sync of Products|Items has been started to EBizCharge Gateway Hub."
                    )
                ];

                /** Sync Assets Factory */
                $syncAssetsFactory->saveSyncCronValues($params, $cronCode);
                $message = "Process completed as total (" . $totalLocalProducts . ") products|items found to export to EBizCharge Hub. ";
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo($message);
                return false;
            }

            /** saving sync customers cron process */
            /** @var  $params */
            $params = [
                SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                SyncAssetsInterface::REMOTE_TOTAL_RECORDS => $totalLocalProducts,
                SyncAssetsInterface::SYNC_REMARKS => __(
                    "Sync of Products|Items has been started to EBizCharge Gateway Hub."
                )
            ];

            /** Sync Assets Factory */
            $syncAssetsFactory->saveSyncCronValues($params, $cronCode);


            /** if found customers at EBizCharge */
            if ($localProducts && count($localProducts) > 0) {

                $message = "Total (" . $totalLocalProducts .
                    ") Products/Items found to export to EBizCharge Hub. ";
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo(__($message));
                $message = "Integrating and uploading Products/Items to EBizCharge Gateway Hub.";
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo($message);

                $this->renderProductsUploadIntegrationProgressBar($output, $localProducts);
                $message = "Total (" . $totalLocalProducts .
                    ") Items uploaded to EBizCharge Gateway Hub.";
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo($message);

                /** saving sync customers cron process */
                $endTime = $syncAssetsFactory->getCurrentDateTime();
                $dateDiff = $syncAssetsFactory->getDateTimeDiff($startTime, $endTime);

                /** @var  $params */
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::END_TIME => $endTime,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::TOTAL_TIME => $dateDiff->h . 'h ' . $dateDiff->i . 'm ' . $dateDiff->s . 's',
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Upload of Items has been completed to EBizCharge Payment Gateway Hub."
                    )
                ];
                /** saving values to cron tables */
                $syncAssetsFactory->saveSyncCronValues($params, $cronCode);
            }
            $isCommandCompleted = true;

        } else {
            $message = "Your provided Cli-Command is not correct. So please provide a valid Cli-Command to download customers: e.g php bin/magento ebizcharge:command:cli --command uplaod-items";
            $this->writeLine($message);
            $this->_ebizchargeLogger->addInfo($message);
            $isCommandCompleted = false;
        }
        return $isCommandCompleted;
    }

    /**
     * Render Products Upload Integration Progress Bar
     *
     * @param OutputInterface $output
     * @param mixed $localItems
     */
    public function renderProductsUploadIntegrationProgressBar(OutputInterface $output, $localItems)
    {
        /** @var ProgressBar $progress */
        $progressBar = $this->_progressBarFactory->create(
            [
                'output' => $output,
                'max' => count($localItems),
            ]
        );
        $progressBar->setFormat(
            '%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%'
        );
        $progressBar->start();
        $uploadCounter = 0;

        /** @var  $item */
        foreach ($localItems as $item) {
            try {
                $syncAssetsFactory = $this->_syncAssetsFactory->create();
                $itemSku = $item->getSku();
                $ebizchargeInternalId = $item->getEcItemInternalid() ?? false;

                $uploadCounter++;
                /** Saving EBizCharge Item to the internal item */

                /** @var  $params */
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                    SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => $uploadCounter,
                    SyncAssetsInterface::LAST_SYNC_TOTAL_RECORDS => $uploadCounter,
                    SyncAssetsInterface::LAST_SYNC_COUNTER => $uploadCounter,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Syncing items from Local database to EBizCharge is in progress"
                    )
                ];

                $syncAssetsFactory->saveSyncCronValues(
                    $params,
                    SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_ITEMS
                );

                /** Saving item to EBizCharge Gateway */
                /** @var $customerModel */
                $this->_productFactory->create()->syncItemToEbizcharge($item);

                $this->_ebizchargeLogger->addInfo('Prouct Sku: ' . $itemSku .
                    ' has been uploaded to EBizCharge Successfully ');
                $progressBar->advance();
            } catch (Exception $e) {
                $this->_ebizchargeLogger->addInfo(__(
                    'Exception occurred during upload the item to EBizCharge Product SKU:' . $itemSku .
                    ' Exception: ' . $e->getMessage()
                ));
                $progressBar->advance();
                continue;
            }
        }
        $progressBar->finish();
        $output->write(PHP_EOL);
    }

    /**
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws Exception
     */
    public function uploadOrders(string $command, InputInterface $input, OutputInterface $output): bool
    {
        /** @var  $isCommandCompleted */
        $isCommandCompleted = false;

        /** Command upload Customers */
        if ($command === CommandsCliModelInterface::COMMAND_UPLOAD_ORDERS) {

            $message = "Shell process started to export mission Orders to EBizCharge Hub.";
            $this->writeLine($message);
            $this->_ebizchargeLogger->addInfo(__($message));
            $message = "Fetching missing orders please wait---------------------------------------------------";
            $this->writeLine($message);

            /** @var $syncAssetsFactory */
            $syncAssetsFactory = $this->_syncAssetsFactory->create();
            $startTime = $syncAssetsFactory->getCurrentDateTime();
            $cronCode = SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_ORDERS;

            /** @var  $params */
            $params = [
                SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_FAILED,
                SyncAssetsInterface::REMOTE_TOTAL_RECORDS => 0,
                SyncAssetsInterface::START_TIME => $startTime,
                SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => 0,
                SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_FAILED,
                SyncAssetsInterface::TOTAL_TIME => '0d 0h 0m 0s',
            ];
            $syncAssetsFactory->saveSyncCronValues($params, $cronCode);

            /** @var $localOrders */
            $localOrders = $this->_orderFactory->create()->getLatestLocalOrders();
            $totalLocalOrders = count($localOrders);

            if ($totalLocalOrders === 0) {
                /** saving sync Orders cron process */
                /** @var  $params */
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::REMOTE_TOTAL_RECORDS => $totalLocalOrders,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Syncing of Orders started from Local Database to EBizCharge Gateway Hub."
                    )
                ];
                /** Sync Assets Factory */
                $syncAssetsFactory->saveSyncCronValues($params, $cronCode);
                $message = "Process completed as total (" . $totalLocalOrders . ") Orders found to export to EBizCharge Hub.";
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo($message);

                return false;
            }

            /** saving sync Orders cron process */
            /** @var  $params */
            $params = [
                SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                SyncAssetsInterface::REMOTE_TOTAL_RECORDS => $totalLocalOrders,
                SyncAssetsInterface::SYNC_REMARKS => __(
                    "Syncing of Orders started from Local Database to EBizCharge Gateway Hub."
                )
            ];
            /** Sync Assets Factory */
            $syncAssetsFactory->saveSyncCronValues($params, $cronCode);

            $this->writeLine("Total (" . $totalLocalOrders . ") Orders found with LOCAL DATABASE");

            /** if found customers at EBizCharge */
            if ($localOrders && count($localOrders) > 0) {
                $this->writeLine("Uploading Orders to EBizCharge Gateway");
                $this->renderOrdersUploadIntegrationProgressBar($output, $localOrders);
                $this->writeLine("Total (" . $totalLocalOrders .
                    ") orders has been uploaded to the EBizCharge Gateway");

                /** saving sync customers cron process */
                $endTime = $syncAssetsFactory->getCurrentDateTime();
                $dateDiff = $syncAssetsFactory->getDateTimeDiff($startTime, $endTime);

                /** @var  $params */
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::END_TIME => $endTime,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_COMPLETED,
                    SyncAssetsInterface::TOTAL_TIME => $dateDiff->h . 'h ' . $dateDiff->i . 'm ' . $dateDiff->s . 's',
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Sync of local Orders to EBizCharge has been Completed"
                    )
                ];
                /** saving values to cron tables */
                $syncAssetsFactory->saveSyncCronValues($params, $cronCode);
            }
            $isCommandCompleted = true;

        } else {
            // phpcs:ignore
            $this->writeLine("Sorry provided command is not correct, please provide correct command for Upload Orders e.g php bin/magento " . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . " " . CommandsCliModelInterface::EBIZCHARGE_COMMAND . " " . CommandsCliModelInterface::COMMAND_UPLOAD_ORDERS);
        }

        return $isCommandCompleted;
    }

    /**
     * Render Orders Upload Integration Progress Bar
     *
     * @param OutputInterface $output
     * @param mixed $localOrders
     */
    public function renderOrdersUploadIntegrationProgressBar(OutputInterface $output, $localOrders)
    {
        /** @var ProgressBar $progress */
        $progressBar = $this->_progressBarFactory->create(
            [
                'output' => $output,
                'max' => count($localOrders),
            ]
        );
        $progressBar->setFormat(
            '%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%'
        );
        $progressBar->start();
        $uploadCounter = 0;

        /** @var $order */
        foreach ($localOrders as $order) {
            try {
                /** @var $incrementId */
                $incrementId = $order->getIncrementId();
                $syncAssetsFactory = $this->_syncAssetsFactory->create();

                $uploadCounter++;
                /** Saving EBizCharge Item to the internal item */

                /** @var  $params */
                $params = [
                    SyncAssetsInterface::STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                    SyncAssetsInterface::TOTAL_DOWNLOAD_RECORDS => $uploadCounter,
                    SyncAssetsInterface::LAST_SYNC_TOTAL_RECORDS => $uploadCounter,
                    SyncAssetsInterface::LAST_SYNC_COUNTER => $uploadCounter,
                    SyncAssetsInterface::PROCESS_CURRENT_STATUS => SyncAssetsInterface::ASSETS_STATUS_IN_PROGRESS,
                    SyncAssetsInterface::SYNC_REMARKS => __(
                        "Syncing Order from Local database to EBizCharge is in progress"
                    )
                ];

                $syncAssetsFactory->saveSyncCronValues(
                    $params,
                    SyncAssetsInterface::SYNC_ASSETS_CRON_CODE_UPLOAD_ORDERS
                );

                /** Order Factory and Export Order */
                $this->_orderFactory->create()->exportOrderToEbizcharge($order);

                $this->_ebizchargeLogger->addInfo('EBizCharge Orders exported to EBizCharge successfully');
                $progressBar->advance();
            } catch (Exception $e) {
                $this->_ebizchargeLogger->addInfo(__('Order Export Exception Order Number:' .
                    $incrementId . ' Ex: ' . $e->getMessage()));
                // phpcs:ignore
                dump($e->getMessage());
                $progressBar->advance();
                continue;
            }
        }
        $progressBar->finish();
        $output->write(PHP_EOL);
    }

    /**
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws Exception
     */
    public function reOrderViaCli(string $command, InputInterface $input, OutputInterface $output): bool
    {
        /** @var  $isCommandCompleted */
        $isCommandCompleted = false;

        /** @var  $cliCommand */
        $cliCommand = $command;
        $commandOption = $input->getOption(CommandsCliModelInterface::INPUT_SECOND_ARGUMENT_NAME);
        /**
         * if Cli command or command options are not given
         */
        if (!$cliCommand || !$commandOption) {
            // phpcs:ignore
            $this->writeLine("Error: Please provide correct options for order number Please see php bin/magento --list " . "\n" . "or see 'php bin/magento " . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . " " . CommandsCliModelInterface::EBIZCHARGE_COMMAND . " " . CommandsCliModelInterface::COMMAND_RE_ORDER_VIA_CRON . " --order 1\' where 1 is order number");
            return false;
        }

        /** @var  $orderNumber Order Number from Input Options */
        $orderNumber = $input->getOption(CommandsCliModelInterface::INPUT_SECOND_ARGUMENT_NAME);

        /** @var  $currentOrder */
        $currentOrder = $this->_orderFactory->create()->loadByAttribute('increment_id', $orderNumber);

        /** still order not loaded with Increment Id */
        if (!$currentOrder->getId()) {
            $currentOrder = $this->_orderFactory->create()->load($orderNumber);
        }
        /** if still no order found with order number */
        if (!$currentOrder->getId()) {
            $this->writeLine("Sorry no parent order exists with the provided Order Number: " . $orderNumber);
        }

        /** temp upload the order to EBizCharge */
        /*
              $upload = $this->_orderFactory->create()->syncOrderToEbizcharge($currentOrder->getEntityId());

                //var_dump($currentOrder->debug());
                exit;
        */

        /**
         * Re-Create Order with Parent Order
         */
        /** @var $reOrder */
        $reOrder = $this->_orderFactory->create()->reOrder($currentOrder);
        $isCommandCompleted = true;

        return $isCommandCompleted;
    }

    /**
     * Send Low Stock Email Notifications
     *
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @phpcs:disable
     */
    public function sendLowStockNotifications(string $command, InputInterface $input, OutputInterface $output): bool
    {
        /** @var  $isCommandCompleted */
        $isCommandCompleted = false;

        /** if Command is low stock Notifications via cron  */
        if ($command === CommandsCliModelInterface::COMMAND_LOW_STOCK_NOTIFICATIONSL_VIA_CRON) {
            /** checking low stock items */
            // phpcs:disable
            $this->writeLine("Shell process started to check low stock items to send notifications from EBizCharge Payment Gateway");
            /** @var  $stockItems */
            $stockItems = $this->_productFactory->create()->prepareStockItemsForLowStockEmail('cli');
            /** @var  $isCommandCompleted */
            $isCommandCompleted = false;

        } else {
            $this->writeLine("Sorry the provided command is not correct, please provide valid command for download customers: e.g php bin/magento " . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . " " . CommandsCliModelInterface::EBIZCHARGE_COMMAND . " " . CommandsCliModelInterface::COMMAND_LOW_STOCK_NOTIFICATIONSL_VIA_CRON);
        }

        return $isCommandCompleted;
    }

    /**
     * Manually Run Create Recurring Orders Cron
     *
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function manuallyRunCreateRecurringOrdersCron(
        string          $command,
        InputInterface  $input,
        OutputInterface $output
    ): int
    {
        /** @var  $isCommandCompleted */
        $isCommandCompleted = 0;

        /** if Command is low stock Notifications via cron  */
        if ($command === CommandsCliModelInterface::COMMAND_MANUAL_RUN_RECURRING_ORDERS_CRON) {
            /** checking low stock items */
            $this->writeLine(
                "Shell process started to run the Recurring Orders CRON EBizCharge Payment Gateway"
            );

            /** @var $startDate */
            $fromRecurringDate = date_create("@" . strtotime('-10 days'));

            /** @var  $recurringOrders */
            $recurringOrdersCollection = $this->_orderFactory->create()
                ->prepareRecurringOrders($fromRecurringDate, true, 'cli');

            $isCommandCompleted = 1;

        } else {
            $this->writeLine(
                "Sorry provided command is not correct, please provide valid command for : e.g php bin/magento " .
                CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . " " . CommandsCliModelInterface::EBIZCHARGE_COMMAND . " " .
                CommandsCliModelInterface::COMMAND_LOW_STOCK_NOTIFICATIONSL_VIA_CRON
            );
        }
        return $isCommandCompleted;
    }

    /**
     * Manually Download Payments Cron
     *
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function manuallyDownloadPaymentsCron(string $command, InputInterface $input, OutputInterface $output): int
    {
        /** @var  $isCommandCompleted */
        $isCommandCompleted = 0;

        /** if Command Download Payments Manually cron  */
        if ($command === CommandsCliModelInterface::COMMAND_MANUAL_DOWNLOAD_PAYMENTS_CRON) {
            /** checking low stock items */
            $message = "Shell process started to download the Payments via CRON from EBizCharge Payment Hub.";
            $this->writeLine($message);
            $this->_ebizchargeLogger->addInfo($message);
            /**
             * History Payments
             */
            $historyPayments = $this->_paymentHistory->saveEbizchargePaymentsHistoryToLocal();

            if (!$historyPayments) {
                $message = "Total saved new payments " . $historyPayments;
                $this->writeLine("\n" . "----------------------------------------");
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo($message);
            }
            $isCommandCompleted = 1;

        } else {
            // phpcs:disable
            $this->writeLine(
                "Sorry provided command is not correct, please provide valid command for : e.g php bin/magento " .
                CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . " " . CommandsCliModelInterface::EBIZCHARGE_COMMAND . " " .
                CommandsCliModelInterface::COMMAND_MANUAL_DOWNLOAD_PAYMENTS_CRON
            );
            // phpcs:enable
        }

        return $isCommandCompleted;
    }
    // phpcs:enable

    /**
     * Manually Sync Items Stock CRON
     *
     * @param string $command
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return bool
     */
    public function manuallySyncItemsStockCron(
        string          $command = "",
        InputInterface  $input = null,
        OutputInterface $output = null
    ): bool
    {
        /** @var  $isCommandCompleted */
        $isCommandCompleted = false;

        try {

            if ($command === CommandsCliModelInterface::COMMAND_MANUAL_SYNC_ITEMS_STOCK_CRON) {

                /** Shell process sync started */
                $message = "Shell process started to sync items stock via CRON at EBizCharge Payment Hub.";
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo($message);

                $productsCollection = $this->_productFactory->create()->syncItemsStockToEbizCharge();

                /** Shell process sync started */
                $message = "Shell process completed to sync stock via CRON JOB.";
                $this->writeLine($message);
                $this->_ebizchargeLogger->addInfo($message);
                $isCommandCompleted = true;
            }
        } catch (Exception $e) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during syncing item stock. Error: " .
                $e->getMessage()));
        }

        return $isCommandCompleted;
    }

    /**
     * Manually Update Customer by ID
     *
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    public function manuallyUpdateCustomer(string $command, InputInterface $input, OutputInterface $output): bool
    {
        /** @var  $isCommandCompleted */
        $isCommandCompleted = false;

        try {
            /** @var $customerId */
            $customerId = $input->getOption(CommandsCliModelInterface::INPUT_ARGUMENT_TYPE_CUSTOMER_ID);
            $customerFactory = $this->_customerFactory->create();

            /**
             * if we have options from CLI Customer Id
             */
            if ($customerId) {
                /** @var $customer */
                $customer = $customerFactory->load($customerId);
                /**
                 * update Customer at EBizCharge
                 */
                $updateCustomer = $customerFactory->updateCustomerAtEbizcharge($customer);
                // phpcs:disable
                echo("\n" . "Updated Customer at EBizCharge with Customer ID : " . $customerId);

                $ebizCustomer = $customerFactory->getEbizCustomerByInternalId($customer->getEcCustInternalId());

                $customer->setEcCustToken($ebizCustomer->CustomerToken);
                //$customer->setEcDivisionId(TranApi::EBIZCHARGE_DIVISION_ID);
                $customer->setEcDivisionId($this->_ebizchargeSOAPApi->getDivisionId());

                $customer->save();
                echo("\n" . "Saved the customer Customer ID : " . $customerId . "\n");
            } else {
                $customerCollections = $customerFactory->getCollection()->addFieldToSelect('*');
                foreach ($customerCollections as $customer) {
                    $customerId = $customer->getId();
                    /** @var $customer */
                    $customer = $customerFactory->load($customerId);

                    if ((string)$customer->getEcCustToken() === "") {
                        echo("\n" . "Loaded Customer with missing Token Customer ID : " . $customerId);
                        echo("\n" . "Updating at EBizCharge Gateway Customer ID : " . $customerId);

                        /**
                         * update Customer at EBizCharge Gateway
                         */
                        $updateCustomer = $customerFactory->updateCustomerAtEbizcharge($customer);
                        echo("\n" . "Updated Customer at EBizCharge with Customer ID : " . $customerId);

                        $ebizCustomer = $customerFactory->getEbizCustomerByInternalId($customer->getEcCustInternalId());
                        $customer->setEcCustToken($ebizCustomer->CustomerToken);
                        $customer->setEcDivisionId(SoapApiModelInterface::EBIZCHARGE_DIVISION_ID);
                        $customer->setEcDivisionId($this->_ebizchargeSOAPApi->getDivisionId());

                        $customer->save();
                        echo("\n" . "Saved the customer Customer ID : " . $customerId . "\n");
                    }
                }
            }
            // phpcs:enable
            $isCommandCompleted = true;

        } catch (Exception $e) {
            $this->_ebizchargeLogger->addCritical(__("Exception occurred during updating the customer Error: " .
                $e->getMessage()));
        }

        return $isCommandCompleted;
    }

    /**
     * Format out put to cli
     *
     * @param string $string
     * @param string $type
     * @return string
     */
    public function formateOutput(string $string, string $type): string
    {
        switch ($type) {
            case 'info':
                return '<info>' . $string . '</info>';
            case 'error':
                return '<error>' . $string . '</error>';
            default:
                return '<comment>' . $string . '</comment>';
        }
    }
    // phpcs:enable

    /**
     * Render Progress Bar
     *
     * @param OutputInterface $output
     * @param mixed $progressItems
     */
    public function renderProgressBar(OutputInterface $output, $progressItems)
    {
        /** @var ProgressBar $progress */
        $progressBar = $this->_progressBarFactory->create(
            [
                'output' => $output,
                'max' => count($progressItems),
            ]
        );
        $progressBar->setFormat(
            '%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%'
        );
        $progressBar->start();

        foreach ($progressItems as $item) {
            // Add here your functionality
            $progressBar->advance();
        }
        $progressBar->finish();
        $output->write(PHP_EOL);
    }

    /**
     * Sales Orders update temporary
     *
     * @param mixed $ebizOrder
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateOrderAtEbizChargeTemp($ebizOrder)
    {
        $store = $this->_storeManager->getStore();

        /** @var $ebizSalesOrderParams */
        $ebizSalesOrderParams = (array)$ebizOrder;
        $ebizSalesOrderParams['Currency'] = $store->getCurrentCurrency()->getCode();
        $ebizSalesOrderParams['Software'] = $this->_ebizchargeSOAPApi->getSoftwareId();
        $ebizSalesOrderParams['DivisionId'] = $this->_ebizchargeSOAPApi->getDivisionId();
        $ebizSalesOrderParams['NotifyCustomer'] = true;
        $ebizSalesOrderParams['LocationId'] = $store->getName() . '-' . $store->getId();
        $ebizSalesOrderParams['IsToBeEmailed'] = true;
        $ebizSalesOrderParams['IsToBePrinted'] = true;
        $ebizSalesOrderParams['URL'] = $store->getBaseUrl(UrlInterface::URL_TYPE_WEB);

        $customerID = 300019;

        //if (isset($ebizSalesOrderParams['CustomerId']) && $ebizSalesOrderParams['CustomerId'] !== '') {
        // $customerID = $ebizSalesOrderParams['CustomerId'];
        //}
        $ebizSalesOrderParams['CustomerId'] = $customerID;

        /** @var $customer */
        $customer = $this->_customerFactory->create()->load($customerID);
        $customerBillingAddress = $customer->getDefaultBillingAddress();
        $customerShippingAddress = $customer->getDefaultShippingAddress();

        $product = $this->_productFactory->create()->load(300002);
        $unitPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $grossPrice = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();

        $qty = 1;
        $taxAmount = 10;
        $totalAmount = ($unitPrice * $qty) + $taxAmount;
        $discountAmount = 5;
        $totalAmount = $totalAmount - $discountAmount;

        $ebizSalesOrderParams['Amount'] = $totalAmount;
        $ebizSalesOrderParams['AmountDue'] = $totalAmount;
        $ebizSalesOrderParams['TotalTaxAmount'] = $taxAmount;

        $orderItem = [
            'ItemId' => $product->getId(),
            'Name' => trim($product->getName()),
            'Sku' => trim($product->getSku()),
            'Description' => trim($product->getName()),
            'UnitPrice' => $unitPrice,
            'Qty' => $qty,
            'Taxable' => false,
            'TaxRate' => 0.00,
            'UnitOfMeasure' => 'lbs',
            'TotalLineAmount' => $totalAmount,
            'TotalLineTax' => $taxAmount,
            'ItemLineNumber' => 1,
            'GrossPrice' => $grossPrice,
            'WarrantyDiscount' => $discountAmount,
            'SalesDiscount' => $discountAmount,
            'ItemClass' => ''
        ];

        $ebizSalesOrderParams['Items'] = [
            'Item' => $orderItem
        ];

        $billingStreetAddress = $customerBillingAddress->getStreet();
        $shippingStreetAddress = $customerShippingAddress->getStreet();

        $ebizSalesOrderParams['BillingAddress'] = [
            'FirstName' => $customerShippingAddress->getName(),
            'LastName' => $customerShippingAddress->getLastname(),
            'CompanyName' => $customerShippingAddress->getCompany(),
            'Address1' => isset($shippingStreetAddress[0]) ? $shippingStreetAddress[0] : '',
            'Address2' => isset($shippingStreetAddress[1]) ? $shippingStreetAddress[1] : '',
            'Address3' => isset($shippingStreetAddress[2]) ? $shippingStreetAddress[2] : '',
            'Address4' => isset($shippingStreetAddress[3]) ? $shippingStreetAddress[3] : '',
            'Address5' => isset($shippingStreetAddress[4]) ? $shippingStreetAddress[4] : '',
            'Address6' => isset($shippingStreetAddress[5]) ? $shippingStreetAddress[5] : '',
            'City' => $customerShippingAddress->getCity(),
            'State' => $customerShippingAddress->getRegion(),
            'ZipCode' => $customerShippingAddress->getPostcode(),
            'Country' => $customerShippingAddress->getCountry(),
            'IsDefault' => 1,
            'AddressId' => $customerShippingAddress->getId()
        ];
        $ebizSalesOrderParams['ShippingAddress'] = [
            'FirstName' => $customerBillingAddress->getName(),
            'LastName' => $customerBillingAddress->getLastname(),
            'CompanyName' => $customerBillingAddress->getCompany(),
            'Address1' => isset($billingStreetAddress[0]) ? $billingStreetAddress[0] : '',
            'Address2' => isset($billingStreetAddress[1]) ? $billingStreetAddress[1] : '',
            'Address3' => isset($billingStreetAddress[2]) ? $billingStreetAddress[2] : '',
            'Address4' => isset($billingStreetAddress[3]) ? $billingStreetAddress[3] : '',
            'Address5' => isset($billingStreetAddress[4]) ? $billingStreetAddress[4] : '',
            'Address6' => isset($billingStreetAddress[5]) ? $billingStreetAddress[5] : '',
            'City' => $customerBillingAddress->getCity(),
            'State' => $customerBillingAddress->getRegion(),
            'ZipCode' => $customerBillingAddress->getPostcode(),
            'Country' => $customerBillingAddress->getCountry(),
            'IsDefault' => 1,
            'AddressId' => $customerBillingAddress->getId()
        ];

        $updateOrderParams = [
            'securityToken' => $this->_ebizchargeSOAPApi->getUeSecurityToken(),
            'salesOrder' => $ebizSalesOrderParams,
            'customerId' => '',
            'subCustomerId' => '',
            'salesOrderNumber' => $ebizSalesOrderParams['SalesOrderNumber'],
            'salesOrderInternalId' => $ebizSalesOrderParams['SalesOrderInternalId']
        ];

        $updateOrderAtEbizChargeResponse = $this->_ebizchargeSOAPApi->getClient()->UpdateSalesOrder($updateOrderParams);
    }
    // phpcs:enable

    /**
     * Configure the Command Method
     *
     * @return void
     * @phpcs:disable
     */
    protected function configure()
    {
        $newLine = "\n" . "\t" . "\t" . "\t" . "\t" . "\t" . "\t" . "\t";

        $this->setName(CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND);
        $commandDescriptions = __('EBizCharge Hub CLI Framework: ');
        $commandDescriptions .= __($newLine . 'We provides very useful CLI commands to facilitate merchants. ');
        //       $commandDescriptions .= $newLine;
        $commandDescriptions .= __($newLine . '* To download customers from EBizCharge Hub : "' . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' ' . CommandsCliModelInterface::EBIZCHARGE_COMMAND . ' ' . CommandsCliModelInterface::COMMAND_DOWNLOAD_CUSTOMERS . '"');
        $commandDescriptions .= __($newLine . '* To download items from EBizCharge Hub : "' . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' ' . CommandsCliModelInterface::EBIZCHARGE_COMMAND . ' ' . CommandsCliModelInterface::COMMAND_DOWNLOAD_ITEMS . '"');
        $commandDescriptions .= __($newLine . '* To download orders from EBizCharge Hub :"' . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' ' . CommandsCliModelInterface::EBIZCHARGE_COMMAND . ' ' . CommandsCliModelInterface::COMMAND_DOWNLOAD_ORDERS . '"');
        $commandDescriptions .= __($newLine . '* To upload customers to EBizCharge Hub : "' . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' ' . CommandsCliModelInterface::EBIZCHARGE_COMMAND . ' ' . CommandsCliModelInterface::COMMAND_UPLOAD_CUSTOMERS . '"');
        $commandDescriptions .= __($newLine . '* To upload items to EBizCharge Hub : "' . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' ' . CommandsCliModelInterface::EBIZCHARGE_COMMAND . ' ' . CommandsCliModelInterface::COMMAND_UPLOAD_ITEMS . '"');
        $commandDescriptions .= __($newLine . '* To upload orders to EBizCharge Hub : "' . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' ' . CommandsCliModelInterface::EBIZCHARGE_COMMAND . ' ' . CommandsCliModelInterface::COMMAND_UPLOAD_ORDERS . '"');
        $commandDescriptions .= __($newLine . '* To re-order via cli : "' . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' ' . CommandsCliModelInterface::EBIZCHARGE_COMMAND . ' ' . CommandsCliModelInterface::COMMAND_RE_ORDER_VIA_CRON . ' --order 1" where 1 is order id');
        $commandDescriptions .= __($newLine . '* Send notifications of low stock items : "' . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' ' . CommandsCliModelInterface::EBIZCHARGE_COMMAND . ' ' . CommandsCliModelInterface::COMMAND_LOW_STOCK_NOTIFICATIONSL_VIA_CRON . '" ');
        $commandDescriptions .= __($newLine . '* To run subscribed orders manually : "' . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' ' . CommandsCliModelInterface::EBIZCHARGE_COMMAND . ' ' . CommandsCliModelInterface::COMMAND_MANUAL_RUN_RECURRING_ORDERS_CRON . '" ');
        $commandDescriptions .= __($newLine . '* To download payments manually : "' . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' ' . CommandsCliModelInterface::EBIZCHARGE_COMMAND . ' ' . CommandsCliModelInterface::COMMAND_MANUAL_DOWNLOAD_PAYMENTS_CRON . '" ');
        $commandDescriptions .= __($newLine . '* Update customers manually to EBizCharge Hub : "' . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' ' . CommandsCliModelInterface::EBIZCHARGE_COMMAND . ' ' . CommandsCliModelInterface::COMMAND_UPDATE_CUSTOMER_AT_EBIZCHARGE . '" ');
        $commandDescriptions .= __($newLine . '* Sync stocks of items manually to EBizCharge Hub : "' . CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' ' . CommandsCliModelInterface::EBIZCHARGE_COMMAND . ' ' . CommandsCliModelInterface::COMMAND_MANUAL_SYNC_ITEMS_STOCK_CRON . '" ');

        $this->setDescription($commandDescriptions);

        /** adding first command option */
        $this->addOption(
            CommandsCliModelInterface::INPUT_ARGUMENT_NAME,
            CommandsCliModelInterface::INPUT_ARGUMENT_SHORTCUT_NAME,
            InputOption::VALUE_OPTIONAL,
            'EBizCharge CLI first argument value need to be added here'
        );
        /** adding second option as an argument */
        $this->addOption(
            CommandsCliModelInterface::INPUT_SECOND_ARGUMENT_NAME,
            CommandsCliModelInterface::INPUT_SECOND_ARGUMENT_SHORTCUT_NAME,
            InputOption::VALUE_OPTIONAL,
            'EBizCharge CLI commands second argument value need to be added here'
        );

        /** adding Customer Id option as an argument */
        $this->addOption(
            CommandsCliModelInterface::INPUT_ARGUMENT_TYPE_CUSTOMER_ID,
            CommandsCliModelInterface::INPUT_ARGUMENT_TYPE_CUSTOMER_ID_SHORTCUT,
            InputOption::VALUE_OPTIONAL,
            "EBizCharge CLI commands customer id  "
        );

        parent::configure();
    }
}
