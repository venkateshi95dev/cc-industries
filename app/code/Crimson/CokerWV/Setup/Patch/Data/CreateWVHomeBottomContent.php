<?php
namespace Crimson\CokerWV\Setup\Patch\Data;

use Crimson\Cms\Setup\Patch\Data\BlockCollectionFactory;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Store\Api\StoreRepositoryInterface;

class CreateWVHomeBottomContent implements
    DataPatchInterface,
    PatchVersionInterface
{
    public function __construct(
        private readonly \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory,
        private readonly StoreRepositoryInterface $storeRepositoryInterface,
        private readonly \Magento\Cms\Model\BlockFactory $blockFactory
    )
    {
    }

    public function apply()
    {
        $wvStoreId               = $this->storeRepositoryInterface->get(WVStoreInterface::WV_STORE_CODE)->getId();
        $blockFactory = $this->blockCollectionFactory->create();

        $cmsblock = $blockFactory->addFieldToFilter('identifier', 'wv_home_page_bottom');

        $content = '
        <div class="container padding0">
        <div class="featuerd_style">
        <ul class="ul_flex">
                    <li>
                                                            <a href="{{store direct_url=\'all-wheels/muscle-car.html\'}}" class="promo " title="Generic Muscle Car">
                            <img class="img-responsive" src="{{media url=cms_pages/musclecar-webad_1.jpg}}" alt="Generic Muscle Car" />
                        </a>
                                                </li>
                                <li>
                                                            <a href="{{store direct_url=\'all-wheels/classic.html\'}}" class="promo " title="Generic Classic">
                            <img class="img-responsive" src="{{media url=cms_pages/classic-web-000.jpg}}" alt="Generic Classic">
                        </a>
                                                </li>
                                <li>
                                                            <a href="{{store direct_url=\'all-wheels/hot-rod.html\'}}" class="promo " title="Generic Hot Rod">
                            <img class="img-responsive" src="{{media url=cms_pages/hot-rod-webad.jpg}}" alt="Generic Hot Rod">
                        </a>
                                                </li>
                        </ul>
        </div>
    </div>
        ';


        if (count($cmsblock)) {
            foreach ($cmsblock as $block) {
                $block->setContent($content)->save();
                break;
            }
        } else {
            $cmsBlock = [
                'title' => 'WV Homepage Bottom',
                'identifier' => 'wv_home_page_bottom',
                'stores' => [$wvStoreId],
                'content' => $content,
                'is_active' => 1,
            ];
            $this->blockFactory->create()->setData($cmsBlock)->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.1';
    }
}
