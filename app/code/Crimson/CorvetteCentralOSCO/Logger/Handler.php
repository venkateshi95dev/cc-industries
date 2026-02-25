<?php

namespace Crimson\CorvetteCentralOSCO\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

/**
 * Class Handler
 * @package Crimson\CorvetteCentralOSCO\Logger
 */
class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/osco_api.log';
}
