<?php

namespace Crimson\CokerWV\Console\Command;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Crimson\CokerWV\Service\AssignAdminUsersRoles as AssignAdminUsersRolesService;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area;
use Magento\Framework\Console\Cli;

class AssignAdminUsersRolesCommand extends Command
{


    public function __construct(
        protected AssignAdminUsersRolesService $assignAdminUsersRolesService,
        protected AppState $appState
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this->setName('crimson:coker-wv-data:import');
        $this->setDescription('Command to trigger the import of Admin Users-Roles relations.');
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
        $output->writeln("*** Triggering Import Process ***");

        // starting process
        $result = $this->assignAdminUsersRolesService->execute();
        if (!empty($result['message'])) {
            $output->writeln($result['message']);
        }

        // final steps and message
        $final = Cli::RETURN_SUCCESS;
        $output->writeln("*** Finished!! ***");

        return $final;
    }

}
