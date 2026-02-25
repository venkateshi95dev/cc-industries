<?php

namespace Crimson\CorvetteCentralImport\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Crimson\CorvetteCentralImport\Service\ImportCCProductPackages as ImportCCProductsService;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area;
use Magento\Framework\Console\Cli;

class ImportPackages extends Command
{

    public function __construct(
        protected ImportCCProductsService $importCCProductsService,
        protected AppState $appState
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('crimson:cc-packages:import');
        $this->setDescription('Command to import CC products packages.');
        parent::configure();
    }

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
        $final = Cli::RETURN_FAILURE;
        $result = $this->importCCProductsService->execute();
        if (!empty($result['message'])) {
            $output->writeln($result['message']);
        }

        if (!empty($result["status"]) && $result["status"] == ImportCCProductsService::SUCCESS_CODE) {
            $final = Cli::RETURN_SUCCESS;
        }

        // final message
        $output->writeln("*** Finished!! ***");

        return $final;
    }

}
