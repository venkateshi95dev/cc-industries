<?php

namespace Crimson\MachCatalogRequest\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;

class AddGuestCatalogRequestPreventCmsBlock implements DataPatchInterface
{

    public function __construct(
        private readonly BlockFactory $blockFactory
    ) {}

    public function apply(): void
    {
        try {
            $cmsBlockData = [
                'title' => 'GUEST Catalog Request Account Creation',
                'identifier' => 'guest_catalog_request_account_creation',
                'content' => '<h1>You must create an account in order to request a Free catalog.</h1>
<p style="font-size: 21px;">Create a free online account with Zip Corvette and opt-in to our emails. We&quot;ll send you all the latest news and promotions. Once your account is created, you can request a Free Corvette catalog!</p>
<p style="font-size: 21px;">Please click <a href="{{config path=&quot;web/secure/base_url&quot;}}customer/account/index/">here</a></p>',
                'is_active' => 1,
                'stores' => [1],
                'sort_order' => 0
            ];

            $this->blockFactory->create()->setData($cmsBlockData)->save();
        } catch (\Exception $e) {

        }
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
