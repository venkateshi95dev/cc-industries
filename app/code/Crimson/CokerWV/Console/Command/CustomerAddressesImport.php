<?php

namespace Crimson\CokerWV\Console\Command;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Crimson\CokerWV\Service\ImportCmsPages as ImportCmsPagesService;
use Crimson\CokerWV\Service\ImportCustomerAddresses;
use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CustomerAddressesImport extends Command
{

    public function __construct(
        protected ImportCustomerAddresses $importCustomerAddressesService,
        protected AppState $appState
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('crimson:coker-wv-customer-addresses:import');
        $this->setDescription('Command to trigger the import of CMS Pages content.');
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

        //removing Coker WV addresses
        $output->writeln("Removing Coker and WV addresses.");
        $this->importCustomerAddressesService->removeCokerAndWVAddressesBeforeImport();
        $output->writeln("Addresses removed.");

        // starting process coker_tire
        $result = $this->importCustomerAddressesService->execute(CokerStoreInterface::COKER_WEBSITE_CODE);
        if (!empty($result['message'])) {
            $output->writeln($result['message']);
        }

        // starting process wv
        $result = $this->importCustomerAddressesService->execute(WVStoreInterface::WV_WEBSITE_CODE);
        if (!empty($result['message'])) {
            $output->writeln($result['message']);
        }

        // final steps and message
        $final = Cli::RETURN_SUCCESS;
        $output->writeln("*** Finished!! ***");

        return $final;
    }
}
