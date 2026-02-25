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
 * Interface CommandsCliModelInterface
 *
 * Commands Cli Model Data Interface
 */
interface CommandsCliModelInterface
{
    /**
     * Input Name
     *
     * @const  INPUT_ARGUMENT_NAME
     */
    public const INPUT_ARGUMENT_NAME = "command";

    /**
     * Input Argument Shortcut Name
     *
     * @const: INPUT_ARGUMENT_SHORTCUT_NAME
     */
    public const INPUT_ARGUMENT_SHORTCUT_NAME = "cmd";

    /**
     * Second Input Name
     *
     * @const  INPUT_SECOND_ARGUMENT_NAME
     */
    public const INPUT_SECOND_ARGUMENT_NAME = "order";

    /**
     * Second Input Name ShortCut
     *
     * @const: INPUT_SECOND_ARGUMENT_SHORTCUT_NAME
     */
    public const INPUT_SECOND_ARGUMENT_SHORTCUT_NAME = "o";

    /**
     *
     * Type Customer ID
     *
     * @const: INPUT_ARGUMENT_TYPE_CUSTOMER_ID
     */
    public const INPUT_ARGUMENT_TYPE_CUSTOMER_ID = "customer-id";

    /**
     * Type Customner Id Shortcut
     *
     * @const: INPUT_ARGUMENT_TYPE_CUSTOMER_ID_SHORTCUT
     */
    public const INPUT_ARGUMENT_TYPE_CUSTOMER_ID_SHORTCUT = "cid";

    /**
     * Input Option
     *
     * @const INPUT_ARGUMENT_OPTION
     */
    public const INPUT_ARGUMENT_OPTION = "option";

    /**
     * @const COMMAND_CONFIG
     */
    public const COMMAND_CONFIG = 'config';

    /**
     * Command Download Customers
     *
     * @const COMMAND_DOWNLOAD_CUSTOMERS
     */
    public const COMMAND_DOWNLOAD_CUSTOMERS = 'download-customers';

    /**
     * Command Download Items
     *
     * @const COMMAND_DOWNLOAD_ITEMS
     */
    public const COMMAND_DOWNLOAD_ITEMS = 'download-items';

    /**
     * Command Download Orders
     *
     * @const COMMAND_DOWNLOAD_ORDERS
     */
    public const COMMAND_DOWNLOAD_ORDERS = 'download-orders';

    /**
     * Command Upload Customers
     *
     * @const COMMAND_UPLOAD_CUSTOMERS
     */
    public const COMMAND_UPLOAD_CUSTOMERS = 'upload-customers';

    /**
     * Command Upload Items
     *
     * @const COMMAND_UPLOAD_ITEMS
     */
    public const COMMAND_UPLOAD_ITEMS = 'upload-items';

    /**
     * Command Upload Orders
     *
     * @const COMMAND_UPLOAD_ORDERS
     */
    public const COMMAND_UPLOAD_ORDERS = 'upload-orders';

    /**
     * Command Re-Order
     *
     * @const: COMMAND_RE_ORDER_VIA_CRON
     */
    public const COMMAND_RE_ORDER_VIA_CRON = 're-order';

    /**
     * Command Run Cron Low Stock
     *
     * @const: COMMAND_LOW_STOCK_NOTIFICATIONS
     */
    public const COMMAND_LOW_STOCK_NOTIFICATIONSL_VIA_CRON = 'send-low-stock-notifications';

    /**
     * Manual Run Recurring Orders
     *
     * @const: COMMAND_MANUAL_RUN_RECURRING_ORDERS_CRON
     */
    public const COMMAND_MANUAL_RUN_RECURRING_ORDERS_CRON = 'manually-run-recurring-orders';

    /**
     * Manual Download Payments
     *
     * @const: COMMAND_MANUAL_DOWNLOAD_PAYMENTS_CRON
     */
    public const COMMAND_MANUAL_DOWNLOAD_PAYMENTS_CRON = 'manually-download-payments';

    /**
     * Sync Items Stock CRON
     */
    public const COMMAND_MANUAL_SYNC_ITEMS_STOCK_CRON = 'sync-items-stock';

    /**
     * Update Customer Ebizcharge
     *
     * @const: COMMAND_UPDATE_CUSTOMER_AT_EBIZCHARGE
     */
    public const COMMAND_UPDATE_CUSTOMER_AT_EBIZCHARGE = 'update-customer-ebizcharge';

    /**
     * Ebizcharge Cli Command
     *
     * @const: EBIZCHARGE_CLI_COMMAND
     */
    public const EBIZCHARGE_CLI_COMMAND = 'ebizcharge:cli';

    /**
     * Ebizcharge Command
     *
     * @const: EBIZCHARGE_COMMAND
     */
    public const EBIZCHARGE_COMMAND = '--command';

    /**
     * Error log csv file for download customer
     *
     * @const  DOWNLOAD_CUSTOMER_ERROR_LOG_FILE
     */
    public const DOWNLOAD_CUSTOMER_ERROR_LOG_FILE = BP . '/var/log/download_customer_logs.csv';

    /**
     * Error log csv file for download products
     *
     * @const  DOWNLOAD_ITEMS_ERROR_LOG_FILE
     */
    public const DOWNLOAD_ITEMS_ERROR_LOG_FILE = BP . '/var/log/download_items_logs.csv';

    /**
     * Error log csv file for download orders
     *
     * @const  DOWNLOAD_ORDERS_ERROR_LOG_FILE
     */
    public const DOWNLOAD_ORDERS_ERROR_LOG_FILE = BP . '/var/log/download_orders_logs.csv';
}
