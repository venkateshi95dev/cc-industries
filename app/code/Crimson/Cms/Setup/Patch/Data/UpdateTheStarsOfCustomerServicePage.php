<?php

declare(strict_types=1);

namespace Crimson\Cms\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Psr\Log\LoggerInterface;
use Exception;

class UpdateTheStarsOfCustomerServicePage implements DataPatchInterface
{
    const PAGE_ID = "the-stars-of-customer-service";
    const DATA_PATH = "/../../data/page/the-stars-of-customer-service.html";

    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private PageRepositoryInterface  $pageRepository,
        private LoggerInterface  $logger
    ) {
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies() : array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases() : array
    {
        return [];
    }

    /**
     * @return void
     */
    public function apply() : void
    {
        $this->moduleDataSetup->startSetup();

        $filePath = __DIR__ . self::DATA_PATH;

        try {
            $content = $this->getHtmlContent($filePath);
            $page = $this->pageRepository->getById(self::PAGE_ID);
            $page->setContent($content);
            $this->pageRepository->save($page);
        } catch (Exception $e) {
            $this->logger->error("Can't update 'The Stars of Customer Service' CMS page.");
            $this->logger->error($e->getMessage());
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @param $path
     * @return string
     * @throws Exception
     */
    protected function getHtmlContent($path) : string
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new Exception("File not found `$path`");
        }
        return (string) file_get_contents($path);
    }
}
