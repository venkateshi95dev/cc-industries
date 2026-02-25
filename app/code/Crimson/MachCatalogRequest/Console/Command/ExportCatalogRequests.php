<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/19/2019 10:59 AM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Console\Command;

use Crimson\MachCatalogRequest\Model\Service\CatalogRequestExport;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ExportCatalogRequests
 * @package Crimson\MachCatalogRequest\Console\Command
 */
class ExportCatalogRequests extends Command
{
    /**
     * Object manager factory
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var CatalogRequestExport
     */
    protected $catalogRequestExport;

    /**
     * Inject dependencies
     *
     * @param ObjectManagerInterface $objectManager
     * @param CatalogRequestExport   $catalogRequestExport
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        CatalogRequestExport $catalogRequestExport
    ) {
        $this->objectManager = $objectManager;
        parent::__construct();
        $this->catalogRequestExport = $catalogRequestExport;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $description = 'MACH Export Unprocessed Catalog Requests';

        $this->setName('mach:catalog_request:export')
            ->setDescription($description);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $startTimerLive = microtime(true);
            $result         = $this->catalogRequestExport->execute();
            $runTime        = round(microtime(true) - $startTimerLive, 4);

            $output->writeln(__('Exported %1 catalog requests in %2 seconds.', $result, $runTime));

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
