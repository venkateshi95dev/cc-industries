<?php

namespace Crimson\CokerWV\Console\Command;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Crimson\CokerWV\Service\ImportNewsletterSubscribers;
use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportNewsletterSubscribersCommand extends Command
{


    public function __construct(
        protected AppState $appState,
        protected ImportNewsletterSubscribers $importNewsletterSubscribers
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('crimson:coker-wv-newsletter-subscribers:import');
        $this->setDescription('Command to trigger the import of Newsletter Subscribers.');
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
        $output->writeln("*** Triggering Import Process ***");

        // starting process
        $result = $this->importNewsletterSubscribers->execute(CokerStoreInterface::COKER_STORE_CODE);
        if (!empty($result['message'])) {
            $output->writeln($result['message']);
        }

        $result = $this->importNewsletterSubscribers->execute(CokerStoreInterface::COKER_DEFAULT_STORE_CODE);
        if (!empty($result['message'])) {
            $output->writeln($result['message']);
        }

        $result = $this->importNewsletterSubscribers->execute(WVStoreInterface::WV_STORE_CODE);
        if (!empty($result['message'])) {
            $output->writeln($result['message']);
        }

        // final steps and message
        $final = Cli::RETURN_SUCCESS;
        $output->writeln("*** Finished!! ***");

        return $final;
    }
}
