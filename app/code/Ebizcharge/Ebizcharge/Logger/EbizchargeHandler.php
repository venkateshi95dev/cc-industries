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

namespace Ebizcharge\Ebizcharge\Logger;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;

/**
 * Ebizcharge Log Handler class
 *
 * Class EbizchargeHandler
 */
class EbizchargeHandler extends Base
{
    /**
     * Directory Seperator
     *
     * @const: DS
     */
    public const DS = '/';

    /**
     * Default Logs Directory
     *
     * @const: LOG_DIR
     */
    public const LOG_DIR = '/var/log';

    /**
     * Default Log File Name
     *
     * @const: DEFAULT_LOG_FILE_NAME
     */
    public const DEFAULT_LOG_FILE_NAME = 'ebizcharge.log';

    /**
     * Cron log file name for ebizcharge
     *
     * @const: CRON_LOG_FILE_NAME
     */
    public const CRON_LOG_FILE_NAME = 'ebizcron.log';

    /**
     * System Config Payment Gateway is Active
     */
    public const SYSTEM_CONFIG_EBIZCHARGE_ACTIVE = 'payment/ebizcharge_ebizcharge/active';

    /**
     * Logs Enabled or Disabled
     *
     * @const: SYSTEM_CONFIG_ENABLE_LOGS
     */
    public const SYSTEM_CONFIG_ENABLE_LOGS = 'payment/ebizcharge_ebizcharge/enable_logs';

    /**
     * System config Logs File
     *
     * @const: SYSTEM_CONFIG_LOGS_FILE
     */
    //const SYSTEM_CONFIG_LOGS_FILE = 'payment/ebizcharge_ebizcharge/logs_file';

    /**
     * Scope Configuration interface
     *
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $_sopeConfig;

    /**
     * Main Handler Constructor
     *
     * @param DriverInterface $filesystem
     * @param ScopeConfigInterface $scopeConfig
     * @param string|null $filePath
     * @param string|null $fileName
     * @throws Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        ScopeConfigInterface $scopeConfig,
        string $filePath = null,
        string $fileName = null
    ) {
        /** @var _sopeConfig */
        $this->_sopeConfig = $scopeConfig;
        /** @var fileName */
        $this->fileName = $this->_getLoggerFileName();

        parent::__construct($filesystem, $filePath, $fileName);
    }

    /**
     * Get Logger Filename From System Config or Default
     *
     * @return string
     */
    protected function _getLoggerFileName(): string
    {
        /** @var  $defaultLogFilePath */
        $defaultLogFilePath = self::LOG_DIR;

        /** @var  $defaultfileName */
        $defaultfileName = self::DEFAULT_LOG_FILE_NAME;

        if ($this->isBackendProcess()) {
            $defaultfileName = self::CRON_LOG_FILE_NAME;
        }

        /**
         * Default Log filename
         * @var $logFile
         */
        $logFile = (string)$defaultLogFilePath . self::DS . $defaultfileName;
        /**
         * Get user Log file from system config
         *
         * @var  $userConfigFile
         */
        /*$userConfigFile = $this->_sopeConfig->getValue(self::SYSTEM_CONFIG_LOGS_FILE, ScopeInterface::SCOPE_STORE);

        if ($userConfigFile !== '' && !$this->isBackendProcess()) {
            $logFile = (string)$defaultLogFilePath . self::DS . $userConfigFile;
        }*/

        return $logFile;
    }

    /**
     * Check if request is a backend process Cron|CLI
     *
     * @return bool
     * @phpcs:disable
     */
    private function isBackendProcess(): bool
    {
        if (defined('STDIN')) {
            return true;
        }

        if (php_sapi_name() === 'cli') {
            return true;
        }

        if (array_key_exists('SHELL', $_ENV)) {
            return true;
        }

        if (array_key_exists('SHELL_VERBOSITY', $_ENV)) {
            return true;
        }

        if (empty($_SERVER['REMOTE_ADDR']) &&
            !isset($_SERVER['HTTP_USER_AGENT']) &&
            count($_SERVER['argv']) > 0) {
            return true;
        }

        if (!array_key_exists('REQUEST_METHOD', $_SERVER)) {
            return true;
        }

        return false;
    }
    // phpcs:enable
}
