<?php
namespace Crimson\AutoInvoice\Cron;

use Crimson\AutoInvoice\Model\Creater;
use Crimson\AutoInvoice\Model\Logger;
use Magento\Framework\Exception\LocalizedException;

class CreateInvoice
{
        protected $creater;
        protected $logger;

        public function __construct(
                Creater $creater,
                Logger $logger
        ){
                $this->logger = $logger;
                $this->creater = $creater;
        }

        public function execute()
        {
                try {
                        $this->creater->execute();
                }catch(LocalizedException $e){
                        $this->logger->error($e->getMessage());
                }catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                }

        }
}
