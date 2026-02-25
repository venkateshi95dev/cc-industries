<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Crimson\CokerWV\Api\WVStoreInterface;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AdditionalConfigForAmasty implements DataPatchInterface
{
    const AMSORTING_DISABLE_METHOD_PATH = 'amsorting/general/disable_methods';
    const AMSORTING_DISABLE_METHOD_VALUE = 'position,saving,rating_summary,reviews_count,wished,revenue,wheel_lug_nut_type,amz_price,special_price,bestsellers,price,new,whitewall_width,tread_width_text';
    const FACEBOOK_PIXEL_ENABLE_PATH = 'apptrian_facebookpixel/general/enabled';
    const FACEBOOK_PIXEL_ID = 'apptrian_facebookpixel/general/pixel_id';
    const FACEBOOK_PIXEL_VALUE = '1171565436284579';
    const AJAXCART_PATH = 'ajaxcart/general/active';
    const AMSHOPBY_CATEGORY_FILTER_ENABLE = 'amshopby/category_filter/enabled';

    public function __construct(
        private readonly \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        private readonly \Magento\Store\Model\StoreManagerInterface            $storeManager
    )
    {
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        $cokerWebsiteId = $this->storeManager->getWebsite(CokerStoreInterface::COKER_WEBSITE_CODE)->getId();
        $WVWebsiteId = $this->storeManager->getWebsite(WVStoreInterface::WV_WEBSITE_CODE)->getId();
        // update config for Amasty_SortBy
        $this->configWriter->save(self::AMSORTING_DISABLE_METHOD_PATH, self::AMSORTING_DISABLE_METHOD_VALUE, 'default');
        // update config for apptrian_facebookpixel
        $this->configWriter->save(self::FACEBOOK_PIXEL_ENABLE_PATH, 0, 'default');
        $this->configWriter->save(self::FACEBOOK_PIXEL_ENABLE_PATH, 1, 'websites', $cokerWebsiteId);
        $this->configWriter->save(self::FACEBOOK_PIXEL_ENABLE_PATH, 1, 'websites', $WVWebsiteId);
        $this->configWriter->save(self::FACEBOOK_PIXEL_ID, self::FACEBOOK_PIXEL_VALUE, 'websites', $cokerWebsiteId);
        $this->configWriter->save(self::FACEBOOK_PIXEL_ID, self::FACEBOOK_PIXEL_VALUE, 'websites', $WVWebsiteId);
        // update config for Amasty_ShopBy
        $this->configWriter->save(self::AMSHOPBY_CATEGORY_FILTER_ENABLE, 0, 'default');
        $this->configWriter->save(self::AMSHOPBY_CATEGORY_FILTER_ENABLE, 0, 'websites',$cokerWebsiteId);
        $this->configWriter->save(self::AMSHOPBY_CATEGORY_FILTER_ENABLE, 1, 'websites',$WVWebsiteId);
    }


    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [
            CreateCokerWebiste::class,
            CreateWVWebiste::class
        ];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
