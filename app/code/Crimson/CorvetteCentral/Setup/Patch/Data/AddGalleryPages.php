<?php

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class AddGalleryPages implements DataPatchInterface
{
    const CMS_LAYOUT_UPDATE = 'full-width-cms-base';
    const CMS_PAGE_KEYWORDS = 'Corvette parts, Corvette accessories, Corvette restoration parts, Corvette performance parts, Corvette interior accessories, Corvette exhaust systems.';

    public function __construct(
        private readonly PageFactory $pageFactory,
        private readonly PageRepositoryInterface $pageRepository,
        private readonly Json $json,
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly StoreManagerInterface $storeManager,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
    ) {}

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();

        $jsonData = file_get_contents(__DIR__ . '/../../data/gallery_data.json');
        $pages = $this->json->unserialize($jsonData);

        foreach ($pages as $pageData) {
            $htmlContent = $this->generateHtmlContent($pageData);
            $this->createOrUpdatePage(
                $pageData['relativeUrl'],
                $pageData['title'],
                $htmlContent,
                $pageData['metaTitle'],
                $pageData['metaDescription']
            );
        }

        $this->moduleDataSetup->endSetup();
    }

    private function generateUniqueStyle($existingStyles): string
    {
        do {
            $style = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 7);
        } while (in_array($style, $existingStyles));
        return $style;
    }

    private function generateHtmlContent($pageData): string
    {
        $existingStyles = ['O1XNVO6', 'BBIXGC6', 'QP7Q48S', 'QP7Q48S'];
        $styles = [];
        $columnGroupStyles = [];

        foreach ($pageData['images'] as $image) {
            $styles[$image['src']] = [
                'column' => $this->generateUniqueStyle($existingStyles),
                'figure' => $this->generateUniqueStyle($existingStyles),
                'desktop_image' => $this->generateUniqueStyle($existingStyles),
                'mobile_image' => $this->generateUniqueStyle($existingStyles),
                'caption' => $this->generateUniqueStyle($existingStyles)
            ];
            $existingStyles = array_merge($existingStyles, array_values($styles[$image['src']]));
        }

        foreach (array_chunk($pageData['images'], 4) as $index => $imageGroup) {
            $columnGroupStyles[$index] = $this->generateUniqueStyle($existingStyles);
            $existingStyles[] = $columnGroupStyles[$index];
        }

        $html = $this->generateStyles($styles, $columnGroupStyles);
        $html .= $this->generateHtmlBody($pageData, $styles, $columnGroupStyles);

        return $html;
    }

    private function generateStyles(array $styles, array $columnGroupStyles): string
    {
        ob_start();
        ?>
        <style>
            #html-body [data-pb-style="O1XNVO6"] {
                justify-content: flex-start;
                display: flex;
                flex-direction: column;
                padding-top: 40px;
                padding-bottom: 40px
            }

            #html-body [data-pb-style="BBIXGC6"], #html-body [data-pb-style="O1XNVO6"] {
                background-position: left top;
                background-size: cover;
                background-repeat: no-repeat;
                background-attachment: scroll
            }

            #html-body [data-pb-style="BBIXGC6"] {
                justify-content: flex-start;
                display: flex;
                flex-direction: column;
                padding-bottom: 40px
            }

            #html-body [data-pb-style="QP7Q48S"] {
                display: flex;
                width: 100%
            }

            <?php foreach ($columnGroupStyles as $style): ?>
            #html-body [data-pb-style="<?= $style ?>"] {
                margin-left: -10px;
                margin-right: -10px;
                align-self: stretch;
            }
            <?php endforeach; ?>

            <?php foreach ($styles as $style): ?>
            #html-body [data-pb-style="<?= $style['column'] ?>"] {
                justify-content: flex-start;
                display: flex;
                flex-direction: column;
                background-position: left top;
                background-size: cover;
                background-repeat: no-repeat;
                background-attachment: scroll;
                width: 25%;
                padding: 10px;
                align-self: stretch;
            }
            #html-body [data-pb-style="<?= $style['figure'] ?>"] {
                border-style: none;
            }
            #html-body [data-pb-style="<?= $style['desktop_image'] ?>"] {
                max-width: 100%;
                height: auto;
            }
            #html-body [data-pb-style="<?= $style['mobile_image'] ?>"] {
                max-width: 100%;
                height: auto;
            }
            #html-body [data-pb-style="<?= $style['caption'] ?>"] {
                border-style: none;
            }
            <?php endforeach; ?>
        </style>

        <?php
        return ob_get_clean();
    }

    private function generateHtmlBody(array $pageData, array $styles, array $columnGroupStyles): string
    {
        ob_start();
        ?>

        <div data-content-type="row" data-appearance="contained" data-element="main">
            <div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="O1XNVO6">
                <div data-content-type="text" data-appearance="default" data-element="main"><p>
                        <a href="/gallery">← back to gallery</a></p>
                    <h1><?= $pageData['title'] ?></h1>
                    <p>If you are a Corvette Central customer and would like your Corvette featured in our customer gallery, just post a high-resolution, unaltered photo to our
                        <a href="http://facebook.com/CorvetteCentral/" target="_blank" rel="noopener">Facebook</a>,
                        <a href="http://twitter.com/corvettecentral/" target="_blank" rel="noopener">Twitter</a> or
                        <a href="https://plus.google.com/+corvettecentral/posts" target="_blank" rel="noopener">Google Plus</a> accounts, along with a few words about your car! (Photo must not include people; include year of car, engine/drivetrain details, and any other modifications).
                    </p></div>
            </div>
        </div>

        <div data-content-type="row" data-appearance="contained" data-element="main">
            <div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="BBIXGC6">
                <?php foreach (array_chunk($pageData['images'], 4) as $index => $imageGroup): ?>
                    <div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="<?= $columnGroupStyles[$index] ?>">
                        <div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="QP7Q48S">
                            <?php foreach ($imageGroup as $image): ?>
                                <?php $style = $styles[$image['src']]; ?>
                                <div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="<?= $style['column'] ?>">
                                    <figure class="w-full" data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="<?= $style['figure'] ?>">
                                        <a href="<?= $image['href'] ?>" target="<?= $image['target'] ?>" data-link-type="default" title="<?= $image['title'] ?>" data-element="link">
                                            <img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/<?= $image['src'] ?>}}" alt="<?= $image['alt'] ?>" title="<?= $image['title'] ?>" data-element="desktop_image" data-pb-style="<?= $style['desktop_image'] ?>">
                                            <img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/<?= $image['src'] ?>}}" alt="<?= $image['alt'] ?>" title="<?= $image['title'] ?>" data-element="mobile_image" data-pb-style="<?= $style['mobile_image'] ?>">
                                        </a>
                                        <figcaption data-element="caption" data-pb-style="<?= $style['caption'] ?>"><?= $image['caption'] ?></figcaption>
                                    </figure>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function createOrUpdatePage(
        string $identifier,
        string $title,
        string $content,
        string $metaTitle,
        string $metaDescription,
    ): void {
        $store = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE);
        $storeId = (int)$store->getId();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('identifier', $identifier)
            ->addFilter('store_id', $storeId)
            ->create();

        $pages = $this->pageRepository->getList($searchCriteria)->getItems();

        if (!empty($pages)) {
            $page = reset($pages);
        } else {
            $page = $this->pageFactory->create();
            $page->setIdentifier($identifier);
        }

        $page->setTitle($title)
            ->setPageLayout(self::CMS_LAYOUT_UPDATE)
            ->setMetaTitle($metaTitle)
            ->setMetaKeywords(self::CMS_PAGE_KEYWORDS)
            ->setMetaDescription($metaDescription)
            ->setIsActive(1)
            ->setContent($content)
            ->setStoreId($storeId);

        $this->pageRepository->save($page);
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
