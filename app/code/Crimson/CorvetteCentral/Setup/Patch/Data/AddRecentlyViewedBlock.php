<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Exception;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Store\Model\StoreManagerInterface;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;

class AddRecentlyViewedBlock implements DataPatchInterface
{
    const BLOCK_NAME = 'Recently Viewed Products';
    const BLOCK_ID = 'recently_viewed_products';

    /**
     * Create Footer Details block constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param BlockRepositoryInterface $blockRepository
     * @param BlockFactory $blockFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly BlockFactory             $blockFactory,
        private readonly StoreManagerInterface    $storeManager
    ) {
    }

    /**
     * Do Upgrade.
     *
     * @return void
     * @throws Exception
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $store = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE);

        try {
            $block = $this->blockRepository->getById(self::BLOCK_ID);
        } catch (LocalizedException $e) {
            /** @var BlockInterface $block */
            $block = $this->blockFactory->create();
            $block->setIdentifier(self::BLOCK_ID)
                ->setTitle(self::BLOCK_NAME);
        }

        $block->setContent(
            '<style>#html-body [data-pb-style=U5G0PY3]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="U5G0PY3"><div data-content-type="text" data-appearance="default" data-element="main"><p>{{widget type="Magento\Catalog\Block\Widget\RecentlyViewed" uiComponent="widget_recently_viewed" page_size="10" show_attributes="name,image" show_buttons="add_to_cart" template="product/widget/viewed/grid.phtml"}}</p></div></div></div>'
        );
        $block->setStoreId($store->getId());
        $this->blockRepository->save($block);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
