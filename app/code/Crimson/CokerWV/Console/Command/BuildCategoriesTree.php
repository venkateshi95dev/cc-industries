<?php

namespace Crimson\CokerWV\Console\Command;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Crimson\CokerWV\Service\BuildCokerCategoriesTree;
use Crimson\CokerWV\Service\BuildWVCategoriesTree;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area;
use Magento\Framework\Console\Cli;

class BuildCategoriesTree extends Command
{


    public function __construct(
        protected BuildWVCategoriesTree              $buildWVCategoriesTree,
        protected BuildCokerCategoriesTree           $buildCokerCategoriesTree,
        protected AppState                           $appState,
        protected \Magento\Framework\Registry $registry
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('crimson:coker-wv-data:import-categories-tree');
        $this->setDescription('Command to trigger the import of Categories.');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        // setting Area Code
        try {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
        }

        if (!$this->registry->registry('isSecureArea'))
            $this->registry->register('isSecureArea', true);
        // initial message
        $output->writeln("*** Triggering Import Process ***");

        // starting process
        $this->buildCokerCategoriesTree->execute();
        $this->buildWVCategoriesTree->execute();
        // final steps and message
        $final = Cli::RETURN_SUCCESS;
        $output->writeln("*** Finished!! ***");

        return $final;
    }

}
