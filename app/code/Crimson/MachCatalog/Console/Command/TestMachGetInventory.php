<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/11/2019 5:04 PM
 * @brief
 */

namespace Crimson\MachCatalog\Console\Command;

use Crimson\MachCatalog\Model\Api\Catalog;
use Crimson\MachCatalog\Model\Api\Inventory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class TestMachGetInventory
 * @package Crimson\MachCatalog\Console\Command
 */
class TestMachGetInventory extends Command
{

    /**#@+
     * Input arguments for mode setter command
     */
    const ID_ARGUMENT = 'sku';
    /**#@-*/

    /**
     * Object manager factory
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var Inventory
     */
    protected $inventoryApi;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Inject dependencies
     *
     * @param ObjectManagerInterface     $objectManager
     * @param ProductRepositoryInterface $productRepository
     * @param Inventory                  $inventoryApi
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ProductRepositoryInterface $productRepository,
        Inventory $inventoryApi
    ) {
        $this->objectManager = $objectManager;
        parent::__construct();
        $this->productRepository = $productRepository;
        $this->inventoryApi      = $inventoryApi;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $description = 'Test MACH GetInventory by SKU';

        $this->setName('mach:test:get_inventory')
            ->setDescription($description)
            ->setDefinition(
                [
                    new InputArgument(
                        self::ID_ARGUMENT,
                        InputArgument::REQUIRED,
                        'MACH Product SKU'
                    )
                ]
            );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $sku     = $input->getArgument(self::ID_ARGUMENT);
            $product = $this->productRepository->get($sku);

            $startTimerLive = microtime(true);
            $result         = $this->inventoryApi->get($product, true, true);
            $runTime        = round(microtime(true) - $startTimerLive, 4);

            $output->writeln(print_r($result->debug(), true));
            if ($result->getErrorNumber()) {
                $output->writeln('<error>' . $result->getErrorNumber() . '</error>');
            }
            if ($result->getErrorMessage()) {
                $output->writeln('<error>' . $result->getErrorMessage() . '</error>');
            }

            $output->writeln('Import Run Time (seconds): ' . $runTime);

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }

            // we must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_FAILURE;
        }
    }
}
