<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddBannerSliderCMSBlocks implements DataPatchInterface
{

    public function __construct(
        private readonly BlockFactory $blockFactory
    ) {}

    public function apply()
    {
        $contentCoker ='<div class="owl-carousel home-slider">

<div class="item">
<a class="slider-link" title="Show Slider" href="{{config path="web/secure/base_url"}}showpages/">
<img class="slider-image" src="{{media url=&quot;wysiwyg/241029-ctc-fall_moultrie.jpg&quot;}}" alt="Show Slider" data-slide="slide-1"/>
</a>
</div>

<div class="item">
<a class="slider-link" title="Michelin 2024 Winter Rebate" href="{{config path="web/secure/base_url"}}tires/brands/michelin-tires.html">
<img class="slider-image" src="{{media url=&quot;wysiwyg/coker-wv-banner-slides/241031-ctc-michelinwinterrebate-slider.jpg&quot;}}" alt="Michelin 2024 Winter Rebate" data-slide="slide-2"/>
</a>
</div>

<div class="item">
<a class="slider-link" title="Sitewide Free Shipping Slider Fall 2024" href="{{config path="web/secure/base_url"}}">
<img class="slider-image" src="{{media url=&quot;wysiwyg/coker-wv-banner-slides/240805-ctc-sitewide_free_shipping-slider3_1.jpg&quot;}}" alt="Sitewide Free Shipping Slider Fall 2024" data-slide="slide-3"/>
</a>
</div>

<div class="item">
<a class="slider-link" title="BFG Fall Promo 2024-2" href="{{config path="web/secure/base_url"}}tires/brands/bf-goodrich-tires.html">
<img class="slider-image" src="{{media url=&quot;wysiwyg/coker-wv-banner-slides/240924-ctc-bfg_late_fall_rebate-slider.jpg&quot;}}" alt="BFG Fall Promo 2024-2" data-slide="slide-4"/>
</a>
</div>

<div class="item">
<a class="slider-link" title="Coker Classic Star-Slider" href="{{config path="web/secure/base_url"}}tires/brands/coker-classic-tires/star_series.html">
<img class="slider-image" src="{{media url=&quot;wysiwyg/coker-wv-banner-slides/240809-ctc-coker_classicss-slider.jpg&quot;}}" alt="Coker Classic Star-Slider" data-slide="slide-5"/>
</a>
</div>

<div class="item">
<a class="slider-link" title="Tire & Wheel Complete Packages" href="{{config path="web/secure/base_url"}}tire-and-wheel-packages">
<img class="slider-image" src="{{media url=&quot;wysiwyg/coker-wv-banner-slides/240703-ctc-bundle_sets-slider_1.jpg&quot;}}" alt="Tire & Wheel Complete Packages" data-slide="slide-6"/>
</a>
</div>

<div class="item">
<a class="slider-link" title="BFG Fall 2024 Rebate" href="{{config path="web/secure/base_url"}}tires/brands/bf-goodrich-tires.html">
<img class="slider-image" src="{{media url=&quot;wysiwyg/coker-wv-banner-slides/240828-ctc-bfg_fall_rebate-slider.jpg&quot;}}" alt="BFG Fall 2024 Rebate" data-slide="slide-7"/>
</a>
</div>

<div class="item">
<a class="slider-link" title="Maxxilite Wheels-Slider" href="{{config path="web/secure/base_url"}}wheels/15x5-5-oe-vw-style-silver-4x130-34-mm.html">
<img class="slider-image" src="{{media url=&quot;wysiwyg/coker-wv-banner-slides/241028-ctc-maxilite_vw_wheel-slider-recovered.jpg&quot;}}" alt="Maxxilite Wheels-Slider" data-slide="slide-8"/>
</a>
</div>

<div class="item">
<a class="slider-link" title="Michelin Fall Rebate-2024" href="{{config path="web/secure/base_url"}}tires/brands/michelin-tires.html">
<img class="slider-image" src="{{media url=&quot;wysiwyg/coker-wv-banner-slides/240903-ctc-michelinfallrebate-slider.jpg&quot;}}" alt="Michelin Fall Rebate-2024" data-slide="slide-9"/>
</a>
</div>

<div class="item">
<a class="slider-link" title="WV Website Restock-Slider" href="{{config path="web/secure/base_url"}}all-wheels.html">
<img class="slider-image" src="{{media url=&quot;wysiwyg/coker-wv-banner-slides/240604-wv-callforupdates-slider.jpg&quot;}}" alt="WV Website Restock-Slider" data-slide="slide-10"/>
</a>
</div>

</div>
';

        $cmsblockCoker = [
            'title' => 'Coker Main Slider',
            'identifier' => 'coker_main_slider',
            'stores' => [12],
            'content' => $contentCoker,
            'is_active' => 1,
        ];
        $this->blockFactory->create()->setData($cmsblockCoker)->save();

        $contentWv ='<div class="owl-carousel home-slider">

<div class="item">
<a class="slider-link" title="Tire & Wheel Complete Packages" href="{{config path="web/secure/base_url"}}tire-and-wheel-packages">
<img class="slider-image" src="{{media url=&quot;wysiwyg/coker-wv-banner-slides/240703-ctc-bundle_sets-slider_1.jpg&quot;}}" alt="Tire & Wheel Complete Packages" data-slide="slide-1"/>
</a>
</div>

<div class="item">
<a class="slider-link" title="Michelin 2024 Winter Rebate" href="{{config path="web/secure/base_url"}}tires/brands/michelin-tires.html">
<img class="slider-image" src="{{media url=&quot;wysiwyg/coker-wv-banner-slides/241031-ctc-michelinwinterrebate-slider.jpg&quot;}}" alt="Michelin 2024 Winter Rebate" data-slide="slide-2"/>
</a>
</div>

</div>';

        $cmsblockWv = [
            'title' => 'Wv Main Slider',
            'identifier' => 'wv_main_slider',
            'stores' => [3],
            'content' => $contentWv,
            'is_active' => 1,
        ];
        $this->blockFactory->create()->setData($cmsblockWv)->save();

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
