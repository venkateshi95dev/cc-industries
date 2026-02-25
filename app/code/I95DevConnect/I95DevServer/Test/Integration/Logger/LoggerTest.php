<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Logger;

/**
 * Test case for Logs creation
 */
class LoggerTest extends \PHPUnit\Framework\TestCase
{
    public const SUCCESS = 1;
    public const ERROR = 0;
    public const CCL = 'createCustomersList';
    public const CINFO = 'customerInfo';
    public const I95EXC = 'I95EXC';
    public const CRITICAL = 'critical';
    public const ISSUE001 = "Issue Came With Log Creation";

    /**
     * @author Kavya Koona
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->scopeConfig = $objectManager->create(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        );
        $this->ioOperations = $objectManager->create(
            \Magento\Framework\Filesystem\Io\File::class
        );
        $this->date = $objectManager->create(
            \Magento\Framework\Stdlib\DateTime\DateTime::class
        );
        $this->logger = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\Logger::class
        );
    }

    /**
     * Test case for creating critical log
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_logsettings/debug 1
     * @author               Kavya Koona
     */
    public function testCreateCriticalLog()
    {
        $this->logger->createLog(self::CCL, self::CINFO, self::I95EXC, self::CRITICAL);
        $isFileExist = $this->checkLogFile();
        $this->assertEquals(self::SUCCESS, $isFileExist, self::ISSUE001);
    }

    /**
     * Check log file exist or not
     *
     * @return boolean
     * @author Kavya Koona
     */
    public function checkLogFile()
    {
        $logPath = $this->logger->getLogPath(self::I95EXC);
        return file_exists($logPath);
    }

    /**
     * Test case for creating info log
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_logsettings/debug 1
     * @author               Kavya Koona
     */
    public function testCreateInfoLog()
    {
        $this->logger->createLog(self::CCL, self::CINFO, self::I95EXC, 'info');
        $isFileExist = $this->checkLogFile();
        $this->assertEquals(self::SUCCESS, $isFileExist, self::ISSUE001);
    }

    /**
     * Test case for creating debug log
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_logsettings/debug 1
     * @author               Kavya Koona
     */
    public function testCreateDebugLog()
    {
        $this->logger->createLog(self::CCL, self::CINFO, self::I95EXC, 'debug');
        $isFileExist = $this->checkLogFile();
        $this->assertEquals(self::SUCCESS, $isFileExist, self::ISSUE001);
    }

    /**
     * Test case for creating error log
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_logsettings/debug 1
     * @author               Kavya Koona
     */
    public function testCreateErrorLog()
    {

        $this->logger->createLog(self::CCL, self::CINFO, self::I95EXC, 'error');
        $isFileExist = $this->checkLogFile();
        $this->assertEquals(self::SUCCESS, $isFileExist, self::ISSUE001);
    }

    /**
     * Test case for creating default log
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_logsettings/debug 1
     * @author               Kavya Koona
     */
    public function testCreateDefaultLog()
    {

        $this->logger->createLog(self::CCL, self::CINFO, self::I95EXC, 'default');
        $isFileExist = $this->checkLogFile();
        $this->assertEquals(self::SUCCESS, $isFileExist, self::ISSUE001);
    }

    /**
     * Test case for creating General log
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_logsettings/debug 1
     * @author               Kavya Koona
     */
    public function testCreateGeneralLog()
    {
        $this->logger->createLog(self::CCL, self::CINFO, 'general', self::CRITICAL);
        $isFileExist = $this->checkLogFile();
        $this->assertEquals(self::SUCCESS, $isFileExist, self::ISSUE001);
    }
}
