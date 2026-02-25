<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Console;

use I95DevConnect\MessageQueue\Console\CronBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;

/**
 * Process IBMQ data to Magento
 */
class Cron extends CronBase
{
    /**
     * Configure command name and description
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('i95devcron:run');
        $this->setDescription('Process the data of IBMQ');
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
            $this->writeCronOutput("IBMQ Cron Started ");
            $response = $this->executeCron(
                "i95devCron.php/",
                "rest/corvette_central_store/V1/ReverseSyncService/?methodName=syncMQtoMagento"
            );
            $this->writeCronOutput("IBMQ Cron End ", $response);
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
