<?php

namespace Crimson\CokerWV\Console\Command;

use Crimson\CokerWV\Service\ImportCartPriceRuleCoupons as ImportCartPriceRuleCoupons;
use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCouponCodes extends Command
{

    public function __construct(
        protected ImportCartPriceRuleCoupons $importService,
        protected AppState $appState
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('crimson:coker-wv-promotion:import-coupon-code');
        $this->setDescription('Command to trigger the import of CMS Pages content.');
        $options = [
            new InputOption(
                'name',
                '-r',
                InputOption::VALUE_REQUIRED,
                'Cart price rule name'
            ),
            new InputOption(
                'filename',
                '-f',
                InputOption::VALUE_REQUIRED,
                'CSV file for import'
            ),
        ];
        $this->setDefinition($options);
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

        $name = $input->getOption('name');
        $filename = $input->getOption('filename');
        // initial message
        $output->writeln("Triggering Import Process for rule: ". $name);

        // starting process
        $this->importService->execute($name, $filename);
        if (!empty($result['message'])) {
            $output->writeln($result['message']);
        }

        // final steps and message
        $final = Cli::RETURN_SUCCESS;
        $output->writeln("*** Finished!! ***");

        return $final;
    }
}
