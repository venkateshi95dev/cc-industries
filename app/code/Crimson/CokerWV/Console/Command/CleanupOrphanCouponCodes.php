<?php

namespace Crimson\CokerWV\Console\Command;

use Crimson\CokerWV\Service\CleanupCouponCodes as Service;
use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupOrphanCouponCodes extends Command
{

    public function __construct(
        protected Service $importService,
        protected AppState $appState
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('crimson:coker-wv-promotion:cleanup-coupon-code');
        $this->setDescription('Command to clean up un-used coupon codes.');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        // setting Area Code
        try {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
        }

        // initial message
        $output->writeln("Triggering cleanup coupon");

        // starting process
        $this->importService->execute();
        if (!empty($result['message'])) {
            $output->writeln($result['message']);
        }

        // final steps and message
        $final = Cli::RETURN_SUCCESS;
        $output->writeln("*** Finished!! ***");

        return $final;
    }
}
