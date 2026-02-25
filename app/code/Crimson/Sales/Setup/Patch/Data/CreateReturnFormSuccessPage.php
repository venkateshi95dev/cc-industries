<?php

declare(strict_types=1);

namespace Crimson\Sales\Setup\Patch\Data;

use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

class CreateReturnFormSuccessPage implements DataPatchInterface
{
    const IDENTIFIER = 'returns-form-success';

    public function __construct(
        private PageRepositoryInterface $pageRepository,
        private PageInterfaceFactory $pageFactory,
        private ModuleDataSetupInterface $moduleDataSetup,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     */
    public function apply() : void
    {
        $this->moduleDataSetup->startSetup();

        try {
            // Check if page already exists
            $this->pageRepository->getById(self::IDENTIFIER);
        } catch (LocalizedException $e) {
            // Page doesn't exist, create it
            $pageData = [
                'title' => 'Return Form Success',
                'identifier' => self::IDENTIFIER,
                'content' => $this->getPageContent(),
                'is_active' => 1,
                'page_layout' => '1column',
                'stores' => [Store::DEFAULT_STORE_ID],
            ];

            try {
                $page = $this->pageFactory->create();
                $page->setData($pageData);
                $this->pageRepository->save($page);
            } catch (\Exception $e) {
                $this->logger->error("Wasn't able to create the page " . self::IDENTIFIER);
                $this->logger->error($e->getMessage());
            }
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @return string
     */
    protected function getPageContent() : string
    {
        return <<<CONTENT
        <style>
            #html-body [data-pb-style=QY72QY4]{
            justify-content:flex-start;
            display:flex;
            flex-direction:column;
            background-position:left top;
            background-size:cover;
            background-repeat:no-repeat;
            background-attachment:scroll
            }
        </style>
        <div data-content-type="row" data-appearance="contained" data-element="main">
            <div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="QY72QY4">
                <div data-content-type="text" data-appearance="default" data-element="main">
                    <p id="BWIO9JW" style="text-align: left;">Thank you for completing the return request for your recent purchase from Zip Corvette. Your satisfaction is our top priority. Our team is currently reviewing your request and we will contact you soon to complete the return process. There is nothing else you need to do right now; we will be in touch shortly.<br>Zip: Corvettes are all we do.</p>
                </div>
            </div>
        </div>

CONTENT;
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
