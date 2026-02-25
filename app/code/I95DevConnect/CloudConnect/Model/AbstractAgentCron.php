<?php

namespace I95DevConnect\CloudConnect\Model;

use I95DevConnect\CloudConnect\Helper\Data as CloudHelper;
use Magento\Framework\Exception\LocalizedException;
use I95DevConnect\CloudConnect\Model\LoggerFactory;

/**
 * Class AbstractAgentCron
 * @package I95DevConnect\CloudConnect\Model
 */
abstract class AbstractAgentCron
{
    /**
     * @var CloudHelper|null
     */
    protected $cloudHelper = null;
    /**
     * @var string
     */
    protected $logFilename = '';
    /**
     * @var string
     */
    protected $schedulerType = '';
    /**
     * @var Service|null
     */
    public $service = null;
    /**
     * @var \I95DevConnect\CloudConnect\Model\LoggerFactory
     */
    public $logger;

    /**
     * AbstractAgentCron constructor.
     * @param CloudHelper $cloudHelper
     * @param Service $service
     * @param \I95DevConnect\CloudConnect\Model\LoggerFactory $logger
     */
    public function __construct(
        CloudHelper $cloudHelper,
        Service $service,
        LoggerFactory $logger
    ) {
        $this->cloudHelper = $cloudHelper;
        $this->service = $service;
        $this->logger = $logger;
    }

    /**
     * Start cron process
     *
     * @return bool|string
     */
    protected function startCronProcess()
    {
        try {
            if ($scheduler = $this->getSchedulerDetails()) {
                $this->initiateJob(
                    $scheduler['schedulerId'],
                    $scheduler['schedulerData']
                );
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                $this->logFilename,
                $ex->getMessage(),
                Logger::EXCEPTION,
                Logger::CRITICAL
            );
            return $ex->getMessage();
        }
        return true;
    }

    /**
     * Abstract intiatejob
     *
     * @param int $schedulerId
     * @param array $schedulerData
     * @throws LocalizedException
     */
    abstract protected function initiateJob($schedulerId, $schedulerData);

    /**
     * Get shedulerdetails
     *
     * @return array|false
     * @throws LocalizedException
     */
    protected function getSchedulerDetails()
    {
        return $this->cloudHelper->syncData($this->logFilename, $this->schedulerType, $this->service);
    }
}
