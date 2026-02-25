<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Api;

/**
 * Interface for i95dev Log creation.
 */
interface LoggerInterface
{
    public const MAGLOGNAME = 'MagentoToERP';
    public const ERPLOGNAME = 'ERPToMagento';
    public const I95EXC = 'i95devApiException';
    public const GENERIC = 'Generic';
    public const MSGLOGNAME = 'MessageToMagento';
    public const CRITICAL = 'critical';
    public const INFO = 'info';

    /**
     * Create log function
     *
     * @param string $logArea
     * @param string $message
     * @param string $logName
     * @param string $logType
     * @return void
     */
    public function createLog($logArea, $message, $logName, $logType);
}
