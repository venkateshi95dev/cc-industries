<?php
declare(strict_types=1);

namespace Crimson\BuyNow\Console\Command;

use Crimson\BuyNow\Service\Exporter;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ExportFeeds extends Command
{

    public function __construct(
        protected Exporter $exporter,
        protected Emulation $emulation,
        protected StoreRepositoryInterface       $storeManager,
        protected State $appState
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('crimson:buynow:export');
        $this->setDescription('Export BuyNow Product Feeds');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // setting Area Code
        $this->appState->emulateAreaCode(Area::AREA_FRONTEND,function (){
            $store = $this->storeManager->get(CokerStoreInterface::COKER_STORE_CODE);
            $this->emulation->startEnvironmentEmulation($store->getId(), Area::AREA_FRONTEND, true);
            try {
                $this->exporter->execute();
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            $this->emulation->stopEnvironmentEmulation();
        });

    }

}
