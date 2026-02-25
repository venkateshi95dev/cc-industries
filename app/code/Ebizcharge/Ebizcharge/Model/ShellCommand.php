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

use Ebizcharge\Ebizcharge\Api\Data\CommandsCliModelInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Process\PhpExecutableFinderFactory;
use Magento\Framework\ShellInterface;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Console Command Model Class
 *
 * Class ShellCommand
 */
class ShellCommand
{

    public const BP = " ";
    /**
     * Shell Command Download Customers
     *
     * @const SHELL_COMMAND_PROCESS_DOWNLOAD_CUSTOMERS
     */
    public const SHELL_COMMAND_PROCESS_DOWNLOAD_CUSTOMERS = CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND .
    ' --command ' . CommandsCliModelInterface::COMMAND_DOWNLOAD_CUSTOMERS;

    /**
     * Shell Command Download Orders
     *
     * @const SHELL_COMMAND_PROCESS_DOWNLOAD_ORDERS
     */
    public const SHELL_COMMAND_PROCESS_DOWNLOAD_ORDERS = CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND .
    ' --command ' . CommandsCliModelInterface::COMMAND_DOWNLOAD_ORDERS;

    /**
     * Shell Command Download Products
     *
     * @const SHELL_COMMAND_PROCESS_DOWNLOAD_PRODUCTS
     */
    public const SHELL_COMMAND_PROCESS_DOWNLOAD_PRODUCTS = CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND .
    ' --command ' . CommandsCliModelInterface::COMMAND_DOWNLOAD_ITEMS;

    /**
     * Shell Command Upload Customers
     *
     * @const SHELL_COMMAND_PROCESS_UPLOAD_CUSTOMERS
     */
    public const SHELL_COMMAND_PROCESS_UPLOAD_CUSTOMERS = CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND .
    ' --command ' . CommandsCliModelInterface::COMMAND_UPLOAD_CUSTOMERS;

    /**
     * Shell Command Upload Orders
     *
     * @const SHELL_COMMAND_PROCESS_UPLOAD_ORDERS
     */
    public const SHELL_COMMAND_PROCESS_UPLOAD_ORDERS = CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' --command ' .
    CommandsCliModelInterface::COMMAND_UPLOAD_ORDERS;

    /**
     * Shell Command Upload Products
     *
     * @const SHELL_COMMAND_PROCESS_UPLOAD_PRODUCTS
     */
    public const SHELL_COMMAND_PROCESS_UPLOAD_PRODUCTS = CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND .
    ' --command ' . CommandsCliModelInterface::COMMAND_UPLOAD_ITEMS;

    public const SHELL_COMMAND_PROCESS_RUN_SUBSCRIBED_ORDERS = CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND .
    ' --command ' . CommandsCliModelInterface::COMMAND_MANUAL_RUN_RECURRING_ORDERS_CRON;

    public const SHELL_COMMAND_PROCESS_RUN_FETCH_SUBSCRIBED_PAYMENTS_FROM_EBIZCHARGE =
        CommandsCliModelInterface::EBIZCHARGE_CLI_COMMAND . ' --command ' .
        CommandsCliModelInterface::COMMAND_MANUAL_DOWNLOAD_PAYMENTS_CRON;

    /**
     * @var ShellInterface
     */
    protected ShellInterface $_shell;

    /**
     * @var PhpExecutableFinder
     */
    protected PhpExecutableFinder $_phpExecutableFinder;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var DirectoryList
     */
    protected DirectoryList $directoryList;

    /**
     * @param ShellInterface $shell
     * @param PhpExecutableFinderFactory $phpExecutableFinderFactory
     * @param DirectoryList $directoryList
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        ShellInterface             $shell,
        PhpExecutableFinderFactory $phpExecutableFinderFactory,
        DirectoryList              $directoryList,
        EbizchargeLogger           $ebizchargeLogger
    )
    {
        /** @var  shell */
        $this->_shell = $shell;

        /** @var  phpExecutableFinder */
        $this->_phpExecutableFinder = $phpExecutableFinderFactory->create();
        /**
         * Directory List
         */
        $this->directoryList = $directoryList;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Shell Process Download Customers
     *
     * @throws LocalizedException
     */
    public function shellProcessDownloadCustomers()
    {
        return $this->run(self::SHELL_COMMAND_PROCESS_DOWNLOAD_CUSTOMERS);
    }

    /**
     * Run and execute Method
     *
     * @param string $command
     * @return bool
     */
    public function run($command = ''): bool
    {
        if (!$command && $command !== '') {
            return false;
        }
        // shell command for running cron job $commandDownloadCron = $command.' --group=\'ebizcharge_sync_downloads\'';
        try {

            $magentoRoot = $this->directoryList->getRoot();
            $phpPath = $this->_phpExecutableFinder->find() ?: 'php';
            $command = $phpPath . ' ' . $magentoRoot . '/bin/magento ' . $command;

            /** running Process at background */
            $this->_shell->execute($command);
            $this->_ebizchargeLogger->addInfo("Process started running with command = " . $command);
            //  $this->_shell->execute('php bin/magento cron:run --group="ebizcharge_sync_downloads"');
            return true;
        } catch (LocalizedException $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Localized exception occurred during executing process Exception:" . $exception->getMessage()
            ));
            return false;
        }
    }

    /**
     * Shell Process Download Orders
     *
     * @throws LocalizedException
     */
    public function shellProcessDownloadOrders()
    {
        return $this->run(self::SHELL_COMMAND_PROCESS_DOWNLOAD_ORDERS);
    }

    /**
     * Shell Process Download Products
     *
     * @throws LocalizedException
     */
    public function shellProcessDownloadProducts()
    {
        return $this->run(self::SHELL_COMMAND_PROCESS_DOWNLOAD_PRODUCTS);
    }

    /**
     * Shell process to Uplaod Customers to EBizCharge Hub
     *
     * @return bool
     */
    public function shellProcessUploadCustomers()
    {
        return $this->run(self::SHELL_COMMAND_PROCESS_UPLOAD_CUSTOMERS);
    }

    /**
     * Shell Process To Upload Orders to EBizCharge Hub
     *
     * @return bool
     */
    public function shellProcessUploadOrders()
    {
        return $this->run(self::SHELL_COMMAND_PROCESS_UPLOAD_ORDERS);
    }

    /**
     * Shell Process to Upload Products to EBizCharge Hub
     *
     * @return bool
     */
    public function shellProcessUploadProducts()
    {
        return $this->run(self::SHELL_COMMAND_PROCESS_UPLOAD_PRODUCTS);
    }

    /**
     * Shell process to Run Subscribed Orders  from EBizCharge Hub
     *
     * @return bool
     */
    public function shellProcessRunSubscribedOrders()
    {
        return $this->run(self::SHELL_COMMAND_PROCESS_RUN_SUBSCRIBED_ORDERS);
    }

    /**
     * Fetch Recurring Payments from EBizCharge Hub
     *
     * @return bool
     */
    public function shellProcessRunToFetchRecurringPaymentsFromEBizCharge()
    {
        return $this->run(self::SHELL_COMMAND_PROCESS_RUN_FETCH_SUBSCRIBED_PAYMENTS_FROM_EBIZCHARGE);
    }
}
