<?php

declare(strict_types=1);

namespace Crimson\Sales\Setup\Patch\Data;

use Amasty\Customform\Api\FormRepositoryInterface;
use Exception;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Psr\Log\LoggerInterface;

class CreateStartAReturnPage implements DataPatchInterface
{
    public function __construct(
        protected ModuleDataSetupInterface $moduleDataSetup,
        protected PageRepositoryInterface $pageRepository,
        protected FormRepositoryInterface $formRepository,
        protected PageFactory $pageFactory,
        protected StoreRepositoryInterface $storeRepository,
        protected State $appState,
        protected LoggerInterface $logger
    ) {}

    /**
     * @return string[]
     */
    public static function getDependencies() : array
    {
        return [CreateReturnsForm::class];
    }

    /**
     * @return string[]
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

        try {
            $this->appState->setAreaCode(Area::AREA_FRONTEND);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        try {
            $form = $this->formRepository->getByFormCode(CreateReturnsForm::FORM_CODE);
            $page = $this->pageFactory->create();
            $store = $this->storeRepository->get('default');

            if (!$form || !$form->getFormId()) {
                throw new Exception('Form not found!');
            }

            $pageContent = $this->getPageContent((int)$form->getFormId());

            $page->setIdentifier('start-a-return');
            $page->setTitle('Start a Return');
            $page->setContent($pageContent);
            $page->setPageLayout('1column');
            $page->setIsActive(1);
            $page->setStores([$store->getId()]);

            $this->pageRepository->save($page);

        } catch (Exception $e) {
            $this->logger->error("Wasn't able to apply Data Patch 'CreateStartAReturnPage'.");
            $this->logger->error($e->getMessage());
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @param int $formId
     * @return string
     */
    protected function getPageContent(int $formId) : string
    {
        return <<<CONTENT
        <style>#html-body [data-pb-style=TX6BNRS]{
        justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll
        }</style><div data-content-type="row" data-appearance="contained" data-element="main">
        <div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="TX6BNRS">
        <h1>Start a Return</h1>
        <div data-content-type="html" data-appearance="default" data-element="main">
        {{widget type="Amasty\Customform\Block\Init" template="Amasty_Customform::init.phtml" form_id="{$formId}"}}
        </div></div></div>
        CONTENT;
    }
}
