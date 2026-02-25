<?php

namespace Crimson\MachInventoryImport\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Crimson\MachInventoryImport\Service\ImportMachInventory as ImportMachInventoryService;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area;
use Magento\Framework\Console\Cli;

/**
 * Class ImportMachInventory
 * @package Crimson\MachInventoryImport\Console\Command
 */
class ImportMachInventory extends Command
{

    /**
     * @var ImportMachInventoryService
     */
    protected $importMachInventory;

    /**
     * @var AppState
     */
    protected $appState;


    public function __construct(
        ImportMachInventoryService $importMachInventory,
        AppState $appState
    ) {
        parent::__construct();
        $this->appState = $appState;
        $this->importMachInventory = $importMachInventory;
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setName('crimson:mach-inventory:import');
        $this->setDescription('Command to trigger the Mach Inventory import process manually.');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        // setting Area Code
        try {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
        }

        // initial message
        $output->writeln("*** Triggering Mach Inventory Import Process ***");

        // starting process
        $final = Cli::RETURN_FAILURE;
        $result = $this->importMachInventory->execute();
        if (!empty($result['message'])) {
            $output->writeln($result['message']);
        }

        if (!empty($result["status"]) && $result["status"] == ImportMachInventoryService::SUCCESS_CODE) {
            $final = Cli::RETURN_SUCCESS;
        }

        // final message
        $output->writeln("*** Finished!! ***");

        return $final;
    }

}
