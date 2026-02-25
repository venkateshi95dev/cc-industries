<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Console;

use I95DevConnect\MessageQueue\Console\CronBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;

/**
 * Pull response from cloud
 */
class PullResponseCron extends CronBase
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('i95devcron:pull-response');
        $this->setDescription('i95Dev Pull Response Cron');
        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->writeCronOutput("Pull Response Cron Started ");
            $response = $this->executeCron(
                "i95devPullResponseCron.php/",
                "rest/corvette_central_store/V1/CloudConnect/PullResponse/?methodName=syncData"
            );

            $this->writeCronOutput("Pull Response Cron End ", $response);
            return Cli::RETURN_SUCCESS;
        } catch (LocalizedException $ex) {
            $output->writeln('<error>' . $ex->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($ex->getTraceAsString());
            }
            return Cli::RETURN_FAILURE;
        }
    }
}
