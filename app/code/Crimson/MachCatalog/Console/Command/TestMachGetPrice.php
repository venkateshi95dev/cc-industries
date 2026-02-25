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
use Crimson\MachCatalog\Model\Api\Price as PriceApi;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class TestMachGetPrice
 * @package Crimson\MachCatalog\Console\Command
 */
class TestMachGetPrice extends Command
{

    /**#@+
     * Input arguments for mode setter command
     */
    const ID_ARGUMENT = 'sku';
    const QUOTE_QTY_ARGUMENT = 'quote-qty';
    const MACH_CUSTOMER_NUMBER_ARGUMENT = 'mach-customer-number';
    /**#@-*/

    /**
     * Object manager factory
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var PriceApi
     */
    protected $priceApi;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Inject dependencies
     *
     * @param ObjectManagerInterface     $objectManager
     * @param ProductRepositoryInterface $productRepository
     * @param PriceApi                    $priceApi
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ProductRepositoryInterface $productRepository,
        PriceApi $priceApi
    ) {
        $this->objectManager = $objectManager;
        parent::__construct();
        $this->productRepository = $productRepository;
        $this->priceApi          = $priceApi;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $description = 'Test MACH GetInventory by SKU';

        $this->setName('mach:test:get_price')
            ->setDescription($description)
            ->setDefinition(
                [
                    new InputArgument(
                        self::ID_ARGUMENT,
                        InputArgument::REQUIRED,
                        'MACH Product SKU'
                    ),
                    new InputOption(
                        self::QUOTE_QTY_ARGUMENT,
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'MACH Quote Quantity',
                        1
                    ),
                    new InputOption(
                        self::MACH_CUSTOMER_NUMBER_ARGUMENT,
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'MACH Customer Number'
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
            $result         = $this->priceApi->get($product, true, true);
            $runTime        = round(microtime(true) - $startTimerLive, 4);


            $output->writeln(print_r($result->debug(), true));
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
