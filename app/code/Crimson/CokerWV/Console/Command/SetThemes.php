<?php

namespace Crimson\CokerWV\Console\Command;

use Crimson\CokerWV\Service\SetThemesConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area;
use Magento\Framework\Console\Cli;

class SetThemes extends Command
{


    public function __construct(
        protected SetThemesConfig $setThemesConfig,
        protected AppState $appState
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('crimson:coker-wv-themes:set');
        $this->setDescription('Command to set Coker and WV themes in the config.');
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

        // initial message
        $output->writeln("*** Triggering Process ***");

        // starting process
        $result = $this->setThemesConfig->execute();
        if (!empty($result['message'])) {
            $output->writeln($result['message']);
        }

        // final steps and message
        $final = Cli::RETURN_SUCCESS;
        $output->writeln("*** Finished!! ***");

        return $final;
    }

}
